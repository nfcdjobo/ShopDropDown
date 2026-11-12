@extends('admin.layouts.app')

@section('title', 'Administrateurs — Admin')

@section('content')

@php
    // État transmis à Alpine. Après une erreur de validation, la bonne modale se rouvre
    // avec les valeurs saisies (champ caché _modal / _id).
    $errorModal = $errors->any() ? old('_modal') : null;

    $config = [
        'routes' => [
            'update'  => route('admin.administrators.update', '__ID__'),
            'toggle'  => route('admin.administrators.toggle-active', '__ID__'),
            'reset'   => route('admin.administrators.reset-access', '__ID__'),
            'destroy' => route('admin.administrators.destroy', '__ID__'),
            'restore' => route('admin.administrators.restore', '__ID__'),
            'force'   => route('admin.administrators.force-destroy', '__ID__'),
        ],
        'form' => [
            'id'         => old('_id', ''),
            'first_name' => old('first_name', ''),
            'last_name'  => old('last_name', ''),
            'email'      => old('email', ''),
            'phone'      => old('phone', ''),
        ],
        'delivery'    => old('delivery', 'link'),
        'openModal'   => $errorModal,
        'errorModal'  => $errorModal,
        'errorId'     => $errorModal === 'edit' ? old('_id') : null,
        'credentials' => session('credentials'),
    ];

    $currentStatus = $filters['status'] ?? null;
@endphp

<script>
    // État de la page : modales (création, modification, confirmation, accès affichés) et actions.
    function adminsPage(cfg) {
        return {
            modal: cfg.openModal || (cfg.credentials ? 'credentials' : null),
            form: cfg.form,
            delivery: cfg.delivery || 'link',
            routes: cfg.routes,
            errorModal: cfg.errorModal,
            errorId: cfg.errorId,
            credentials: cfg.credentials,
            copied: false,
            confirm: {
                action: '', method: 'POST', title: '', text: '', button: 'Confirmer',
                tone: 'neutral', icon: 'fa-circle-question',
                askDelivery: false, needsEmail: false, email: '', typed: '',
            },

            init: function () {
                if (this.modal) this.focusFirst();
            },

            url: function (key, id) { return this.routes[key].replace('__ID__', id); },

            // Les erreurs serveur ne s'affichent que dans la modale (et pour la ligne) concernée
            get showErrors() {
                return this.errorModal === this.modal && (this.modal !== 'edit' || this.form.id === this.errorId);
            },

            get confirmReady() {
                return !this.confirm.needsEmail
                    || this.confirm.typed.trim().toLowerCase() === this.confirm.email.toLowerCase();
            },

            focusFirst: function () {
                var name = this.modal;
                setTimeout(function () {
                    var el = document.querySelector('[data-modal="' + name + '"] [data-autofocus]');
                    if (el) el.focus();
                }, 80);
            },

            close: function () { this.modal = null; this.copied = false; },

            openCreate: function () {
                this.form = { id: '', first_name: '', last_name: '', email: '', phone: '' };
                this.delivery = 'link';
                this.errorModal = null;
                this.modal = 'create';
                this.focusFirst();
            },

            openEdit: function (a) {
                this.form = { id: a.id, first_name: a.first_name, last_name: a.last_name, email: a.email, phone: a.phone };
                this.errorModal = null;
                this.modal = 'edit';
                this.focusFirst();
            },

            ask: function (opts) {
                this.confirm = Object.assign({
                    action: '', method: 'POST', title: '', text: '', button: 'Confirmer',
                    tone: 'neutral', icon: 'fa-circle-question',
                    askDelivery: false, needsEmail: false, email: '', typed: '',
                }, opts);
                this.delivery = 'link';
                this.modal = 'confirm';
                this.focusFirst();
            },

            askToggle: function (a) {
                this.ask({
                    action: this.url('toggle', a.id),
                    title: a.active ? 'Suspendre ce compte ?' : 'Réactiver ce compte ?',
                    text: a.active
                        ? a.name + ' ne pourra plus se connecter et sera déconnecté immédiatement.'
                        : a.name + ' pourra de nouveau se connecter.',
                    button: a.active ? 'Suspendre' : 'Réactiver',
                    tone: a.active ? 'warning' : 'success',
                    icon: a.active ? 'fa-user-slash' : 'fa-user-check',
                });
            },

            askReset: function (a) {
                this.ask({
                    action: this.url('reset', a.id),
                    title: "Réinitialiser l'accès",
                    text: 'Choisissez comment ' + a.name + ' recevra son nouvel accès. Ses sessions actuelles pourront être fermées.',
                    button: "Envoyer l'accès",
                    tone: 'neutral',
                    icon: 'fa-key',
                    askDelivery: true,
                });
            },

            askDelete: function (a) {
                this.ask({
                    action: this.url('destroy', a.id), method: 'DELETE',
                    title: 'Supprimer ce compte ?',
                    text: a.name + ' sera déconnecté et déplacé dans « Supprimés ». Vous pourrez le restaurer ou le supprimer définitivement.',
                    button: 'Supprimer', tone: 'danger', icon: 'fa-trash',
                });
            },

            askRestore: function (a) {
                this.ask({
                    action: this.url('restore', a.id),
                    title: 'Restaurer ce compte ?',
                    text: a.name + ' retrouvera son compte avec son statut précédent.',
                    button: 'Restaurer', tone: 'success', icon: 'fa-rotate-left',
                });
            },

            askForce: function (a) {
                this.ask({
                    action: this.url('force', a.id), method: 'DELETE',
                    title: 'Supprimer définitivement ?',
                    text: 'Cette action est irréversible : le compte de ' + a.name + ' disparaîtra pour toujours.',
                    button: 'Supprimer définitivement', tone: 'danger', icon: 'fa-skull-crossbones',
                    needsEmail: true, email: a.email,
                });
            },

            copy: function (text) {
                var self = this;
                if (!navigator.clipboard) return;
                navigator.clipboard.writeText(text).then(function () {
                    self.copied = true;
                    setTimeout(function () { self.copied = false; }, 2000);
                });
            },
        };
    }
</script>

<div x-data="adminsPage(@js($config))"
     x-effect="document.documentElement.classList.toggle('overflow-hidden', modal !== null)"
     @keydown.escape.window="if (modal && modal !== 'credentials') close()"
     class="max-w-7xl mx-auto px-5 lg:px-8 py-8 lg:py-12">

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Back-office</p>
            <h1 class="font-display text-3xl font-semibold tracking-tight">Administrateurs</h1>
            <p class="mt-1 text-sm text-ink-muted">Créez et gérez les comptes qui administrent la boutique. Section réservée au super administrateur.</p>
        </div>
        <button type="button" @click="openCreate()"
                class="btn-primary gap-2 px-5 h-11 rounded-full text-sm font-semibold">
            <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Nouvel administrateur
        </button>
    </div>

    {{-- ── ONGLETS ───────────────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-6 bg-ivory-100 p-1 rounded-2xl w-fit max-w-full overflow-x-auto">
        @foreach([
            ['label' => 'Tous',       'value' => null,        'count' => $counts['all']],
            ['label' => 'Actifs',     'value' => 'active',    'count' => $counts['active']],
            ['label' => 'Suspendus',  'value' => 'suspended', 'count' => $counts['suspended']],
            ['label' => 'Supprimés',  'value' => 'trashed',   'count' => $counts['trashed']],
        ] as $tab)
            @php $isActiveTab = $currentStatus === $tab['value']; @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => $tab['value'], 'page' => null]) }}"
               class="flex items-center gap-2 px-4 h-9 rounded-xl text-sm font-semibold whitespace-nowrap transition-all
                      {{ $isActiveTab ? 'bg-white shadow-soft text-ink' : 'text-ink-muted hover:text-ink' }}">
                {{ $tab['label'] }}
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded-full
                    {{ $isActiveTab ? ($tab['value'] === 'trashed' ? 'bg-red-500 text-white' : 'bg-terracotta-500 text-white') : 'bg-ink/10 text-ink-muted' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- ── RECHERCHE ─────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('admin.administrators.index') }}" class="mb-6">
        @if($currentStatus)
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm" aria-hidden="true"></i>
            <label for="q" class="sr-only">Rechercher un administrateur</label>
            <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                   placeholder="Nom, e-mail, téléphone…"
                   class="w-full pl-11 pr-4 h-11 rounded-xl bg-white border border-ink/15 text-sm outline-none
                          focus:border-terracotta-400 focus:ring-2 focus:ring-terracotta-100 transition-colors">
        </div>
    </form>

    {{-- ── LISTE ─────────────────────────────────────────────────────── --}}
    @if($admins->isEmpty())
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-6 py-16 text-center">
            <i class="fa-solid {{ $currentStatus === 'trashed' ? 'fa-trash-can' : 'fa-user-shield' }} text-4xl text-ink-muted/20 mb-4" aria-hidden="true"></i>
            <p class="font-display text-xl font-semibold">
                @if($currentStatus === 'trashed') Aucun compte supprimé
                @elseif(!empty($filters['q'])) Aucun résultat
                @else Aucun administrateur @endif
            </p>
            <p class="text-sm text-ink-muted mt-2">
                @if($currentStatus === 'trashed') Les comptes que vous supprimez apparaissent ici : vous pourrez les restaurer.
                @elseif(!empty($filters['q'])) Essayez d'autres mots-clés.
                @else Créez le premier compte avec « Nouvel administrateur ». @endif
            </p>
        </div>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

            <div class="hidden lg:grid grid-cols-12 gap-4 px-5 py-3 bg-ivory-50 border-b border-ink/5
                        text-[11px] uppercase tracking-wider font-semibold text-ink-muted">
                <div class="col-span-4">Administrateur</div>
                <div class="col-span-2">{{ $currentStatus === 'trashed' ? 'Supprimé le' : 'Activité' }}</div>
                <div class="col-span-1 text-right">Commandes</div>
                <div class="col-span-2 text-center">Statut</div>
                <div class="col-span-3 text-right">Actions</div>
            </div>

            <div class="divide-y divide-ink/5">
                @foreach($admins as $admin)
                    @php
                        $trashed = $admin->trashed();

                        if ($trashed) {
                            [$badge, $label] = ['bg-red-50 text-red-700 ring-red-200', 'Supprimé'];
                        } elseif ($admin->is_active) {
                            [$badge, $label] = ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'Actif'];
                        } else {
                            [$badge, $label] = ['bg-amber-50 text-amber-700 ring-amber-200', 'Suspendu'];
                        }

                        // Données passées à Alpine pour les modales
                        $p = [
                            'id'         => $admin->id,
                            'name'       => $admin->full_name,
                            'first_name' => $admin->first_name ?? '',
                            'last_name'  => $admin->last_name ?? '',
                            'email'      => $admin->email,
                            'phone'      => $admin->phone ?? '',
                            'active'     => (bool) $admin->is_active,
                        ];

                        $btn = 'inline-flex items-center gap-2 h-9 px-3 lg:w-9 lg:px-0 lg:justify-center rounded-lg text-xs font-semibold transition-colors';
                    @endphp

                    <div class="grid grid-cols-2 lg:grid-cols-12 gap-4 items-center px-5 py-4 hover:bg-ivory-50/60 transition-colors {{ $trashed ? 'bg-red-50/30' : '' }}">

                        {{-- Identité --}}
                        <div class="col-span-2 lg:col-span-4 flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-terracotta-50 ring-1 ring-terracotta-200 flex items-center justify-center shrink-0 text-xs font-bold text-terracotta-600">
                                {{ $admin->initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $admin->full_name }}</p>
                                <p class="text-xs text-ink-muted truncate">{{ $admin->email }}</p>
                                @if($admin->phone)
                                    <p class="text-xs text-ink-muted">{{ $admin->phone }}</p>
                                @endif
                            </div>
                            <span class="lg:hidden ml-auto text-[11px] font-semibold px-2 py-0.5 rounded-full ring-1 {{ $badge }}">{{ $label }}</span>
                        </div>

                        {{-- Activité / date de suppression --}}
                        <div class="hidden lg:block lg:col-span-2 text-xs text-ink-muted space-y-0.5">
                            @if($trashed)
                                <p>{{ $admin->deleted_at->format('d/m/Y à H:i') }}</p>
                            @else
                                <p>Créé le {{ $admin->created_at->format('d/m/Y') }}</p>
                                <p>Connexion : {{ $admin->last_login_at?->diffForHumans() ?? 'jamais' }}</p>
                            @endif
                        </div>

                        {{-- Commandes assignées --}}
                        <div class="hidden lg:block lg:col-span-1 text-sm font-semibold text-right">
                            {{ $admin->assigned_orders_count }}
                        </div>

                        {{-- Statut --}}
                        <div class="hidden lg:flex lg:col-span-2 justify-center">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 {{ $badge }}">{{ $label }}</span>
                        </div>

                        {{-- Actions --}}
                        <div class="col-span-2 lg:col-span-3 flex flex-wrap lg:justify-end gap-1.5">
                            @if($trashed)
                                <button type="button" @click="askRestore(@js($p))" title="Restaurer" aria-label="Restaurer {{ $admin->full_name }}"
                                        class="{{ $btn }} bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                    <i class="fa-solid fa-rotate-left" aria-hidden="true"></i><span class="lg:sr-only">Restaurer</span>
                                </button>
                                <button type="button" @click="askForce(@js($p))" title="Supprimer définitivement" aria-label="Supprimer définitivement {{ $admin->full_name }}"
                                        class="{{ $btn }} bg-red-50 text-red-700 hover:bg-red-100">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="lg:sr-only">Supprimer définitivement</span>
                                </button>
                            @else
                                <button type="button" @click="openEdit(@js($p))" title="Modifier" aria-label="Modifier {{ $admin->full_name }}"
                                        class="{{ $btn }} bg-ivory-100 hover:bg-terracotta-50 hover:text-terracotta-600">
                                    <i class="fa-solid fa-pen" aria-hidden="true"></i><span class="lg:sr-only">Modifier</span>
                                </button>
                                <button type="button" @click="askReset(@js($p))" title="Réinitialiser / renvoyer l'accès" aria-label="Réinitialiser l'accès de {{ $admin->full_name }}"
                                        class="{{ $btn }} bg-ivory-100 hover:bg-terracotta-50 hover:text-terracotta-600">
                                    <i class="fa-solid fa-key" aria-hidden="true"></i><span class="lg:sr-only">Accès</span>
                                </button>
                                <button type="button" @click="askToggle(@js($p))"
                                        title="{{ $admin->is_active ? 'Suspendre' : 'Réactiver' }}" aria-label="{{ $admin->is_active ? 'Suspendre' : 'Réactiver' }} {{ $admin->full_name }}"
                                        class="{{ $btn }} {{ $admin->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                    <i class="fa-solid {{ $admin->is_active ? 'fa-user-slash' : 'fa-user-check' }}" aria-hidden="true"></i>
                                    <span class="lg:sr-only">{{ $admin->is_active ? 'Suspendre' : 'Réactiver' }}</span>
                                </button>
                                <button type="button" @click="askDelete(@js($p))" title="Supprimer" aria-label="Supprimer {{ $admin->full_name }}"
                                        class="{{ $btn }} bg-red-50 text-red-700 hover:bg-red-100">
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i><span class="lg:sr-only">Supprimer</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($admins->hasPages())
            <div class="mt-6 flex justify-center">{{ $admins->links() }}</div>
        @endif
    @endif


    {{-- ══════════════════════════ MODALES ══════════════════════════ --}}

    {{-- ── Création ── --}}
    <div x-show="modal === 'create'" x-cloak x-transition.opacity.duration.200ms data-modal="create"
         role="dialog" aria-modal="true" aria-labelledby="create-title"
         class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="close()"></div>
        <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white rounded-3xl shadow-lift p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="create-title" class="font-display text-2xl font-semibold tracking-tight">Nouvel administrateur</h2>
                    <p class="mt-1 text-sm text-ink-muted">Le compte est créé et ses accès sont envoyés par e-mail.</p>
                </div>
                <button type="button" @click="close()" aria-label="Fermer" class="w-9 h-9 -mr-2 -mt-1 rounded-full hover:bg-ivory-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.administrators.store') }}" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="_modal" value="create">

                @include('admin.administrators.partials.fields', ['mode' => 'create'])
                @include('admin.administrators.partials.delivery')

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                    <button type="button" @click="close()" class="h-11 px-5 rounded-full text-sm font-semibold bg-ivory-100 hover:bg-ivory-200 transition-colors">Annuler</button>
                    <button type="submit" class="btn-primary gap-2 h-11 px-6 rounded-full text-sm font-semibold">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Créer et envoyer l'accès
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modification ── --}}
    <div x-show="modal === 'edit'" x-cloak x-transition.opacity.duration.200ms data-modal="edit"
         role="dialog" aria-modal="true" aria-labelledby="edit-title"
         class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="close()"></div>
        <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white rounded-3xl shadow-lift p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="edit-title" class="font-display text-2xl font-semibold tracking-tight">Modifier l'administrateur</h2>
                    <p class="mt-1 text-sm text-ink-muted">Si l'e-mail change, les liens de réinitialisation en attente sont annulés.</p>
                </div>
                <button type="button" @click="close()" aria-label="Fermer" class="w-9 h-9 -mr-2 -mt-1 rounded-full hover:bg-ivory-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <form method="POST" :action="url('update', form.id)" class="mt-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="_id" :value="form.id">

                @include('admin.administrators.partials.fields', ['mode' => 'edit'])

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                    <button type="button" @click="close()" class="h-11 px-5 rounded-full text-sm font-semibold bg-ivory-100 hover:bg-ivory-200 transition-colors">Annuler</button>
                    <button type="submit" class="btn-primary gap-2 h-11 px-6 rounded-full text-sm font-semibold">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Confirmation générique (suspendre, réactiver, réinitialiser, supprimer, restaurer, supprimer définitivement) ── --}}
    <div x-show="modal === 'confirm'" x-cloak x-transition.opacity.duration.200ms data-modal="confirm"
         role="alertdialog" aria-modal="true" aria-labelledby="confirm-title"
         class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="close()"></div>
        <div class="relative w-full max-w-md max-h-[92vh] overflow-y-auto bg-white rounded-3xl shadow-lift p-6 sm:p-8">
            <div class="flex gap-4">
                <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0"
                      :class="{
                          'bg-red-50 text-red-600': confirm.tone === 'danger',
                          'bg-amber-50 text-amber-600': confirm.tone === 'warning',
                          'bg-emerald-50 text-emerald-600': confirm.tone === 'success',
                          'bg-terracotta-50 text-terracotta-600': confirm.tone === 'neutral'
                      }">
                    <i class="fa-solid" :class="confirm.icon" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <h2 id="confirm-title" class="font-display text-xl font-semibold tracking-tight" x-text="confirm.title"></h2>
                    <p class="mt-1.5 text-sm text-ink-muted" x-text="confirm.text"></p>
                </div>
            </div>

            <form method="POST" :action="confirm.action" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="_method" :value="confirm.method">

                {{-- Réinitialisation : choix du mode d'envoi --}}
                <template x-if="confirm.askDelivery">
                    <div>@include('admin.administrators.partials.delivery')</div>
                </template>

                {{-- Suppression définitive : retaper l'e-mail --}}
                <template x-if="confirm.needsEmail">
                    <div>
                        <label for="confirm_email" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                            Retapez <span class="normal-case tracking-normal font-bold text-ink" x-text="confirm.email"></span> pour confirmer
                        </label>
                        <input id="confirm_email" type="text" name="confirm_email" x-model="confirm.typed"
                               autocomplete="off" data-autofocus
                               class="w-full h-11 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-red-400 focus:bg-white outline-none transition text-sm">
                    </div>
                </template>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button type="button" @click="close()" class="h-11 px-5 rounded-full text-sm font-semibold bg-ivory-100 hover:bg-ivory-200 transition-colors">Annuler</button>
                    <button type="submit" :disabled="!confirmReady"
                            class="h-11 px-6 rounded-full text-sm font-semibold text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="{
                                'bg-red-600 hover:bg-red-700': confirm.tone === 'danger',
                                'bg-amber-500 hover:bg-amber-600': confirm.tone === 'warning',
                                'bg-emerald-600 hover:bg-emerald-700': confirm.tone === 'success',
                                'bg-ink hover:bg-terracotta-500': confirm.tone === 'neutral'
                            }"
                            x-text="confirm.button"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Accès provisoires (affichés une seule fois) ── --}}
    <div x-show="modal === 'credentials'" x-cloak x-transition.opacity.duration.200ms data-modal="credentials"
         role="dialog" aria-modal="true" aria-labelledby="credentials-title"
         class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4 sm:p-6">
        {{-- Pas de fermeture au clic extérieur : évite de perdre le mot de passe par mégarde --}}
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm"></div>
        <template x-if="credentials">
            <div class="relative w-full max-w-md max-h-[92vh] overflow-y-auto bg-white rounded-3xl shadow-lift p-6 sm:p-8">
                <h2 id="credentials-title" class="font-display text-2xl font-semibold tracking-tight">Accès provisoires</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    Pour <strong x-text="credentials.name"></strong>.
                    <span class="block mt-1 font-semibold text-amber-700">Ce mot de passe n'est affiché qu'une seule fois.</span>
                </p>

                <dl class="mt-5 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-ink-muted font-semibold">Identifiant</dt>
                        <dd class="mt-1 font-mono break-all" x-text="credentials.email"></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-ink-muted font-semibold">Mot de passe provisoire</dt>
                        <dd class="mt-1 flex items-center gap-2">
                            <code class="flex-1 px-3 py-2.5 rounded-xl bg-ivory-100 ring-1 ring-ink/5 font-mono text-base tracking-wide select-all break-all" x-text="credentials.password"></code>
                            <button type="button" @click="copy(credentials.password)"
                                    class="h-10 px-3 rounded-xl bg-ink text-ivory-50 text-xs font-semibold hover:bg-terracotta-500 transition-colors shrink-0">
                                <span x-show="!copied"><i class="fa-regular fa-copy mr-1" aria-hidden="true"></i>Copier</span>
                                <span x-show="copied" x-cloak><i class="fa-solid fa-check mr-1" aria-hidden="true"></i>Copié</span>
                            </button>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-ink-muted font-semibold">Page de connexion</dt>
                        <dd class="mt-1 text-xs break-all text-ink-muted" x-text="credentials.login"></dd>
                    </div>
                </dl>

                <p class="mt-5 text-xs rounded-xl px-3.5 py-3"
                   :class="credentials.mailed ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800'">
                    <i class="fa-solid mr-1.5" :class="credentials.mailed ? 'fa-circle-check' : 'fa-triangle-exclamation'" aria-hidden="true"></i>
                    <span x-text="credentials.mailed
                        ? 'Ces accès ont aussi été envoyés par e-mail.'
                        : 'L\'e-mail n\'a pas pu être envoyé : communiquez ces accès vous-même, de façon sécurisée.'"></span>
                </p>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="close()" class="btn-primary h-11 px-6 rounded-full text-sm font-semibold">J'ai noté les accès</button>
                </div>
            </div>
        </template>
    </div>

</div>

@endsection
