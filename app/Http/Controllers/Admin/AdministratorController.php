<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdministratorCredentialsNotification;
use App\Notifications\AdministratorInvitationNotification;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Gestion des administrateurs (role = admin) par le super administrateur.
 *
 * Tout se fait depuis une seule page, via des modales :
 *   index()        → liste (Tous / Actifs / Suspendus / Supprimés) + recherche
 *   store()        → création + envoi des accès par e-mail (invitation OU identifiants)
 *   update()       → modification de l'identité
 *   toggleActive() → suspension / réactivation
 *   resetAccess()  → réinitialisation du compte (lien par e-mail OU mot de passe provisoire)
 *   destroy()      → suppression douce (restaurable)
 *   restore()      → restauration
 *   forceDestroy() → suppression définitive (compte déjà supprimé + saisie de l'e-mail)
 *
 * Les comptes super_admin ne sont jamais listés ni modifiables ici.
 */
class AdministratorController extends Controller implements HasMiddleware
{
    private const PER_PAGE = 20;

    /** Réservé au super administrateur actif, quelle que soit la configuration des routes. */
    public static function middleware(): array
    {
        return [
            function (Request $request, Closure $next) {
                $user = $request->user();

                abort_unless($user && $user->is_active && $user->isSuperAdmin(), 403,
                    'Cette section est réservée au super administrateur.');

                return $next($request);
            },
        ];
    }

    // ────────────────────────────────────────────────────────────────
    //  Liste
    // ────────────────────────────────────────────────────────────────

    /**
     * GET /admin/administrators
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,suspended,trashed'],
        ]);

        $query = $this->admins()->withCount('assignedOrders');

        match ($filters['status'] ?? null) {
            'trashed'   => $query->onlyTrashed()->orderByDesc('deleted_at'),
            'active'    => $query->where('is_active', true)->latest(),
            'suspended' => $query->where('is_active', false)->latest(),
            default     => $query->latest(),
        };

        if ($q = $filters['q'] ?? null) {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], Str::lower($q)) . '%';

            $query->where(function ($sub) use ($like) {
                foreach (['first_name', 'last_name', 'email', 'phone'] as $column) {
                    $sub->orWhereRaw("LOWER({$column}) LIKE ?", [$like]);
                }
            });
        }

        $counts = [
            'all'       => $this->admins()->count(),
            'active'    => $this->admins()->where('is_active', true)->count(),
            'suspended' => $this->admins()->where('is_active', false)->count(),
            'trashed'   => $this->admins()->onlyTrashed()->count(),
        ];

        return view('admin.administrators.index', [
            'admins'  => $query->paginate(self::PER_PAGE)->withQueryString(),
            'filters' => $filters,
            'counts'  => $counts,
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    //  Création
    // ────────────────────────────────────────────────────────────────

    /**
     * POST /admin/administrators
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            // unique : inclut les comptes supprimés (la contrainte de la base les compte aussi)
            'email'      => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')],
            'phone'      => ['nullable', 'string', 'max:30'],
            'delivery'   => ['required', 'in:link,password'],
        ], $this->messages());

        $admin = new User();
        $admin->forceFill([
            'first_name'           => trim($data['first_name']),
            'last_name'            => trim($data['last_name']),
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'role'                 => UserRole::Admin,
            // Mot de passe aléatoire inutilisable : l'accès réel est fourni par provisionAccess()
            'password'             => Str::password(40),
            'must_change_password' => true,
            'profile_completed'    => true,
            'is_active'            => true,
            'email_verified_at'    => now(),
        ])->save();

        $result = $this->provisionAccess($admin, $data['delivery']);

        return $this->accessResponse($admin, $result, isReset: false);
    }

    // ────────────────────────────────────────────────────────────────
    //  Modification
    // ────────────────────────────────────────────────────────────────

    /**
     * PUT /admin/administrators/{id}
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $admin = $this->findAdmin($id);

        $this->normalizeEmail($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone'      => ['nullable', 'string', 'max:30'],
        ], $this->messages());

        $oldEmail = $admin->email;

        $admin->forceFill([
            'first_name' => trim($data['first_name']),
            'last_name'  => trim($data['last_name']),
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
        ])->save();

        // L'e-mail change : les liens de réinitialisation en attente ne sont plus valables
        if ($oldEmail !== $admin->email) {
            DB::table('password_reset_tokens')->where('email', $oldEmail)->delete();
        }

        return $this->back()->with('success', "Les informations de {$admin->full_name} ont été mises à jour.");
    }

    // ────────────────────────────────────────────────────────────────
    //  Suspension / réactivation
    // ────────────────────────────────────────────────────────────────

    /**
     * POST /admin/administrators/{id}/toggle-active
     */
    public function toggleActive(string $id): RedirectResponse
    {
        $admin = $this->findAdmin($id);

        $admin->forceFill(['is_active' => ! $admin->is_active])->save();

        if (! $admin->is_active) {
            $this->revokeSessions($admin); // un compte suspendu est déconnecté immédiatement
        }

        return $this->back()->with(
            'success',
            "Le compte de {$admin->full_name} a été " . ($admin->is_active ? 'réactivé' : 'suspendu') . '.'
        );
    }

    // ────────────────────────────────────────────────────────────────
    //  Réinitialisation de l'accès
    // ────────────────────────────────────────────────────────────────

    /**
     * POST /admin/administrators/{id}/reset-access
     *
     * delivery = link     → e-mail avec un lien pour choisir son mot de passe (recommandé)
     * delivery = password → nouveau mot de passe provisoire, envoyé par e-mail ET affiché une fois
     */
    public function resetAccess(Request $request, string $id): RedirectResponse
    {
        $admin = $this->findAdmin($id);

        $data = $request->validate([
            'delivery' => ['required', 'in:link,password'],
        ]);

        if (! $admin->is_active) {
            return $this->back()->with('error', "Réactivez d'abord le compte de {$admin->full_name} avant de lui renvoyer un accès.");
        }

        $result = $this->provisionAccess($admin, $data['delivery'], isReset: true);

        return $this->accessResponse($admin, $result, isReset: true);
    }

    // ────────────────────────────────────────────────────────────────
    //  Suppression douce / restauration / suppression définitive
    // ────────────────────────────────────────────────────────────────

    /**
     * DELETE /admin/administrators/{id} — suppression douce (restaurable).
     */
    public function destroy(string $id): RedirectResponse
    {
        $admin = $this->findAdmin($id);

        $this->revokeSessions($admin);
        $admin->delete();

        return $this->back()->with(
            'success',
            "Le compte de {$admin->full_name} a été supprimé. Vous pouvez le restaurer depuis l'onglet « Supprimés »."
        );
    }

    /**
     * POST /admin/administrators/{id}/restore
     */
    public function restore(string $id): RedirectResponse
    {
        $admin = $this->findAdmin($id, onlyTrashed: true);

        $admin->restore();

        return $this->back()->with(
            'success',
            "Le compte de {$admin->full_name} a été restauré (" . ($admin->is_active ? 'actif' : 'suspendu') . ').'
        );
    }

    /**
     * DELETE /admin/administrators/{id}/force — suppression DÉFINITIVE et irréversible.
     *
     * Garde-fous : le compte doit déjà être supprimé (douce) et le super administrateur
     * doit retaper l'e-mail du compte.
     */
    public function forceDestroy(Request $request, string $id): RedirectResponse
    {
        $admin = $this->findAdmin($id, onlyTrashed: true);

        $request->validate(['confirm_email' => ['required', 'string']]);

        if (Str::lower(trim($request->input('confirm_email'))) !== Str::lower($admin->email)) {
            return $this->back()->with('error', "L'e-mail saisi ne correspond pas : suppression annulée.");
        }

        $name = $admin->full_name;

        try {
            DB::transaction(function () use ($admin) {
                // Références pouvant bloquer la suppression (clés étrangères)
                $this->revokeSessions($admin, rotateRememberToken: false);
                $admin->pushSubscriptions()->delete();
                DB::table('password_reset_tokens')->where('email', $admin->email)->delete();
                DB::table('orders')->where('assigned_to', $admin->id)->update(['assigned_to' => null]);

                $admin->forceDelete();
            });
        } catch (QueryException $e) {
            Log::error('Suppression définitive administrateur impossible', [
                'user_id' => $admin->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->back()->with(
                'error',
                "Suppression définitive impossible : ce compte est encore référencé ailleurs (historique, commandes…). Laissez-le dans « Supprimés »."
            );
        }

        return $this->back()->with('success', "Le compte de {$name} a été supprimé définitivement.");
    }

    // ────────────────────────────────────────────────────────────────
    //  Helpers privés
    // ────────────────────────────────────────────────────────────────

    /** Requête de base : uniquement les comptes « admin » (jamais super_admin ni clients). */
    private function admins()
    {
        return User::query()->where('role', UserRole::Admin);
    }

    /**
     * Retrouve un administrateur par son UUID (404 sinon).
     * Un identifiant mal formé est rejeté avant la base : PostgreSQL lèverait une erreur SQL.
     */
    private function findAdmin(string $id, bool $onlyTrashed = false): User
    {
        abort_unless(Str::isUuid($id), 404);

        $query = $this->admins();

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        return $query->whereKey($id)->firstOrFail();
    }

    /** E-mail en minuscules, sans espaces : évite les doublons et les liens liés à une autre casse. */
    private function normalizeEmail(Request $request): void
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
    }

    /**
     * Prépare l'accès de l'administrateur et l'envoie par e-mail.
     *
     * link     : jeton de réinitialisation + e-mail d'invitation (aucun mot de passe en clair)
     * password : mot de passe provisoire (envoyé par e-mail et retourné pour affichage)
     *
     * Une panne d'envoi n'annule pas l'opération : elle est journalisée et signalée.
     *
     * @return array{mailed: bool, plain: ?string}
     */
    private function provisionAccess(User $admin, string $delivery, bool $isReset = false): array
    {
        $plain = null;

        if ($delivery === 'password') {
            $plain = User::generateDefaultPassword(12);

            $admin->forceFill([
                'password'             => $plain,   // haché par le cast 'hashed'
                'must_change_password' => true,
            ])->save();

            $this->revokeSessions($admin);
        }

        try {
            if ($delivery === 'password') {
                $admin->notify(new AdministratorCredentialsNotification($plain, $isReset));
            } else {
                $admin->notify(new AdministratorInvitationNotification(Password::createToken($admin), $isReset));
            }

            $mailed = true;
        } catch (Throwable $e) {
            Log::error('Envoi des accès administrateur impossible', [
                'user_id'  => $admin->id,
                'delivery' => $delivery,
                'error'    => $e->getMessage(),
            ]);

            $mailed = false;
        }

        return ['mailed' => $mailed, 'plain' => $plain];
    }

    /** Message de retour + (si mot de passe provisoire) modale d'accès affichée une seule fois. */
    private function accessResponse(User $admin, array $result, bool $isReset): RedirectResponse
    {
        $redirect = redirect()->route('admin.administrators.index');

        $verb = $isReset ? 'Accès réinitialisé' : 'Administrateur créé';

        if ($result['mailed']) {
            $redirect->with('success', "{$verb} : un e-mail a été envoyé à {$admin->email}.");
        } else {
            $redirect->with(
                'warning',
                "{$verb}, mais l'e-mail n'a pas pu être envoyé. Vérifiez la configuration d'envoi puis utilisez « Renvoyer l'accès »."
            );
        }

        if ($result['plain'] !== null) {
            $redirect->with('credentials', [
                'name'     => $admin->full_name,
                'email'    => $admin->email,
                'password' => $result['plain'],
                'login'    => route('admin.login'),
                'mailed'   => $result['mailed'],
            ]);
        }

        return $redirect;
    }

    /**
     * Déconnecte le compte partout : jeton « se souvenir de moi » changé et sessions
     * supprimées (si le pilote de session est « database »).
     */
    private function revokeSessions(User $admin, bool $rotateRememberToken = true): void
    {
        if ($rotateRememberToken && $admin->exists && ! $admin->trashed()) {
            $admin->forceFill(['remember_token' => Str::random(60)])->save();
        }

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $admin->id)->delete();
        }
    }

    /** Retour sur la liste en conservant onglet, recherche et page. */
    private function back(): RedirectResponse
    {
        return back();
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required'  => 'Le nom est obligatoire.',
            'email.required'      => "L'adresse e-mail est obligatoire.",
            'email.email'         => "L'adresse e-mail n'est pas valide.",
            'email.unique'        => "Cette adresse e-mail est déjà utilisée (peut-être par un compte supprimé : consultez l'onglet « Supprimés »).",
            'delivery.required'   => "Choisissez comment transmettre l'accès.",
            'delivery.in'         => "Mode de transmission invalide.",
        ];
    }
}
