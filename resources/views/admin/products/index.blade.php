@extends('admin.layouts.app')

@section('title', 'Produits')
@section('page_title', 'Produits')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>Produits</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft">
        <i class="fa-solid fa-plus"></i>
        <span class="hidden sm:inline">Nouveau produit</span>
        <span class="sm:hidden">Nouveau</span>
    </a>
@endsection

@section('content')

{{-- =============== FILTRES =============== --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-4 lg:p-5 mb-6">
    <form method="GET" class="flex flex-col lg:flex-row lg:items-end gap-3 lg:gap-4">
        <div class="flex-1">
            <label for="q" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Recherche</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Nom, SKU, description…"
                       class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 lg:flex gap-3">
            <div>
                <label for="type" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Type</label>
                <select name="type" id="type"
                        class="w-full lg:w-36 px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <option value="">Tous</option>
                    <option value="simple"  {{ ($filters['type'] ?? '') === 'simple'  ? 'selected' : '' }}>Simples</option>
                    <option value="package" {{ ($filters['type'] ?? '') === 'package' ? 'selected' : '' }}>Packages</option>
                </select>
            </div>

            <div>
                <label for="category_id" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Catégorie</label>
                <select name="category_id" id="category_id"
                        class="w-full lg:w-44 px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <option value="">Toutes</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-[10px] uppercase tracking-widest font-semibold text-ink-muted mb-1">Statut</label>
                <select name="status" id="status"
                        class="w-full lg:w-40 px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <option value="">Tous</option>
                    <option value="active"    {{ ($filters['status'] ?? '') === 'active'    ? 'selected' : '' }}>Actifs</option>
                    <option value="inactive"  {{ ($filters['status'] ?? '') === 'inactive'  ? 'selected' : '' }}>Inactifs</option>
                    <option value="featured"  {{ ($filters['status'] ?? '') === 'featured'  ? 'selected' : '' }}>Mis en avant</option>
                    <option value="low_stock" {{ ($filters['status'] ?? '') === 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 lg:flex-none h-11 px-5 rounded-xl bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i>
                Filtrer
            </button>
            @if(array_filter($filters ?? []))
                <a href="{{ route('admin.products.index') }}" class="h-11 w-11 rounded-xl bg-ivory-100 hover:bg-red-50 hover:text-red-600 flex items-center justify-center text-ink-muted" title="Réinitialiser">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- =============== LISTE =============== --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

    @if($products->isEmpty())
        <div class="py-20 text-center px-6">
            <div class="w-20 h-20 rounded-full bg-ivory-100 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-box text-2xl text-ink-muted"></i>
            </div>
            <h3 class="mt-6 font-display text-xl font-semibold">
                {{ array_filter($filters ?? []) ? 'Aucun produit ne correspond' : 'Pas encore de produit' }}
            </h3>
            <p class="mt-2 text-sm text-ink-muted max-w-md mx-auto">
                {{ array_filter($filters ?? []) ? 'Essayez de réinitialiser les filtres.' : 'Ajoutez votre premier produit pour commencer à vendre.' }}
            </p>
            @if(!array_filter($filters ?? []))
                <a href="{{ route('admin.products.create') }}" class="mt-6 inline-flex items-center gap-2 px-5 h-11 rounded-full bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors">
                    <i class="fa-solid fa-plus"></i>
                    Ajouter un produit
                </a>
            @endif
        </div>
    @else
        {{-- Table desktop --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ivory-100 text-left text-xs uppercase tracking-wider text-ink-muted font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Produit</th>
                        <th class="px-6 py-3.5">Catégorie</th>
                        <th class="px-6 py-3.5">Prix</th>
                        <th class="px-6 py-3.5 text-center">Stock</th>
                        <th class="px-6 py-3.5">Note</th>
                        <th class="px-6 py-3.5">Statut</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/5">
                    @foreach($products as $product)
                        <tr class="hover:bg-ivory-100/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0">
                                        @if($product->featured_image_url)
                                            <img src="{{ $product->featured_image_url }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-semibold truncate">{{ $product->name }}</p>
                                            @if($product->isPackage())
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase text-violet-700 bg-violet-50 px-1.5 py-0.5 rounded" title="Offre groupée">
                                                    <i class="fa-solid fa-boxes-stacked text-[9px]"></i>
                                                    Package
                                                </span>
                                            @endif
                                            @if($product->is_featured)
                                                <i class="fa-solid fa-star text-[10px] text-amber-500" title="Mis en avant"></i>
                                            @endif
                                        </div>
                                        <p class="text-xs text-ink-muted truncate font-mono">{{ $product->sku ?? $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($product->category)
                                    <span class="text-sm text-ink-muted">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-xs text-ink-muted/60">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-display font-semibold">{{ $product->formatted_price }}</p>
                                @if($product->on_sale)
                                    <p class="text-xs text-ink-muted line-through">{{ number_format($product->compare_price, 0, ',', ' ') }} F</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(!$product->track_stock)
                                    <span class="inline-flex items-center gap-1 text-xs text-ink-muted">
                                        <i class="fa-solid fa-infinity text-[10px]"></i> illimité
                                    </span>
                                @elseif($product->stock === 0)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-50 px-2.5 py-1 rounded-full">
                                        0 · Rupture
                                    </span>
                                @elseif($product->is_low_stock)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                                        {{ $product->stock }} · Faible
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full bg-ivory-100 text-xs font-bold">
                                        {{ $product->stock }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($product->has_rating)
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                                        <span class="text-sm font-semibold">{{ $product->formatted_rating }}</span>
                                        <span class="text-xs text-ink-muted">· {{ $product->rating_count }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-ink-muted/60">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($product->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        En ligne
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-muted bg-ivory-100 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-ink-muted/50"></span>
                                        Masqué
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('products.show', $product->slug) }}" target="_blank"
                                       class="w-9 h-9 rounded-lg hover:bg-ivory-100 flex items-center justify-center text-ink-muted hover:text-terracotta-500 transition-colors"
                                       title="Voir sur la boutique">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="w-9 h-9 rounded-lg hover:bg-ivory-100 flex items-center justify-center text-ink-muted hover:text-terracotta-500 transition-colors"
                                       title="Modifier">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                          onsubmit="return confirm('Archiver « {{ $product->name }} » ? Il ne sera plus visible sur la boutique.');"
                                          class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-9 h-9 rounded-lg hover:bg-red-50 flex items-center justify-center text-ink-muted hover:text-red-600 transition-colors" title="Archiver">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cartes mobile --}}
        <div class="lg:hidden divide-y divide-ink/5">
            @foreach($products as $product)
                <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3 p-4 hover:bg-ivory-100/50">
                    <div class="w-16 h-16 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0">
                        @if($product->featured_image_url)
                            <img src="{{ $product->featured_image_url }}" class="w-full h-full object-cover" alt="">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                <i class="fa-regular fa-image"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <p class="font-semibold truncate">{{ $product->name }}</p>
                            @if($product->isPackage())
                                <span class="text-[9px] font-bold uppercase text-violet-700 bg-violet-50 px-1 py-0.5 rounded">Pack</span>
                            @endif
                            @if($product->is_featured)
                                <i class="fa-solid fa-star text-[10px] text-amber-500"></i>
                            @endif
                        </div>
                        <p class="text-xs text-ink-muted truncate">{{ $product->category?->name ?? 'Sans catégorie' }}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-sm font-display font-semibold">{{ $product->formatted_price }}</span>
                            @if($product->has_rating)
                                <span class="text-xs text-ink-muted inline-flex items-center gap-1">
                                    <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>{{ $product->formatted_rating }}
                                </span>
                            @endif
                            @if(!$product->is_active)
                                <span class="text-[10px] font-bold uppercase text-ink-muted bg-ivory-100 px-1.5 py-0.5 rounded">OFF</span>
                            @endif
                            @if($product->track_stock && $product->stock === 0)
                                <span class="text-[10px] font-bold uppercase text-red-700 bg-red-50 px-1.5 py-0.5 rounded">Rupture</span>
                            @elseif($product->is_low_stock)
                                <span class="text-[10px] font-bold uppercase text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Stock {{ $product->stock }}</span>
                            @endif
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-ink-muted"></i>
                </a>
            @endforeach
        </div>
    @endif

    @if($products->hasPages())
        <div class="px-5 lg:px-6 py-4 border-t border-ink/5 bg-ivory-100/50">
            {{ $products->links() }}
        </div>
    @endif
</div>

@endsection
