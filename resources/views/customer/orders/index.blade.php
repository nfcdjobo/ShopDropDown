@extends('layouts.app')

@section('title', 'Mes commandes — Shop Drop')

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

<div class="max-w-4xl mx-auto px-5 lg:px-8 py-10 lg:py-14">

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('customer.dashboard') }}"
           class="w-9 h-9 rounded-xl bg-white ring-1 ring-ink/10 shadow-soft flex items-center justify-center hover:ring-terracotta-300 transition-colors">
            <i class="fa-solid fa-arrow-left text-sm text-ink-muted"></i>
        </a>
        <div>
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Espace client</p>
            <h1 class="font-display text-3xl font-semibold tracking-tight">Mes commandes</h1>
        </div>
    </div>

    {{-- ── LISTE ─────────────────────────────────────────────────────── --}}
    @if($orders->isEmpty())
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-6 py-16 text-center">
            <i class="fa-solid fa-bag-shopping text-4xl text-ink-muted/20 mb-5"></i>
            <p class="font-display text-xl font-semibold">Aucune commande pour le moment</p>
            <p class="text-sm text-ink-muted mt-2">Vos commandes apparaîtront ici dès votre premier achat.</p>
            <a href="{{ route('home') }}"
               class="mt-6 inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl text-sm font-semibold">
                <i class="fa-solid fa-shop text-xs"></i>
                Découvrir la boutique
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            {{-- Header tableau (desktop) --}}
            <div class="hidden sm:grid grid-cols-12 gap-4 px-5 py-3 bg-ivory-50 border-b border-ink/5 text-[11px] uppercase tracking-wider font-semibold text-ink-muted">
                <div class="col-span-4">Commande</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-2">Articles</div>
                <div class="col-span-2">Total</div>
                <div class="col-span-2">Statut</div>
            </div>

            <div class="divide-y divide-ink/5">
                @foreach($orders as $order)
                    <a href="{{ route('customer.orders.show', $order->order_number) }}"
                       class="grid grid-cols-2 sm:grid-cols-12 gap-4 items-center px-5 py-4 hover:bg-ivory-50/60 transition-colors group">

                        {{-- Numéro --}}
                        <div class="col-span-1 sm:col-span-4">
                            <p class="text-sm font-semibold group-hover:text-terracotta-500 transition-colors">
                                {{ $order->order_number }}
                            </p>
                            <p class="text-xs text-ink-muted sm:hidden mt-0.5">{{ $order->created_at->format('d/m/Y') }}</p>
                        </div>

                        {{-- Date (desktop) --}}
                        <div class="hidden sm:block sm:col-span-2 text-sm text-ink-muted">
                            {{ $order->created_at->format('d/m/Y') }}
                        </div>

                        {{-- Articles --}}
                        <div class="hidden sm:block sm:col-span-2 text-sm text-ink-muted">
                            {{ $order->items_count }} art.
                        </div>

                        {{-- Total --}}
                        <div class="col-span-1 sm:col-span-2 text-right sm:text-left">
                            <span class="text-sm font-display font-semibold">{{ $order->formatted_total }}</span>
                        </div>

                        {{-- Statut --}}
                        <div class="col-span-2 sm:col-span-2 flex items-center justify-between sm:justify-start gap-2">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 {{ statusBadge($order->status) }}">
                                {{ $order->status->label() }}
                            </span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-ink-muted group-hover:text-terracotta-500 transition-colors ml-auto"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $orders->links() }}
            </div>
        @endif
    @endif

</div>

@endsection
