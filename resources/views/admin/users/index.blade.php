@extends('admin.layouts.app')

@section('title', 'Clients — Admin')

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

<div class="max-w-7xl mx-auto px-5 lg:px-8 py-8 lg:py-12">

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Back-office</p>
            <h1 class="font-display text-3xl font-semibold tracking-tight">Clients</h1>
        </div>
    </div>

    {{-- ── FLASH ────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4">
            <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
            <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- ── ONGLETS STATUT ───────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-6 bg-ivory-100 p-1 rounded-2xl w-fit">
        @foreach([
            ['label' => 'Tous',       'value' => null,       'count' => $counts['all']],
            ['label' => 'Actifs',     'value' => 'active',   'count' => $counts['active']],
            ['label' => 'Inactifs',   'value' => 'inactive', 'count' => $counts['inactive']],
            ['label' => 'Nouveaux 7j','value' => 'new',      'count' => $counts['new']],
        ] as $tab)
            @php $active = ($filters['status'] ?? null) === $tab['value']; @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => $tab['value'], 'page' => null]) }}"
               class="flex items-center gap-2 px-4 h-9 rounded-xl text-sm font-semibold transition-all
                      {{ $active ? 'bg-white shadow-soft text-ink' : 'text-ink-muted hover:text-ink' }}">
                {{ $tab['label'] }}
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded-full
                    {{ $active ? 'bg-terracotta-500 text-white' : 'bg-ink/10 text-ink-muted' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- ── FILTRES ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        {{-- Recherche --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1">
            @foreach($filters as $key => $value)
                @if($key !== 'q' && $value !== null)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Nom, email, téléphone…"
                       class="w-full pl-11 pr-4 h-11 rounded-xl bg-white border border-ink/15 text-sm outline-none
                              focus:border-terracotta-400 focus:ring-2 focus:ring-terracotta-100 transition-colors">
            </div>
        </form>

        {{-- Tri --}}
        <form method="GET" action="{{ route('admin.users.index') }}">
            @foreach($filters as $key => $value)
                @if($key !== 'sort' && $value !== null)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <select name="sort" onchange="this.form.submit()"
                    class="h-11 px-4 rounded-xl bg-white border border-ink/15 text-sm outline-none focus:border-terracotta-400 transition-colors cursor-pointer">
                <option value="latest" {{ ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' }}>
                    Plus récents
                </option>
                <option value="orders" {{ ($filters['sort'] ?? '') === 'orders' ? 'selected' : '' }}>
                    Par commandes
                </option>
                <option value="spent"  {{ ($filters['sort'] ?? '') === 'spent'  ? 'selected' : '' }}>
                    Par dépenses
                </option>
            </select>
        </form>
    </div>

    {{-- ── TABLEAU ───────────────────────────────────────────────────── --}}
    @if($customers->isEmpty())
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-6 py-16 text-center">
            <i class="fa-solid fa-users text-4xl text-ink-muted/20 mb-4"></i>
            <p class="font-display text-xl font-semibold">Aucun client trouvé</p>
            <p class="text-sm text-ink-muted mt-2">Modifiez vos filtres ou attendez les premières commandes.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

            {{-- En-tête --}}
            <div class="hidden lg:grid grid-cols-12 gap-4 px-5 py-3 bg-ivory-50 border-b border-ink/5
                        text-[11px] uppercase tracking-wider font-semibold text-ink-muted">
                <div class="col-span-4">Client</div>
                <div class="col-span-2">Inscrit le</div>
                <div class="col-span-2 text-right">Commandes</div>
                <div class="col-span-2 text-right">Total dépensé</div>
                <div class="col-span-1 text-center">Statut</div>
                <div class="col-span-1"></div>
            </div>

            <div class="divide-y divide-ink/5">
                @foreach($customers as $customer)
                    <div class="grid grid-cols-2 lg:grid-cols-12 gap-4 items-center px-5 py-4
                                hover:bg-ivory-50/60 transition-colors">

                        {{-- Identité --}}
                        <div class="col-span-2 lg:col-span-4 flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-terracotta-50 ring-1 ring-terracotta-200
                                        flex items-center justify-center shrink-0 text-xs font-bold text-terracotta-600">
                                {{ $customer->initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $customer->full_name }}</p>
                                <p class="text-xs text-ink-muted truncate">{{ $customer->email }}</p>
                                @if($customer->phone)
                                    <p class="text-xs text-ink-muted">{{ $customer->phone }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="hidden lg:block lg:col-span-2 text-sm text-ink-muted">
                            {{ $customer->created_at->format('d/m/Y') }}
                            @if($customer->created_at->gte(now()->subDays(7)))
                                <span class="ml-1 text-[10px] font-semibold bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded-full ring-1 ring-emerald-200">
                                    Nouveau
                                </span>
                            @endif
                        </div>

                        {{-- Commandes --}}
                        <div class="hidden lg:block lg:col-span-2 text-sm font-semibold text-right">
                            {{ $customer->orders_count }}
                        </div>

                        {{-- Total dépensé --}}
                        <div class="hidden lg:block lg:col-span-2 text-sm font-display font-semibold text-right">
                            {{ number_format((float)($customer->total_spent ?? 0), 0, ',', ' ') }} FCFA
                        </div>

                        {{-- Badge actif --}}
                        <div class="hidden lg:flex lg:col-span-1 justify-center">
                            @if($customer->is_active)
                                <span class="w-2 h-2 rounded-full bg-emerald-500" title="Actif"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-400" title="Inactif"></span>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="col-span-2 lg:col-span-1 flex justify-end">
                            <a href="{{ route('admin.users.show', $customer) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg
                                      bg-ivory-100 hover:bg-terracotta-50 hover:text-terracotta-600 transition-colors">
                                Voir
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pagination --}}
        @if($customers->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $customers->links() }}
            </div>
        @endif
    @endif

</div>

@endsection
