@extends('admin.layouts.app')

@section('title', $customer->full_name . ' — Client')

@php
function statusBadge(\App\Enums\OrderStatus $status): string {
    return match($status) {
        \App\Enums\OrderStatus::Pending    => 'bg-amber-50 text-amber-700 ring-amber-200',
        \App\Enums\OrderStatus::Confirmed  => 'bg-blue-50 text-blue-700 ring-blue-200',
        \App\Enums\OrderStatus::Processing => 'bg-violet-50 text-violet-700 ring-violet-200',
        \App\Enums\OrderStatus::Shipped    => 'bg-sky-50 text-sky-700 ring-sky-200',
        \App\Enums\OrderStatus::Delivered  => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        \App\Enums\OrderStatus::Cancelled  => 'bg-red-50 text-red-600 ring-red-200',
        \App\Enums\OrderStatus::Refunded   => 'bg-slate-50 text-slate-600 ring-slate-200',
    };
}
@endphp

@section('content')

<div class="max-w-6xl mx-auto px-5 lg:px-8 py-8 lg:py-12">

    {{-- ================================================================
         MODALE FLASH : nouveau mot de passe généré
         ================================================================ --}}
    @if(session('password_reset'))
        @php $reset = session('password_reset'); @endphp
        <div x-data="{ open: true }" x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 backdrop-blur-sm px-5">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-7"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="scale-95 opacity-0"
                 x-transition:enter-end="scale-100 opacity-100">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-key text-amber-500"></i>
                    </div>
                    <div>
                        <p class="font-display font-semibold">Mot de passe réinitialisé</p>
                        <p class="text-xs text-ink-muted">Communiquez-le au client par téléphone ou email.</p>
                    </div>
                </div>
                <div class="bg-ivory-100 rounded-xl px-4 py-3 space-y-1.5 text-sm mb-5">
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Client</span>
                        <span class="font-semibold">{{ $reset['name'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Email</span>
                        <span class="font-semibold">{{ $reset['email'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-ink-muted">Nouveau MDP</span>
                        <code class="font-bold text-terracotta-500 bg-terracotta-50 px-2.5 py-1 rounded-lg tracking-wider">
                            {{ $reset['password'] }}
                        </code>
                    </div>
                </div>
                <p class="text-xs text-ink-muted mb-5">
                    Le client devra changer ce mot de passe à sa prochaine connexion.
                    Cette information ne sera plus affichée après la fermeture de cette fenêtre.
                </p>
                <button @click="open = false"
                        class="w-full btn-primary py-3 rounded-xl text-sm font-semibold">
                    J'ai noté le mot de passe — Fermer
                </button>
            </div>
        </div>
    @endif

    {{-- ── FLASH STANDARD ───────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4">
            <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
            <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}"
               class="w-9 h-9 rounded-xl bg-white ring-1 ring-ink/10 shadow-soft flex items-center justify-center
                      hover:ring-terracotta-300 transition-colors shrink-0">
                <i class="fa-solid fa-arrow-left text-sm text-ink-muted"></i>
            </a>

            {{-- Avatar --}}
            <div class="w-14 h-14 rounded-2xl bg-terracotta-50 ring-2 ring-terracotta-200
                        flex items-center justify-center text-xl font-bold text-terracotta-600 shrink-0">
                {{ $customer->initials }}
            </div>

            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="font-display text-2xl lg:text-3xl font-semibold tracking-tight">
                        {{ $customer->full_name }}
                    </h1>
                    @if($customer->is_active)
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                            Actif
                        </span>
                    @else
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-600 ring-1 ring-red-200">
                            Inactif
                        </span>
                    @endif
                    @if(!$customer->profile_completed)
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                            Profil incomplet
                        </span>
                    @endif
                </div>
                <p class="text-sm text-ink-muted mt-0.5">
                    {{ $customer->email }}
                    @if($customer->phone) · {{ $customer->phone }} @endif
                </p>
                <p class="text-xs text-ink-muted mt-0.5">
                    Inscrit {{ $customer->created_at->diffForHumans() }}
                    ({{ $customer->created_at->format('d/m/Y') }})
                </p>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="flex items-center gap-2 shrink-0" x-data="{}">

            {{-- Toggle actif/inactif --}}
            <form method="POST" action="{{ route('admin.users.toggle-active', $customer) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl border transition-colors
                               {{ $customer->is_active
                                    ? 'border-red-200 text-red-600 hover:bg-red-50'
                                    : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                    <i class="fa-solid {{ $customer->is_active ? 'fa-ban' : 'fa-circle-check' }} text-xs"></i>
                    {{ $customer->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>

            {{-- Réinitialiser MDP --}}
            <form method="POST" action="{{ route('admin.users.reset-password', $customer) }}"
                  x-on:submit.prevent="if(confirm('Générer un nouveau mot de passe pour {{ addslashes($customer->full_name) }} ?')) $el.submit()">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl
                               border border-ink/15 hover:border-amber-300 hover:text-amber-600 transition-colors">
                    <i class="fa-solid fa-key text-xs"></i>
                    Reset MDP
                </button>
            </form>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- ================================================================
             COL GAUCHE — STATS (1/3)
             ================================================================ --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- KPIs principaux --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-base font-semibold mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-terracotta-500 text-sm"></i>
                    Activité
                </h2>

                <div class="space-y-3">
                    @foreach([
                        ['label' => 'Commandes totales', 'value' => $stats['total_orders'],     'icon' => 'fa-bag-shopping',  'color' => 'text-ink'],
                        ['label' => 'En cours',          'value' => $stats['active_orders'],    'icon' => 'fa-truck',          'color' => 'text-amber-500'],
                        ['label' => 'Livrées',           'value' => $stats['delivered_orders'], 'icon' => 'fa-circle-check',   'color' => 'text-emerald-600'],
                        ['label' => 'Annulées',          'value' => $stats['cancelled_orders'], 'icon' => 'fa-ban',            'color' => 'text-red-500'],
                    ] as $kpi)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-ink-muted">
                                <i class="fa-solid {{ $kpi['icon'] }} {{ $kpi['color'] }} text-xs w-4 text-center"></i>
                                {{ $kpi['label'] }}
                            </div>
                            <span class="text-sm font-semibold">{{ $kpi['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Financier --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-base font-semibold mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-coins text-terracotta-500 text-sm"></i>
                    Financier
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-ink-muted">Total dépensé</span>
                        <span class="text-sm font-display font-semibold text-terracotta-500">
                            {{ number_format($stats['total_spent'], 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-ink-muted">Panier moyen</span>
                        <span class="text-sm font-semibold">
                            {{ number_format($stats['avg_order_value'], 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    @if($stats['last_order_at'])
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-ink-muted">Dernière commande</span>
                            <span class="text-sm font-semibold">
                                {{ $stats['last_order_at']->diffForHumans() }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Répartition statuts --}}
            @if($stats['total_orders'] > 0)
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                    <h2 class="font-display text-base font-semibold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-terracotta-500 text-sm"></i>
                        Répartition statuts
                    </h2>
                    <div class="space-y-2">
                        @foreach($stats['by_status'] as $statusValue => $count)
                            @php
                                $statusEnum = \App\Enums\OrderStatus::from($statusValue);
                                $pct = round(($count / $stats['total_orders']) * 100);
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-ink-muted">{{ $statusEnum->label() }}</span>
                                    <span class="font-semibold">{{ $count }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-ink/5 overflow-hidden">
                                    <div class="h-full rounded-full bg-terracotta-400 transition-all"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Adresses --}}
            @if($customer->addresses->isNotEmpty())
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                    <h2 class="font-display text-base font-semibold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-terracotta-500 text-sm"></i>
                        Adresses
                    </h2>
                    <div class="space-y-3">
                        @foreach($customer->addresses as $address)
                            <div class="text-xs space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    @if($address->label)
                                        <span class="font-semibold">{{ $address->label }}</span>
                                    @endif
                                    @if($address->is_default)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-terracotta-50 text-terracotta-600 ring-1 ring-terracotta-200 font-semibold">Défaut</span>
                                    @endif
                                </div>
                                <p class="text-ink-muted">{{ $address->full_address }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ================================================================
             COL DROITE — COMMANDES RÉCENTES (2/3)
             ================================================================ --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

                <div class="px-5 py-4 border-b border-ink/5 flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold">Commandes récentes</h2>
                    @if($stats['total_orders'] > 5)
                        <a href="{{ route('admin.orders.index', ['q' => $customer->email]) }}"
                           class="text-xs text-terracotta-500 hover:underline flex items-center gap-1">
                            Toutes ({{ $stats['total_orders'] }})
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>

                @if($recentOrders->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <i class="fa-solid fa-bag-shopping text-3xl text-ink-muted/20 mb-3"></i>
                        <p class="text-sm text-ink-muted">Aucune commande pour ce client.</p>
                    </div>
                @else
                    {{-- En-tête --}}
                    <div class="hidden sm:grid grid-cols-12 gap-4 px-5 py-2.5 bg-ivory-50 border-b border-ink/5
                                text-[11px] uppercase tracking-wider font-semibold text-ink-muted">
                        <div class="col-span-4">Numéro</div>
                        <div class="col-span-3">Date</div>
                        <div class="col-span-2 text-right">Total</div>
                        <div class="col-span-3 text-right">Statut</div>
                    </div>

                    <div class="divide-y divide-ink/5">
                        @foreach($recentOrders as $order)
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="grid grid-cols-2 sm:grid-cols-12 gap-4 items-center px-5 py-4
                                      hover:bg-ivory-50/60 transition-colors group">

                                <div class="col-span-1 sm:col-span-4">
                                    <p class="text-sm font-semibold group-hover:text-terracotta-500 transition-colors">
                                        {{ $order->order_number }}
                                    </p>
                                    <p class="text-xs text-ink-muted sm:hidden">{{ $order->created_at->format('d/m/Y') }}</p>
                                </div>

                                <div class="hidden sm:block sm:col-span-3 text-sm text-ink-muted">
                                    {{ $order->created_at->format('d/m/Y') }}
                                </div>

                                <div class="hidden sm:block sm:col-span-2 text-sm font-display font-semibold text-right">
                                    {{ $order->formatted_total }}
                                </div>

                                <div class="col-span-1 sm:col-span-3 flex justify-end items-center gap-2">
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full ring-1 {{ statusBadge($order->status) }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </div>

                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

    </div>{{-- /grid --}}

</div>

@endsection
