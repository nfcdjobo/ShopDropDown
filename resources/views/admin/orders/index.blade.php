@extends('admin.layouts.app')

@section('title', 'Commandes')
@section('page_title', 'Commandes')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>Commandes</span>
@endsection

@php
    $currentStatus = $filters['status'] ?? '';

    $statusTabs = [
        ['key' => '',           'label' => 'Tout',         'count' => $counts['all']],
        ['key' => 'pending',    'label' => 'En attente',   'count' => $counts['pending'],    'dot' => 'bg-amber-500'],
        ['key' => 'confirm',  'label' => 'Confirmées',   'count' => $counts['confirmed'],  'dot' => 'bg-blue-500'],
        ['key' => 'process', 'label' => 'Préparation',  'count' => $counts['processing'], 'dot' => 'bg-indigo-500'],
        ['key' => 'ship',    'label' => 'Expédiées',    'count' => $counts['shipped'],    'dot' => 'bg-purple-500'],
        ['key' => 'deliver',  'label' => 'Livrées',      'count' => $counts['delivered'],  'dot' => 'bg-emerald-500'],
        ['key' => 'cancel',  'label' => 'Annulées',     'count' => $counts['cancelled'],  'dot' => 'bg-red-500'],
    ];

    $statusColors = [
        'pending'    => 'bg-amber-50 text-amber-700',
        'confirm'  => 'bg-blue-50 text-blue-700',
        'process' => 'bg-indigo-50 text-indigo-700',
        'ship'    => 'bg-purple-50 text-purple-700',
        'deliver'  => 'bg-emerald-50 text-emerald-700',
        'cancel'  => 'bg-red-50 text-red-700',
        'refunded'   => 'bg-pink-50 text-pink-700',
    ];

    $statusDots = [
        'pending'    => 'bg-amber-500',
        'confirm'  => 'bg-blue-500',
        'process' => 'bg-indigo-500',
        'ship'    => 'bg-purple-500',
        'deliver'  => 'bg-emerald-500',
        'cancel'  => 'bg-red-500',
        'refunded'   => 'bg-pink-500',
    ];
@endphp








@section('content')

{{-- =============== ONGLETS STATUTS =============== --}}
<div class="mb-6 -mx-4 sm:mx-0">
    <div class="flex items-center gap-1 overflow-x-auto px-4 sm:px-0 pb-1 scrollbar-none">
        @foreach($statusTabs as $tab)
            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['status' => $tab['key']])) }}"
               class="flex-shrink-0 inline-flex items-center gap-2 px-4 h-9 rounded-full text-sm font-semibold transition-colors
                      {{ $currentStatus === $tab['key']
                            ? 'bg-ink text-ivory-50 shadow-soft'
                            : 'bg-white text-ink-muted hover:bg-ivory-200 ring-1 ring-ink/5' }}">
                @if(!empty($tab['dot']))
                    <span class="w-2 h-2 rounded-full {{ $tab['dot'] }} {{ $currentStatus === $tab['key'] ? 'opacity-100' : 'opacity-60' }}"></span>
                @endif
                {{ $tab['label'] }}
                <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold flex items-center justify-center
                             {{ $currentStatus === $tab['key'] ? 'bg-white/20 text-ivory-50' : 'bg-ink/8 text-ink-muted' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>
</div>

{{-- =============== FILTRES AVANCÉS =============== --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-4 lg:p-5 mb-6">
    <form method="GET" class="flex flex-col lg:flex-row lg:items-end gap-3 lg:gap-4">
        <input type="hidden" name="status" value="{{ $currentStatus }}">

        <div class="flex-1">
            <label for="q" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Recherche</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="N° commande, client, email, téléphone…"
                       class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 lg:flex gap-3">
            <div>
                <label for="assigned_to" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Agent</label>
                <select name="assigned_to" id="assigned_to"
                        class="w-full lg:w-44 px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <option value="">Tous</option>
                    <option value="me"         {{ ($filters['assigned_to'] ?? '') === 'me'         ? 'selected' : '' }}>Mes commandes</option>
                    <option value="unassigned" {{ ($filters['assigned_to'] ?? '') === 'unassigned' ? 'selected' : '' }}>Non attribuées</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ ($filters['assigned_to'] ?? '') === $admin->id ? 'selected' : '' }}>
                            {{ $admin->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="from" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Du</label>
                <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}"
                       class="w-full lg:w-40 px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
            </div>

            <div>
                <label for="to" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Au</label>
                <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}"
                       class="w-full lg:w-40 px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 lg:flex-none h-11 px-5 rounded-xl bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i>
                Filtrer
            </button>
            @if(array_filter(\Illuminate\Support\Arr::except($filters, ['status'])))
                <a href="{{ route('admin.orders.index', ['status' => $currentStatus]) }}"
                   class="h-11 w-11 rounded-xl bg-ivory-100 hover:bg-red-50 hover:text-red-600 flex items-center justify-center text-ink-muted" title="Réinitialiser">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- =============== LISTE =============== --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

    @if($orders->isEmpty())
        <div class="py-20 text-center px-6">
            <div class="w-20 h-20 rounded-full bg-ivory-100 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-cart-shopping text-2xl text-ink-muted"></i>
            </div>
            <h3 class="mt-6 font-display text-xl font-semibold">Aucune commande trouvée</h3>
            <p class="mt-2 text-sm text-ink-muted">Modifiez les filtres pour élargir la recherche.</p>
        </div>
    @else
        {{-- Table desktop --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ivory-100 text-left text-xs uppercase tracking-wider text-ink-muted font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Commande</th>
                        <th class="px-6 py-3.5">Client</th>
                        <th class="px-6 py-3.5">Articles</th>
                        <th class="px-6 py-3.5">Total</th>
                        <th class="px-6 py-3.5">Statut</th>
                        <th class="px-6 py-3.5">Agent</th>
                        <th class="px-6 py-3.5 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/5">
                    @foreach($orders as $order)
                        <tr class="hover:bg-ivory-100/50 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-display font-semibold text-terracotta-500 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold">{{ $order->shipping_full_name }}</p>
                                    <p class="text-xs text-ink-muted">{{ $order->shipping_phone }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-ink-muted">
                                {{ $order->items_count }} art.
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-display font-semibold">{{ $order->formatted_total }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$order->status->value] ?? 'bg-ivory-100 text-ink-muted' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$order->status->value] ?? 'bg-ink-muted' }}"></span>
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($order->assignedTo)
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-ink text-ivory-50 flex items-center justify-center text-[10px] font-bold">
                                            {{ $order->assignedTo->initials }}
                                        </span>
                                        <span class="text-sm truncate max-w-[100px]">{{ $order->assignedTo->first_name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-ink-muted/60 italic">Non attribuée</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-xs text-ink-muted">{{ $order->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-ink-muted">{{ $order->created_at->format('H:i') }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cartes mobile --}}
        <div class="lg:hidden divide-y divide-ink/5">
            @foreach($orders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="block p-4 hover:bg-ivory-100/50">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div>
                            <p class="font-display font-semibold text-terracotta-500">{{ $order->order_number }}</p>
                            <p class="text-sm font-semibold mt-0.5">{{ $order->shipping_full_name }}</p>
                            <p class="text-xs text-ink-muted">{{ $order->shipping_phone }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-display font-semibold">{{ $order->formatted_total }}</p>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $statusColors[$order->status->value] ?? 'bg-ivory-100 text-ink-muted' }}">
                                <span class="w-1 h-1 rounded-full {{ $statusDots[$order->status->value] ?? 'bg-ink-muted' }}"></span>
                                {{ $order->status->label() }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-ink-muted">
                        <span><i class="fa-solid fa-box mr-1"></i>{{ $order->items_count }} article{{ $order->items_count > 1 ? 's' : '' }}</span>
                        <span>·</span>
                        <span>{{ $order->created_at->diffForHumans() }}</span>
                        @if($order->assignedTo)
                            <span>·</span>
                            <span><i class="fa-solid fa-user mr-1"></i>{{ $order->assignedTo->first_name }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    @if($orders->hasPages())
        <div class="px-5 lg:px-6 py-4 border-t border-ink/5 bg-ivory-100/50 flex items-center justify-between gap-4">
            <p class="text-xs text-ink-muted">
                Affichage de {{ $orders->firstItem() }}–{{ $orders->lastItem() }} sur {{ $orders->total() }} commandes
            </p>
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Évite que le click sur un lien ou bouton dans la row ne déclenche le onclick du <tr>
    document.querySelectorAll('table tbody tr').forEach(row => {
        row.querySelectorAll('a, button, form').forEach(el => {
            el.addEventListener('click', e => e.stopPropagation());
        });
    });
</script>
@endpush
