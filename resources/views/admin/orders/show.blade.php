@extends('admin.layouts.app')

@section('title', 'Commande ' . $order->order_number)
@section('page_title', $order->order_number)

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <a href="{{ route('admin.orders.index') }}" class="hover:text-terracotta-500">Commandes</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>{{ $order->order_number }}</span>
@endsection

@php
    $statusColors = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'confirm' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'process' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'ship' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'deliver' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'cancel' => 'bg-red-50 text-red-700 ring-red-200',
        'refunded' => 'bg-pink-50 text-pink-700 ring-pink-200',
    ];
    $statusDots = [
        'pending' => 'bg-amber-500',
        'confirm' => 'bg-blue-500',
        'process' => 'bg-indigo-500',
        'ship' => 'bg-purple-500',
        'deliver' => 'bg-emerald-500',
        'cancel' => 'bg-red-500',
        'refunded' => 'bg-pink-500',
    ];
    $timelineIcons = [
        'pending' => 'fa-clock',
        'confirm' => 'fa-circle-check',
        'process' => 'fa-gear',
        'ship' => 'fa-truck',
        'deliver' => 'fa-house-circle-check',
        'cancel' => 'fa-circle-xmark',
        'refunded' => 'fa-rotate-left',
    ];


    $actionMap = [
        'confirm' => ['label' => 'Confirmer', 'icon' => 'fa-circle-check', 'tone' => 'blue'],
        'process' => ['label' => 'Préparer', 'icon' => 'fa-gear', 'tone' => 'indigo'],
        'ship' => ['label' => 'Expédier', 'icon' => 'fa-truck', 'tone' => 'purple'],
        'deliver' => ['label' => 'Marquer livré', 'icon' => 'fa-house-circle-check', 'tone' => 'emerald'],
        'cancel' => ['label' => 'Annuler', 'icon' => 'fa-circle-xmark', 'tone' => 'red'],
    ];

    $toneBg = [
        'blue' => 'bg-blue-500 hover:bg-blue-600',
        'indigo' => 'bg-indigo-500 hover:bg-indigo-600',
        'purple' => 'bg-purple-500 hover:bg-purple-600',
        'emerald' => 'bg-emerald-500 hover:bg-emerald-600',
        'red' => 'bg-red-500 hover:bg-red-600',
    ];
@endphp

@section('content')

    {{-- =============== EN-TÊTE =============== --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold ring-1
                     {{ $statusColors[$order->status->value] ?? 'bg-ivory-100 text-ink-muted ring-ink/10' }}">
                <span
                    class="w-2 h-2 rounded-full {{ $statusDots[$order->status->value] ?? 'bg-ink-muted' }} {{ in_array($order->status->value, ['pending']) ? 'animate-pulse' : '' }}"></span>
                {{ $order->status->label() }}
            </span>
            <span class="text-sm text-ink-muted">Passée {{ $order->created_at->diffForHumans() }} ·
                {{ $order->created_at->format('d/m/Y à H:i') }}</span>
        </div>
        <a href="{{ route('admin.orders.index') }}"
            class="flex-shrink-0 inline-flex items-center gap-2 px-4 h-10 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Retour
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

        {{-- ============================================================
         COLONNE PRINCIPALE (2/3)
         ============================================================ --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Articles commandés --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
                <div class="px-5 lg:px-6 py-4 border-b border-ink/5 flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold">Articles commandés</h2>
                    <span class="text-xs text-ink-muted">{{ $order->items_count }}
                        article{{ $order->items_count > 1 ? 's' : '' }}</span>
                </div>

                <div class="divide-y divide-ink/5">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-4 px-5 lg:px-6 py-4">
                            <div class="w-16 h-16 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0">
                                @if ($item->product_image)
                                    <img src="{{ \Storage::disk('public')->url($item->product_image) }}"
                                        class="w-full h-full object-cover" alt="">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                        <i class="fa-regular fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold truncate">{{ $item->product_name }}</p>
                                @if ($item->product_sku)
                                    <p class="text-xs text-ink-muted font-mono">{{ $item->product_sku }}</p>
                                @endif
                                @if ($item->product_options)
                                    <p class="text-xs text-ink-muted mt-0.5">
                                        {{ collect($item->product_options)->map(fn($v, $k) => "$k : $v")->implode(' · ') }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold">{{ number_format((float) $item->unit_price, 0, ',', ' ') }}
                                    F</p>
                                <p class="text-xs text-ink-muted">× {{ $item->quantity }}</p>
                                <p class="text-sm font-display font-bold mt-1">
                                    {{ number_format((float) $item->total, 0, ',', ' ') }} F</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Totaux --}}
                <div class="px-5 lg:px-6 py-4 bg-ivory-100/50 border-t border-ink/5">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Sous-total</dt>
                            <dd>{{ number_format((float) $order->subtotal, 0, ',', ' ') }} {{ $order->currency }}</dd>
                        </div>
                        @if ($order->shipping_fee > 0)
                            <div class="flex justify-between">
                                <dt class="text-ink-muted">Livraison</dt>
                                <dd>{{ number_format((float) $order->shipping_fee, 0, ',', ' ') }} {{ $order->currency }}
                                </dd>
                            </div>
                        @endif
                        @if ($order->discount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <dt>Remise</dt>
                                <dd>– {{ number_format((float) $order->discount, 0, ',', ' ') }} {{ $order->currency }}
                                </dd>
                            </div>
                        @endif
                        @if ($order->tax > 0)
                            <div class="flex justify-between">
                                <dt class="text-ink-muted">Taxes</dt>
                                <dd>{{ number_format((float) $order->tax, 0, ',', ' ') }} {{ $order->currency }}</dd>
                            </div>
                        @endif
                        <div
                            class="flex justify-between font-display font-semibold text-base border-t border-ink/10 pt-2 mt-2">
                            <dt>Total</dt>
                            <dd class="text-terracotta-500">{{ $order->formatted_total }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Infos livraison --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-4">Adresse de livraison</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl bg-ivory-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user text-ink-muted text-sm"></i>
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-ink-muted">Client</p>
                            <p class="font-semibold mt-0.5">{{ $order->shipping_full_name }}</p>
                            <a href="tel:{{ $order->shipping_phone }}"
                                class="text-sm text-terracotta-500 hover:underline block mt-0.5">{{ $order->shipping_phone }}</a>
                            <a href="mailto:{{ $order->shipping_email }}"
                                class="text-sm text-terracotta-500 hover:underline block">{{ $order->shipping_email }}</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl bg-ivory-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-location-dot text-ink-muted text-sm"></i>
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-semibold text-ink-muted">Adresse</p>
                            <p class="font-semibold mt-0.5">{{ $order->shipping_city }}, {{ $order->shipping_country }}
                            </p>
                            <p class="text-sm text-ink-muted">{{ $order->shipping_address }}</p>
                            @if ($order->shipping_notes)
                                <p class="text-xs text-amber-700 bg-amber-50 px-2 py-1 rounded-lg mt-2">
                                    <i class="fa-solid fa-note-sticky mr-1"></i>
                                    {{ $order->shipping_notes }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            @if ($order->statusHistories->isNotEmpty())
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                    <h2 class="font-display text-lg font-semibold mb-5">Historique</h2>

                    <ol class="relative border-l border-ink/10 ml-3 space-y-5">
                        @foreach ($order->statusHistories as $history)
                            <li class="ml-6">
                                <span
                                    class="absolute -left-3.5 w-7 h-7 rounded-full flex items-center justify-center ring-4 ring-white
                                         {{ $statusColors[$history->to_status ?? 'pending'] ?? 'bg-ivory-100 text-ink-muted' }}">
                                    <i
                                        class="fa-solid {{ $timelineIcons[$history->to_status ?? 'pending'] ?? 'fa-circle-dot' }} text-xs"></i>
                                </span>
                                <div>
                                    <p class="font-semibold text-sm">{{ $history->label }}</p>
                                    <p class="text-xs text-ink-muted mt-0.5">
                                        {{ $history->created_at->format('d/m/Y à H:i') }}
                                        @if ($history->changedBy)
                                            · par <span class="font-semibold">{{ $history->changedBy->full_name }}</span>
                                        @endif
                                    </p>
                                    @if ($history->note)
                                        <p class="mt-2 text-sm bg-ivory-100 rounded-xl px-3 py-2 text-ink-muted">
                                            <i class="fa-solid fa-quote-left text-[10px] mr-1"></i>
                                            {{ $history->note }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>

        {{-- ============================================================
         SIDEBAR (1/3)
         ============================================================ --}}
        <div class="flex flex-col gap-6">

            {{-- Actions de transition --}}
            @if (!empty($allowedStatuses))
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                    <h2 class="font-display text-lg font-semibold mb-4">Faire avancer</h2>

                    <div class="flex flex-col gap-2">
                        @foreach ($allowedStatuses as $nextStatus)
                            @php $act = $actionMap[$nextStatus->value] ?? null; @endphp
                            @if ($act && $nextStatus->value !== 'cancel')
                                <form method="POST" action="{{ route('admin.orders.update', $order) }}"
                                    x-data="{}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="{{ $nextStatus->value }}">

                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="flex-1 h-11 rounded-xl text-white text-sm font-semibold transition-colors inline-flex items-center justify-center gap-2 {{ $toneBg[$act['tone']] }}">
                                            <i class="fa-solid {{ $act['icon'] }}"></i>
                                            {{ $act['label'] }}
                                        </button>

                                        {{-- Note optionnelle via popover --}}
                                        <button type="button"
                                            @click="$el.closest('form').querySelector('[name=note]').style.display = $el.closest('form').querySelector('[name=note]').style.display === 'none' ? 'block' : 'none'"
                                            class="w-11 h-11 rounded-xl bg-ivory-100 hover:bg-ivory-200 flex items-center justify-center text-ink-muted"
                                            title="Ajouter une note">
                                            <i class="fa-solid fa-comment-dots text-sm"></i>
                                        </button>
                                    </div>
                                    <textarea name="note" rows="2" placeholder="Note interne (optionnelle)…" style="display:none"
                                        class="mt-2 w-full px-3 py-2 text-xs rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 outline-none transition resize-none"></textarea>
                                </form>
                            @endif
                        @endforeach
                    </div>

                    {{-- Annulation séparée --}}
                    @if (in_array(\App\Enums\OrderStatus::Cancelled, $allowedStatuses))
                        <div class="border-t border-ink/5 mt-4 pt-4" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full h-10 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 text-sm font-semibold transition-colors inline-flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-xmark"></i>
                                Annuler la commande
                            </button>
                            <div x-show="open" x-cloak x-transition class="mt-3">
                                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="cancel">
                                    <textarea name="cancellation_reason" required rows="2" placeholder="Motif d'annulation (obligatoire)…"
                                        class="w-full px-3 py-2 text-sm rounded-xl bg-ivory-100 border border-transparent focus:border-red-400 focus:ring-2 focus:ring-red-200 outline-none transition resize-none"></textarea>
                                    <button type="submit"
                                        class="mt-2 w-full h-10 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                                        Confirmer l'annulation
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-ivory-100 rounded-2xl ring-1 ring-ink/5 p-5 text-center">
                    <i class="fa-solid fa-lock text-ink-muted"></i>
                    <p class="mt-2 text-sm text-ink-muted">Aucune transition disponible depuis ce statut.</p>
                </div>
            @endif

            {{-- Attribution --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Agent assigné</h2>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="flex flex-col gap-3">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="assign">

                    <select name="assigned_to"
                        class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        <option value="">— Non attribuée</option>
                        @foreach ($admins as $admin)
                            <option value="{{ $admin->id }}"
                                {{ $order->assigned_to === $admin->id ? 'selected' : '' }}>
                                {{ $admin->full_name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="w-full h-10 rounded-xl bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors">
                        Mettre à jour
                    </button>
                </form>
            </div>

            {{-- Notes internes --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Notes internes</h2>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="flex flex-col gap-3">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="notes">

                    <textarea name="admin_notes" rows="5" placeholder="Notes internes visibles uniquement par l'équipe…"
                        class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-none">{{ old('admin_notes', $order->admin_notes) }}</textarea>

                    <button type="submit"
                        class="w-full h-10 rounded-xl bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors">
                        Enregistrer les notes
                    </button>
                </form>
            </div>

            {{-- Fiche client --}}
            @if ($order->user)
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                    <h2 class="font-display text-lg font-semibold mb-4">Compte client</h2>

                    <div class="flex items-center gap-3 mb-4">
                        <span
                            class="w-12 h-12 rounded-full bg-ink text-ivory-50 flex items-center justify-center font-bold">
                            {{ $order->user->initials }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold truncate">{{ $order->user->full_name }}</p>
                            <p class="text-xs text-ink-muted truncate">{{ $order->user->email }}</p>
                        </div>
                    </div>

                    <dl class="text-xs space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Commandes totales</dt>
                            <dd class="font-semibold">{{ $order->user->orders()->count() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Membre depuis</dt>
                            <dd>{{ $order->user->created_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Profil complété</dt>
                            <dd>
                                @if ($order->user->profile_completed)
                                    <span class="text-emerald-600 font-semibold"><i class="fa-solid fa-check"></i>
                                        Oui</span>
                                @else
                                    <span class="text-amber-600 font-semibold"><i class="fa-solid fa-hourglass-half"></i>
                                        Non</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            {{-- Métadonnées commande --}}
            <div class="bg-ivory-100 rounded-2xl ring-1 ring-ink/5 p-5">
                <h3 class="text-xs uppercase tracking-widest font-semibold text-ink-muted mb-3">Métadonnées</h3>
                <dl class="text-xs space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">Paiement</dt>
                        <dd class="font-semibold">{{ $order->payment_status->label() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">Méthode</dt>
                        <dd class="font-semibold">{{ $order->payment_method->label() }}</dd>
                    </div>
                    @if ($order->paid_at)
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Payée le</dt>
                            <dd>{{ $order->paid_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                    @endif
                    <div class="border-t border-ink/10 pt-2 mt-2">
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">IP</dt>
                            <dd class="font-mono">{{ $order->ip_address ?? '—' }}</dd>
                        </div>
                    </div>
                </dl>

                @if (auth()->user()->isSuperAdmin())
                    <div class="border-t border-ink/10 pt-3 mt-3">
                        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                            onsubmit="return confirm('Archiver définitivement cette commande ?');">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-xs text-red-600 hover:text-red-700 font-semibold inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-box-archive"></i>
                                Archiver (super-admin uniquement)
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
