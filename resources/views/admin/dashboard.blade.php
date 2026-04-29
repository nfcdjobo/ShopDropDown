@extends('admin.layouts.app')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')

@section('topbar_actions')
    <a href="{{ route('admin.orders.index') }}" class="hidden sm:inline-flex items-center gap-2 px-4 h-10 rounded-full bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>Commandes</span>
    </a>
@endsection

@section('content')

{{-- =============== BANNIÈRE DE BIENVENUE =============== --}}
<div class="mb-8 lg:mb-10">
    <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center gap-2">
        <i class="fa-solid fa-sun"></i>
        {{ now()->translatedFormat('l j F Y') }}
    </p>
    <h2 class="mt-2 font-display text-3xl lg:text-4xl font-semibold tracking-tight leading-tight">
        Bonjour {{ auth()->user()->first_name }}, <span class="italic text-ink-muted">voici votre activité.</span>
    </h2>
</div>

{{-- =============== KPIS =============== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8 lg:mb-10">
    @php
        $kpis = [
            [
                'label' => 'Commandes aujourd\'hui',
                'value' => $stats['orders_today'],
                'icon'  => 'fa-cart-plus',
                'tone'  => 'terracotta',
                'hint'  => $stats['orders_month'].' ce mois',
            ],
            [
                'label' => 'En attente',
                'value' => $stats['orders_pending'],
                'icon'  => 'fa-clock',
                'tone'  => 'amber',
                'hint'  => $stats['orders_active'].' actives au total',
                'link'  => route('admin.orders.index', ['status' => 'pending']),
            ],
            [
                'label' => 'Revenu du mois',
                'value' => number_format($stats['revenue_month'], 0, ',', ' ').' F',
                'icon'  => 'fa-sack-dollar',
                'tone'  => 'emerald',
                'hint'  => 'En attente : '.number_format($stats['revenue_pending'], 0, ',', ' ').' F',
                'is_money' => true,
            ],
            [
                'label' => 'Clients',
                'value' => $stats['customers_total'],
                'icon'  => 'fa-users',
                'tone'  => 'indigo',
                'hint'  => '+'.$stats['customers_new_7d'].' cette semaine',
            ],
        ];

        $tones = [
            'terracotta' => 'bg-terracotta-50 text-terracotta-700',
            'amber'      => 'bg-amber-50 text-amber-700',
            'emerald'    => 'bg-emerald-50 text-emerald-700',
            'indigo'     => 'bg-indigo-50 text-indigo-700',
        ];
    @endphp

    @foreach($kpis as $kpi)
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6 hover:shadow-lift transition-shadow">
            <div class="flex items-start justify-between gap-3">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $tones[$kpi['tone']] }}">
                    <i class="fa-solid {{ $kpi['icon'] }}"></i>
                </span>
                @if(!empty($kpi['link']))
                    <a href="{{ $kpi['link'] }}" class="text-ink-muted hover:text-terracotta-500 text-xs" aria-label="Voir">
                        <i class="fa-solid fa-arrow-up-right"></i>
                    </a>
                @endif
            </div>
            <p class="mt-4 text-xs uppercase tracking-widest text-ink-muted font-semibold">{{ $kpi['label'] }}</p>
            <p class="mt-1 font-display font-semibold tracking-tight {{ !empty($kpi['is_money']) ? 'text-2xl lg:text-3xl' : 'text-3xl lg:text-4xl' }}">
                {{ $kpi['value'] }}
            </p>
            <p class="mt-1 text-xs text-ink-muted">{{ $kpi['hint'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

    {{-- =============== COLONNE PRINCIPALE =============== --}}
    <div class="lg:col-span-2 flex flex-col gap-6 lg:gap-8">

        {{-- Graphique 14 derniers jours --}}
        @php
            $days = [];
            for ($i = 13; $i >= 0; $i--) {
                $d = now()->subDays($i)->format('Y-m-d');
                $days[] = ['date' => $d, 'count' => (int) ($chartData[$d] ?? 0)];
            }
            $max = max(array_column($days, 'count')) ?: 1;
        @endphp

        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-display text-lg lg:text-xl font-semibold">Commandes · 14 derniers jours</h3>
                    <p class="text-xs text-ink-muted mt-0.5">Tendance de l'activité récente</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                    <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                    Live
                </span>
            </div>

            <div class="flex items-end gap-1.5 h-48">
                @foreach($days as $day)
                    <div class="flex-1 flex flex-col items-center gap-2 group">
                        <div class="w-full rounded-t-lg bg-gradient-to-t from-terracotta-500 to-terracotta-500/40 hover:from-terracotta-500 hover:to-terracotta-500 transition-colors relative"
                             style="height: {{ max(2, ($day['count'] / $max) * 100) }}%;">
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-ink text-ivory-50 text-[10px] font-semibold px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $day['count'] }} cde
                            </span>
                        </div>
                        <span class="text-[9px] text-ink-muted">{{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Commandes récentes --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <div class="px-5 lg:px-6 py-4 flex items-center justify-between border-b border-ink/5">
                <h3 class="font-display text-lg lg:text-xl font-semibold">Activité récente</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-terracotta-500 hover:underline flex items-center gap-1">
                    Tout voir
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="py-16 text-center">
                    <i class="fa-regular fa-folder-open text-3xl text-ink-muted/50"></i>
                    <p class="mt-3 text-ink-muted">Aucune commande pour l'instant.</p>
                </div>
            @else
                <div class="divide-y divide-ink/5">
                    @foreach($recentOrders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="flex items-center gap-3 px-5 lg:px-6 py-4 hover:bg-ivory-100 transition-colors">
                            <span class="w-10 h-10 rounded-full bg-ivory-100 flex items-center justify-center text-xs font-bold text-ink flex-shrink-0">
                                {{ strtoupper(substr($order->shipping_full_name, 0, 2)) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold truncate">{{ $order->shipping_full_name }}</p>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold
                                        {{ match($order->status->value) {
                                            'pending' => 'bg-amber-50 text-amber-700',
                                            'confirmed' => 'bg-blue-50 text-blue-700',
                                            'processing' => 'bg-indigo-50 text-indigo-700',
                                            'shipped' => 'bg-purple-50 text-purple-700',
                                            'delivered' => 'bg-emerald-50 text-emerald-700',
                                            'cancelled' => 'bg-red-50 text-red-700',
                                            default => 'bg-ink/10 text-ink-muted',
                                        } }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </div>
                                <p class="text-xs text-ink-muted truncate">
                                    {{ $order->order_number }} · {{ $order->items_count }} article{{ $order->items_count > 1 ? 's' : '' }} · {{ $order->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-display font-semibold">{{ $order->formatted_total }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- =============== COLONNE DROITE =============== --}}
    <div class="flex flex-col gap-6 lg:gap-8">

        {{-- À traiter en priorité --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-ink/5">
                <div>
                    <h3 class="font-display text-lg font-semibold">À traiter</h3>
                    <p class="text-xs text-ink-muted">Commandes non attribuées</p>
                </div>
                <span class="w-8 h-8 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-sm">
                    {{ $pendingOrders->count() }}
                </span>
            </div>

            @if($pendingOrders->isEmpty())
                <div class="py-10 text-center">
                    <i class="fa-solid fa-check-double text-2xl text-emerald-500"></i>
                    <p class="mt-2 text-sm text-ink-muted">Tout est sous contrôle.</p>
                </div>
            @else
                <div class="divide-y divide-ink/5">
                    @foreach($pendingOrders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="block px-5 py-3 hover:bg-ivory-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-terracotta-500">{{ $order->order_number }}</p>
                                <span class="text-[10px] text-ink-muted">{{ $order->created_at->diffForHumans(null, true) }}</span>
                            </div>
                            <p class="text-xs text-ink-muted truncate mt-0.5">{{ $order->shipping_full_name }} · {{ $order->formatted_total }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Alertes stock --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-ink/5">
                <div>
                    <h3 class="font-display text-lg font-semibold">Stock faible</h3>
                    <p class="text-xs text-ink-muted">Seuil d'alerte atteint</p>
                </div>
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
            </div>

            @if($lowStockProducts->isEmpty())
                <div class="py-10 text-center">
                    <i class="fa-solid fa-boxes-stacked text-2xl text-emerald-500"></i>
                    <p class="mt-2 text-sm text-ink-muted">Stock confortable partout.</p>
                </div>
            @else
                <div class="divide-y divide-ink/5">
                    @foreach($lowStockProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}"
                           class="flex items-center gap-3 px-5 py-3 hover:bg-ivory-100 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-ivory-100 overflow-hidden flex-shrink-0">
                                @if($product->featured_image_url)
                                    <img src="{{ $product->featured_image_url }}" class="w-full h-full object-cover" alt="">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                        <i class="fa-regular fa-image text-sm"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $product->name }}</p>
                                <p class="text-xs text-ink-muted">Seuil : {{ $product->low_stock_threshold }}</p>
                            </div>
                            <span class="text-sm font-bold {{ $product->stock === 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $product->stock }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
