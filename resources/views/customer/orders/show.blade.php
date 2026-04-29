@extends('layouts.app')

@section('title', 'Commande ' . $order->order_number . ' — Shop Dropdown')

@php
$orderSuccess = session('order_success'); // array{order_number, is_new_user, password_emailed}

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

// Icône par étape de timeline
function timelineIcon(string $toStatus): string {
    return match($toStatus) {
        'pending'    => 'fa-clock',
        'confirmed'  => 'fa-circle-check',
        'processing' => 'fa-box-open',
        'shipped'    => 'fa-truck',
        'delivered'  => 'fa-house-circle-check',
        'cancelled'  => 'fa-ban',
        'refunded'   => 'fa-rotate-left',
        default      => 'fa-circle-dot',
    };
}
@endphp

@section('content')

<div class="max-w-4xl mx-auto px-5 lg:px-8 py-10 lg:py-14">

    {{-- ================================================================
         FLASH : COMMANDE PASSÉE AVEC SUCCÈS (redirection depuis checkout)
         ================================================================ --}}
    @if($orderSuccess)
        <div class="mb-8 rounded-3xl overflow-hidden shadow-soft ring-1
            {{ $orderSuccess['is_new_user'] ? 'ring-terracotta-200' : 'ring-emerald-200' }}">

            {{-- Bandeau coloré --}}
            <div class="px-6 py-5 flex items-start gap-4
                {{ $orderSuccess['is_new_user'] ? 'bg-terracotta-500' : 'bg-emerald-600' }}">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-check text-white text-lg"></i>
                </div>
                <div>
                    <p class="text-white font-display font-semibold text-lg leading-tight">
                        Commande {{ $order->order_number }} confirmée !
                    </p>
                    <p class="text-white/80 text-sm mt-1">
                        Votre commande est en cours de traitement. Vous recevrez un email de confirmation.
                    </p>
                </div>
            </div>

            {{-- Corps du message --}}
            <div class="bg-white px-6 py-5 space-y-3">
                @if($orderSuccess['is_new_user'])
                    {{-- Nouveau client : informer sur le compte créé --}}
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-envelope-circle-check text-terracotta-500 mt-0.5"></i>
                        <p class="text-sm text-ink">
                            Un <strong>compte a été créé</strong> pour vous.
                            Votre mot de passe provisoire a été envoyé à <strong>{{ $order->shipping_email }}</strong>.
                        </p>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-shield-halved text-terracotta-500 mt-0.5"></i>
                        <p class="text-sm text-ink">
                            Pensez à <a href="{{ route('password.change') }}" class="text-terracotta-500 font-semibold hover:underline">personnaliser votre mot de passe</a>
                            pour sécuriser votre compte.
                        </p>
                    </div>
                @else
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-envelope-circle-check text-emerald-600 mt-0.5"></i>
                        <p class="text-sm text-ink">
                            Une confirmation a été envoyée à <strong>{{ $order->shipping_email }}</strong>.
                        </p>
                    </div>
                @endif

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-phone text-ink-muted mt-0.5"></i>
                    <p class="text-sm text-ink-muted">
                        Notre équipe vous contactera au <strong>{{ $order->shipping_phone }}</strong>
                        pour confirmer les détails de la livraison.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('customer.orders.index') }}"
           class="w-9 h-9 rounded-xl bg-white ring-1 ring-ink/10 shadow-soft flex items-center justify-center hover:ring-terracotta-300 transition-colors">
            <i class="fa-solid fa-arrow-left text-sm text-ink-muted"></i>
        </a>
        <div>
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Mes commandes</p>
            <h1 class="font-display text-2xl lg:text-3xl font-semibold tracking-tight flex items-center gap-3">
                {{ $order->order_number }}
                <span class="text-sm font-semibold px-2.5 py-1 rounded-full ring-1 {{ statusBadge($order->status) }}">
                    {{ $order->status->label() }}
                </span>
            </h1>
        </div>
    </div>

    <div class="grid lg:grid-cols-5 gap-6">

        {{-- ── COL GAUCHE : articles + livraison (3/5) ─────────────────── --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Articles commandés --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
                <div class="px-5 py-4 border-b border-ink/5">
                    <h2 class="font-display text-lg font-semibold">Articles</h2>
                </div>
                <div class="divide-y divide-ink/5">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 px-5 py-4">
                            {{-- Image --}}
                            <div class="w-14 h-14 rounded-xl bg-ivory-100 overflow-hidden shrink-0">
                                @if($item->product?->featured_image_url)
                                    <img src="{{ $item->product->featured_image_url }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                        <i class="fa-regular fa-image text-lg"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Infos --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $item->product_name }}</p>
                                @if(!empty($item->product_options))
                                    <p class="text-xs text-ink-muted truncate mt-0.5">
                                        {{ collect($item->product_options)->map(fn($v, $k) => "$k : $v")->implode(' · ') }}
                                    </p>
                                @endif
                                <p class="text-xs text-ink-muted mt-0.5">
                                    {{ number_format((float)$item->unit_price, 0, ',', ' ') }} FCFA × {{ $item->quantity }}
                                </p>
                            </div>

                            {{-- Sous-total --}}
                            <p class="text-sm font-display font-semibold shrink-0">
                                {{ number_format((float)$item->total, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Totaux --}}
                <div class="px-5 py-4 border-t border-ink/5 bg-ivory-50 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-ink-muted">Sous-total</span>
                        <span>{{ number_format((float)$order->subtotal, 0, ',', ' ') }} {{ $order->currency }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-emerald-700">
                        <span>Livraison</span>
                        <span class="font-semibold">{{ $order->shipping_fee > 0 ? number_format((float)$order->shipping_fee, 0, ',', ' ').' '.$order->currency : 'Gratuite' }}</span>
                    </div>
                    <div class="flex justify-between font-display font-semibold pt-2 border-t border-ink/10">
                        <span>Total</span>
                        <span class="text-terracotta-500 text-lg">{{ $order->formatted_total }}</span>
                    </div>
                </div>
            </div>

            {{-- Adresse de livraison --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-5 py-5">
                <h2 class="font-display text-lg font-semibold mb-4">Livraison</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-user text-ink-muted text-xs w-4 text-center"></i>
                        <span>{{ $order->shipping_full_name }}</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-phone text-ink-muted text-xs w-4 text-center"></i>
                        <span>{{ $order->shipping_phone }}</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-envelope text-ink-muted text-xs w-4 text-center"></i>
                        <span>{{ $order->shipping_email }}</span>
                    </div>
                    <div class="flex items-start gap-2.5 pt-1">
                        <i class="fa-solid fa-location-dot text-ink-muted text-xs w-4 text-center mt-0.5"></i>
                        <div>
                            <span>{{ $order->shipping_address }}</span>
                            <span class="text-ink-muted">, {{ $order->shipping_city }}</span>
                        </div>
                    </div>
                    @if($order->shipping_notes)
                        <div class="flex items-start gap-2.5 pt-1 border-t border-ink/5">
                            <i class="fa-solid fa-note-sticky text-ink-muted text-xs w-4 text-center mt-0.5"></i>
                            <span class="text-ink-muted italic">{{ $order->shipping_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── COL DROITE : timeline (2/5) ─────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft px-5 py-5 lg:sticky lg:top-28">
                <h2 class="font-display text-lg font-semibold mb-5">Suivi</h2>

                @if($order->statusHistories->isEmpty())
                    <p class="text-sm text-ink-muted">Aucun historique disponible.</p>
                @else
                    <ol class="relative border-l border-ink/10 ml-3 space-y-6">
                        @foreach($order->statusHistories as $history)
                            @php $isLatest = $loop->first; @endphp
                            <li class="ml-5 relative">
                                {{-- Dot --}}
                                <span class="absolute -left-[29px] top-0.5 w-5 h-5 rounded-full flex items-center justify-center ring-2
                                    {{ $isLatest ? 'bg-terracotta-500 ring-terracotta-200' : 'bg-white ring-ink/15' }}">
                                    <i class="fa-solid {{ timelineIcon($history->to_status) }} text-[9px]
                                        {{ $isLatest ? 'text-white' : 'text-ink-muted' }}"></i>
                                </span>

                                <div>
                                    <p class="text-sm font-semibold {{ $isLatest ? 'text-terracotta-500' : 'text-ink' }}">
                                        {{ $history->to_status_enum->label() }}
                                    </p>
                                    @if($history->note)
                                        <p class="text-xs text-ink-muted mt-0.5">{{ $history->note }}</p>
                                    @endif
                                    <p class="text-[11px] text-ink-muted/70 mt-1">
                                        {{ $history->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif

                {{-- Info paiement --}}
                <div class="mt-6 pt-5 border-t border-ink/5 flex items-center gap-2 text-xs text-ink-muted">
                    <i class="fa-solid fa-lock text-[11px]"></i>
                    <span>Paiement à la livraison — rien à payer maintenant.</span>
                </div>
            </div>
        </div>

    </div>{{-- /grid --}}

</div>

@endsection
