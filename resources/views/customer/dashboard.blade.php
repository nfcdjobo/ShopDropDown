@extends('layouts.app')

@section('title', 'Mon espace — Shop Drop')

@php
/**
 * Badge statut commande — Blade helper local
 * Retourne les classes Tailwind selon le statut.
 */
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

<div class="max-w-5xl mx-auto px-5 lg:px-8 py-10 lg:py-14">

    {{-- ── BANNIÈRE PROFIL INCOMPLET ─────────────────────────────────── --}}
    @if($needsProfileCompletion)
        <div class="mb-8 flex items-start gap-4 rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4">
            <i class="fa-solid fa-circle-exclamation text-amber-500 text-lg mt-0.5 shrink-0"></i>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-amber-900">Complétez votre profil</p>
                <p class="text-xs text-amber-700 mt-0.5">Ajoutez votre numéro de téléphone et vérifiez vos informations pour faciliter vos prochaines commandes.</p>
            </div>
            <a href="{{ route('customer.profile.edit') }}"
               class="shrink-0 text-xs font-semibold text-amber-700 hover:text-amber-900 underline underline-offset-2">
                Mettre à jour
            </a>
        </div>
    @endif

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center gap-2">
                <i class="fa-solid fa-user-circle"></i>
                Espace client
            </p>
            <h1 class="mt-1 font-display text-3xl lg:text-4xl font-semibold tracking-tight">
                Bonjour, {{ $user->first_name }} 👋
            </h1>
        </div>
        <a href="{{ route('customer.profile.edit') }}"
           class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-ink-muted hover:text-ink border border-ink/15 rounded-xl px-4 py-2 hover:border-ink/30 transition-colors">
            <i class="fa-solid fa-pen text-xs"></i>
            Mon profil
        </a>
    </div>

    {{-- ── STATS ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4 mb-10">
        @foreach([
            ['label' => 'Commandes',  'value' => $stats['total_orders'],     'icon' => 'fa-bag-shopping',  'color' => 'text-ink'],
            ['label' => 'En cours',   'value' => $stats['active_orders'],    'icon' => 'fa-truck',          'color' => 'text-terracotta-500'],
            ['label' => 'Livrées',    'value' => $stats['delivered_orders'], 'icon' => 'fa-circle-check',   'color' => 'text-emerald-600'],
        ] as $stat)
            <div class="bg-white rounded-2xl shadow-soft ring-1 ring-ink/5 px-5 py-5 text-center">
                <i class="fa-solid {{ $stat['icon'] }} text-xl {{ $stat['color'] }} mb-2"></i>
                <p class="font-display text-3xl font-semibold">{{ $stat['value'] }}</p>
                <p class="text-xs text-ink-muted mt-0.5">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── COMMANDES EN COURS ────────────────────────────────────────── --}}
    @if($activeOrders->isNotEmpty())
        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-semibold">Commandes en cours</h2>
                <a href="{{ route('customer.orders.index') }}"
                   class="text-xs text-terracotta-500 hover:underline flex items-center gap-1">
                    Tout voir <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="space-y-3">
                @foreach($activeOrders as $order)
                    <a href="{{ route('customer.orders.show', $order->order_number) }}"
                       class="flex items-center gap-4 bg-white rounded-2xl shadow-soft ring-1 ring-ink/5 px-5 py-4 hover:ring-terracotta-200 transition-all group">

                        <div class="w-10 h-10 rounded-xl bg-terracotta-50 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-bag-shopping text-terracotta-500"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $order->order_number }}</p>
                            <p class="text-xs text-ink-muted mt-0.5">
                                {{ $order->items_count }} article(s) · {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-sm font-display font-semibold">{{ $order->formatted_total }}</span>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 {{ statusBadge($order->status) }}">
                                {{ $order->status->label() }}
                            </span>
                            <i class="fa-solid fa-chevron-right text-xs text-ink-muted group-hover:text-terracotta-500 transition-colors"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── HISTORIQUE RÉCENT ─────────────────────────────────────────── --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-xl font-semibold">Historique</h2>
            <a href="{{ route('customer.orders.index') }}"
               class="text-xs text-terracotta-500 hover:underline flex items-center gap-1">
                Toutes mes commandes <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-6 py-12 text-center">
                <i class="fa-solid fa-bag-shopping text-3xl text-ink-muted/30 mb-4"></i>
                <p class="text-sm text-ink-muted">Vous n'avez pas encore passé de commande.</p>
                <a href="{{ route('home') }}"
                   class="mt-4 inline-flex items-center gap-2 btn-primary px-5 py-2.5 rounded-xl text-sm font-semibold">
                    <i class="fa-solid fa-shop text-xs"></i>
                    Découvrir la boutique
                </a>
            </div>
        @else
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft divide-y divide-ink/5 overflow-hidden">
                @foreach($recentOrders as $order)
                    <a href="{{ route('customer.orders.show', $order->order_number) }}"
                       class="flex items-center gap-4 px-5 py-4 hover:bg-ivory-50 transition-colors group">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">{{ $order->order_number }}</p>
                            <p class="text-xs text-ink-muted mt-0.5">{{ $order->created_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-sm font-display font-semibold shrink-0">{{ $order->formatted_total }}</span>
                        <span class="hidden sm:inline text-[11px] font-semibold px-2 py-0.5 rounded-full ring-1 shrink-0 {{ statusBadge($order->status) }}">
                            {{ $order->status->label() }}
                        </span>
                        <i class="fa-solid fa-chevron-right text-xs text-ink-muted group-hover:text-terracotta-500 transition-colors shrink-0"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── ACTIONS RAPIDES ──────────────────────────────────────────── --}}
    <div class="mt-10 grid sm:grid-cols-2 gap-4">
        <a href="{{ route('customer.profile.edit') }}"
           class="flex items-center gap-4 bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-5 py-5 hover:ring-terracotta-200 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-sand-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-pen text-ink-muted"></i>
            </div>
            <div>
                <p class="text-sm font-semibold">Mon profil</p>
                <p class="text-xs text-ink-muted">Nom, téléphone, email</p>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-ink-muted ml-auto group-hover:text-terracotta-500 transition-colors"></i>
        </a>

        <a href="{{ route('password.change') }}"
           class="flex items-center gap-4 bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-5 py-5 hover:ring-terracotta-200 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-sand-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-key text-ink-muted"></i>
            </div>
            <div>
                <p class="text-sm font-semibold">Mot de passe</p>
                <p class="text-xs text-ink-muted">Modifier mon mot de passe</p>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-ink-muted ml-auto group-hover:text-terracotta-500 transition-colors"></i>
        </a>
    </div>

</div>

@endsection
