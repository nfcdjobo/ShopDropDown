<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, HasUuids, SoftDeletes, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'role',
        'must_change_password',
        'profile_completed',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'last_login_at'        => 'datetime',
            'password'             => 'hashed',
            'role'                 => UserRole::class,
            'must_change_password' => 'boolean',
            'profile_completed'    => 'boolean',
            'is_active'            => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Commandes assignées à cet admin (pour dispatcher le travail). */
    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'assigned_to');
    }

    public function pushSubscriptions(): MorphMany
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role', UserRole::Customer);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', UserRole::adminRoles());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        $full = trim("{$this->first_name} {$this->last_name}");
        return $full !== '' ? $full : $this->email;
    }

    public function getInitialsAttribute(): string
    {
        $first = Str::of((string) $this->first_name)->substr(0, 1)->upper()->toString();
        $last  = Str::of((string) $this->last_name)->substr(0, 1)->upper()->toString();
        $init  = $first . $last;
        return $init !== '' ? $init : Str::upper(Str::substr((string) $this->email, 0, 2));
    }

    /*
    |--------------------------------------------------------------------------
    | Vérifications de rôle
    |--------------------------------------------------------------------------
    */

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, UserRole::adminRoles(), true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /*
    |--------------------------------------------------------------------------
    | Logique métier
    |--------------------------------------------------------------------------
    */

    public function needsPasswordChange(): bool
    {
        return $this->must_change_password === true;
    }

    public function needsProfileCompletion(): bool
    {
        return $this->profile_completed === false;
    }

    /**
     * Génère un mot de passe temporaire pour les comptes créés automatiquement
     * lors du checkout invité. La valeur retournée est en clair (à envoyer par
     * email au client) ; le hash se fait via le cast 'hashed' quand on l'assigne.
     */
    public static function generateDefaultPassword(int $length = 10): string
    {
        // Pas de symboles : l'utilisateur doit pouvoir le retaper facilement.
        return Str::password($length, letters: true, numbers: true, symbols: false);
    }

    /**
     * Route Web Push (package laravel-notification-channels/webpush).
     * Laravel appellera cette méthode pour récupérer les abonnements actifs.
     */
    public function routeNotificationForWebpush(): iterable
    {
        return $this->pushSubscriptions;
    }
}
