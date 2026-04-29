@extends('admin.layouts.app')

@section('title', 'Nouveau produit')
@section('page_title', 'Nouveau produit')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <a href="{{ route('admin.products.index') }}" class="hover:text-terracotta-500">Produits</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>Nouveau</span>
@endsection

@section('content')

<form id="product-form"
      action="{{ route('admin.products.store') }}"
      method="POST"
      enctype="multipart/form-data"
      class="max-w-6xl"
      x-data="{ type: '{{ old('type', $product->type ?? 'simple') }}' }">
    @csrf

    {{-- =============================================================
         CHOIX DU TYPE — première étape
         ============================================================= --}}
    <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6 mb-6">
        <h2 class="font-display text-lg font-semibold mb-1">Type de produit</h2>
        <p class="text-xs text-ink-muted mb-5">Un produit simple est vendu seul. Un package regroupe plusieurs sous-produits sous un prix unique.</p>

        <div class="grid sm:grid-cols-2 gap-3">
            <label class="cursor-pointer">
                <input type="radio" name="type" value="simple" x-model="type" class="sr-only peer">
                <div class="p-4 rounded-2xl border-2 border-ink/10 peer-checked:border-terracotta-500 peer-checked:bg-terracotta-50 transition-all">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-ivory-100 flex items-center justify-center">
                            <i class="fa-solid fa-box text-ink"></i>
                        </span>
                        <div>
                            <p class="font-semibold text-sm">Produit simple</p>
                            <p class="text-xs text-ink-muted">Article unique avec son propre prix.</p>
                        </div>
                    </div>
                </div>
            </label>

            <label class="cursor-pointer">
                <input type="radio" name="type" value="package" x-model="type" class="sr-only peer">
                <div class="p-4 rounded-2xl border-2 border-ink/10 peer-checked:border-terracotta-500 peer-checked:bg-terracotta-50 transition-all">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                            <i class="fa-solid fa-boxes-stacked text-violet-700"></i>
                        </span>
                        <div>
                            <p class="font-semibold text-sm">Package (offre groupée)</p>
                            <p class="text-xs text-ink-muted">Un produit principal + bonus à un prix unique.</p>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div x-show="type === 'package'" x-cloak class="mt-4 p-4 rounded-xl bg-violet-50 border border-violet-200 flex gap-3">
            <i class="fa-solid fa-circle-info text-violet-700 mt-0.5"></i>
            <div class="text-xs text-violet-900">
                <p class="font-semibold">Ajout des sous-produits</p>
                <p class="mt-1">Les sous-produits (principal + bonus) s'ajoutent à ce package <strong>après sa création</strong>, depuis la page d'édition.</p>
            </div>
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-6 lg:gap-8">

        {{-- ============================================================
             COLONNE PRINCIPALE (2/3)
             ============================================================ --}}
        <div class="xl:col-span-2 flex flex-col gap-6">

            {{-- Infos générales --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">Informations générales</h2>
                <p class="text-xs text-ink-muted mb-5">Le slug et le SKU sont générés automatiquement si laissés vides.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom du produit *</label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $product->name) }}"
                               :placeholder="type === 'package' ? 'Ex. Pack beauté complet' : 'Ex. Sac en cuir artisanal'"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Slug (URL)</label>
                        <input type="text" name="slug" id="slug"
                               value="{{ old('slug', $product->slug) }}"
                               placeholder="auto"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-mono">
                        @error('slug')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">SKU</label>
                        <input type="text" name="sku" id="sku"
                               value="{{ old('sku', $product->sku) }}"
                               placeholder="SD-XXXX (auto)"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-mono">
                        @error('sku')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="short_description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Accroche courte</label>
                        <input type="text" name="short_description" id="short_description"
                               value="{{ old('short_description', $product->short_description) }}"
                               maxlength="500"
                               placeholder="Résumé en une ligne (max. 500 car.)"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        @error('short_description')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Description complète</label>
                        <textarea name="description" id="description" rows="6"
                                  placeholder="Description détaillée : matières, dimensions, conseils d'utilisation…"
                                  class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-y">{{ old('description', $product->description) }}</textarea>
                        @error('description')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="video_url" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">URL Vidéo</label>
                        <div class="relative">
                            <i class="fa-solid fa-video absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                            <input type="url" name="video_url" id="video_url"
                                   value="{{ old('video_url', $product->video_url) }}"
                                   placeholder="https://youtube.com/watch?v=..."
                                   class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        </div>
                        @error('video_url')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Prix --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">Tarification</h2>
                <p class="text-xs text-ink-muted mb-5" x-text="type === 'package' ? 'Prix global du package — les sous-produits n\'ont pas de prix individuel.' : 'Tous les montants sont en FCFA. Le prix barré déclenche l\'affichage d\'une promotion.'"></p>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label for="price" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prix de vente *</label>
                        <div class="relative">
                            <input type="number" name="price" id="price" required min="0" step="1"
                                   value="{{ old('price', $product->price) }}"
                                   placeholder="0"
                                   class="w-full pl-4 pr-16 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-semibold">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-ink-muted font-semibold">FCFA</span>
                        </div>
                        @error('price')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="compare_price" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prix barré</label>
                        <div class="relative">
                            <input type="number" name="compare_price" id="compare_price" min="0" step="1"
                                   value="{{ old('compare_price', $product->compare_price) }}"
                                   placeholder="0"
                                   class="w-full pl-4 pr-16 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-ink-muted font-semibold">FCFA</span>
                        </div>
                    </div>
                    <div>
                        <label for="cost_price" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prix d'achat</label>
                        <div class="relative">
                            <input type="number" name="cost_price" id="cost_price" min="0" step="1"
                                   value="{{ old('cost_price', $product->cost_price) }}"
                                   placeholder="0"
                                   class="w-full pl-4 pr-16 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-ink-muted font-semibold">FCFA</span>
                        </div>
                        <p class="mt-1 text-xs text-ink-muted">Confidentiel — calcul des marges</p>
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6"
                 x-data="{ mainPreview: null, gallery: [] }">
                <h2 class="font-display text-lg font-semibold mb-1">Images</h2>
                <p class="text-xs text-ink-muted mb-5">Image principale obligatoire. Galerie : jusqu'à 10 images supplémentaires (4 Mo max chacune).</p>

                <div class="mb-5">
                    <label for="featured_image" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Image principale</label>
                    <label for="featured_image" class="block cursor-pointer group">
                        <div class="aspect-video rounded-2xl border-2 border-dashed border-ink/15 bg-ivory-100 flex flex-col items-center justify-center text-ink-muted hover:border-terracotta-500 hover:text-terracotta-500 transition-colors overflow-hidden relative"
                             :class="{ 'border-solid border-ink/5 p-0': mainPreview }">
                            <template x-if="!mainPreview">
                                <div class="flex flex-col items-center">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                    <p class="mt-3 text-sm font-semibold">Importer l'image principale</p>
                                    <p class="text-xs mt-1">JPG, PNG, WebP · 4 Mo max.</p>
                                </div>
                            </template>
                            <template x-if="mainPreview">
                                <img :src="mainPreview" class="absolute inset-0 w-full h-full object-cover" alt="Aperçu">
                            </template>
                        </div>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*" class="hidden"
                               @change="const f=$event.target.files[0]; if(f) mainPreview=URL.createObjectURL(f)">
                    </label>
                    @error('featured_image')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="gallery" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Galerie</label>
                    <label for="gallery" class="block cursor-pointer group">
                        <div class="border-2 border-dashed border-ink/15 rounded-2xl p-6 bg-ivory-100 hover:border-terracotta-500 transition-colors">
                            <div x-show="gallery.length === 0" class="flex flex-col items-center text-ink-muted">
                                <i class="fa-solid fa-photo-film text-2xl"></i>
                                <p class="mt-2 text-sm font-semibold">Ajouter des images à la galerie</p>
                                <p class="text-xs mt-1">Sélection multiple possible</p>
                            </div>
                            <div x-show="gallery.length > 0" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                                <template x-for="(src, i) in gallery" :key="i">
                                    <div class="aspect-square rounded-lg overflow-hidden">
                                        <img :src="src" class="w-full h-full object-cover" alt="">
                                    </div>
                                </template>
                            </div>
                        </div>
                        <input type="file" name="gallery[]" id="gallery" accept="image/*" multiple class="hidden"
                               @change="gallery = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                    </label>
                    @error('gallery.*')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>



            {{-- Preuves visuelles --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6"
     x-data="{ proofs: [] }">
    <h2 class="font-display text-lg font-semibold mb-1">Preuves visuelles</h2>
    <p class="text-xs text-ink-muted mb-5">Captures d'écran de résultats, témoignages clients, avant/après. Jusqu'à 10 images (4 Mo max chacune).</p>

    <label for="proofs" class="block cursor-pointer group">
        <div class="border-2 border-dashed border-ink/15 rounded-2xl p-6 bg-ivory-100 hover:border-terracotta-500 transition-colors mb-4">
            <div x-show="proofs.length === 0" class="flex flex-col items-center text-ink-muted">
                <i class="fa-solid fa-camera-retro text-2xl"></i>
                <p class="mt-2 text-sm font-semibold">Importer des preuves</p>
                <p class="text-xs mt-1">Sélection multiple possible</p>
            </div>
            <div x-show="proofs.length > 0" class="flex items-center gap-2 text-ink-muted">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span class="text-sm" x-text="proofs.length + ' image(s) sélectionnée(s) — cliquez pour modifier'"></span>
            </div>
        </div>
        <input type="file" name="proofs[]" id="proofs" accept="image/*" multiple class="hidden"
               @change="proofs = Array.from($event.target.files).map(f => ({ url: URL.createObjectURL(f), name: f.name }))">
    </label>

    {{-- Légendes & alt par image sélectionnée --}}
    <div x-show="proofs.length > 0" class="flex flex-col gap-3" x-cloak>
        <template x-for="(p, i) in proofs" :key="i">
            <div class="flex gap-3 p-3 rounded-xl bg-ivory-100">
                <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                    <img :src="p.url" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 flex flex-col gap-2 min-w-0">
                    <input type="text" :name="`proof_captions[${i}]`" maxlength="200"
                           placeholder="Légende (optionnelle)"
                           class="w-full px-3 h-9 rounded-lg bg-white border border-transparent focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <input type="text" :name="`proof_alt_texts[${i}]`" maxlength="200"
                           placeholder="Texte alternatif (accessibilité)"
                           class="w-full px-3 h-9 rounded-lg bg-white border border-transparent focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                </div>
            </div>
        </template>
    </div>
    @error('proofs.*')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

            {{-- SEO --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">SEO</h2>
                <p class="text-xs text-ink-muted mb-5">Laissez vide pour utiliser le nom et la description.</p>

                <div class="flex flex-col gap-4">
                    <div>
                        <label for="meta_title" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Titre SEO</label>
                        <input type="text" name="meta_title" id="meta_title" maxlength="150"
                               value="{{ old('meta_title', $product->meta_title) }}"
                               placeholder="Titre pour les moteurs de recherche"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    </div>
                    <div>
                        <label for="meta_description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Méta-description SEO</label>
                        <textarea name="meta_description" id="meta_description" rows="3" maxlength="300"
                                  placeholder="Description pour les moteurs (max. 300 car.)"
                                  class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-none">{{ old('meta_description', $product->meta_description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             SIDEBAR (1/3)
             ============================================================ --}}
        <div class="flex flex-col gap-6">

            {{-- Visibilité --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Visibilité</h2>
                <div class="flex flex-col gap-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold">Produit actif</p>
                            <p class="text-xs text-ink-muted mt-0.5">Visible sur la boutique</p>
                        </div>
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>

                    <div class="h-px bg-ink/5"></div>

                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold">Mis en avant <i class="fa-solid fa-star text-[10px] text-amber-500"></i></p>
                            <p class="text-xs text-ink-muted mt-0.5">Section coups de cœur</p>
                        </div>
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                               {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-amber-400 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>
                </div>
            </div>

            {{-- Organisation --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Organisation</h2>
                <div>
                    <label for="category_id" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Catégorie</label>
                    <select name="category_id" id="category_id"
                            class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        <option value="">— Sans catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Stock --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Stock</h2>
                <p x-show="type === 'package'" x-cloak class="text-xs text-ink-muted mb-4 -mt-2 italic">Représente le nombre de packages disponibles à la vente.</p>

                <div class="flex flex-col gap-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold">Suivi du stock</p>
                            <p class="text-xs text-ink-muted mt-0.5">Bloque si rupture</p>
                        </div>
                        <input type="hidden" name="track_stock" value="0">
                        <input type="checkbox" name="track_stock" id="track_stock" value="1"
                               {{ old('track_stock', $product->track_stock ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>

                    <div>
                        <label for="stock" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Quantité *</label>
                        <input type="number" name="stock" id="stock" required min="0"
                               value="{{ old('stock', $product->stock ?? 0) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-semibold text-center">
                    </div>

                    <div>
                        <label for="low_stock_threshold" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Seuil d'alerte</label>
                        <input type="number" name="low_stock_threshold" id="low_stock_threshold" min="0"
                               value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
                    </div>
                </div>
            </div>



            {{-- Délai de disponibilité --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-1">Délai de disponibilité</h2>
                <p class="text-xs text-ink-muted mb-4">Affiché sur la fiche produit. Laissez vide pour ne rien afficher.</p>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label for="availability_min_days" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Min. (jours)</label>
                        <input type="number" name="availability_min_days" id="availability_min_days" min="0" max="365"
                            value="{{ old('availability_min_days', $product->availability_min_days) }}"
                            placeholder="2"
                            class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
                        @error('availability_min_days')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="availability_max_days" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Max. (jours)</label>
                        <input type="number" name="availability_max_days" id="availability_max_days" min="0" max="365"
                            value="{{ old('availability_max_days', $product->availability_max_days) }}"
                            placeholder="4"
                            class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
                        @error('availability_max_days')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="availability_label" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Libellé personnalisé</label>
                    <input type="text" name="availability_label" id="availability_label" maxlength="100"
                        value="{{ old('availability_label', $product->availability_label) }}"
                        placeholder="Ex. En précommande"
                        class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <p class="mt-1 text-xs text-ink-muted">Si renseigné, remplace l'intervalle ci-dessus.</p>
                    @error('availability_label')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>


            {{-- Offre limitée (compteur d'urgence) --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
    <h2 class="font-display text-lg font-semibold mb-1">Offre limitée</h2>
    <p class="text-xs text-ink-muted mb-4">Affiche un compteur d'urgence qui redémarre automatiquement pour chaque visiteur. Visible uniquement si un prix barré est défini.</p>

    <div>
        <label for="offer_duration_hours" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Durée du compteur (heures)</label>
        <input type="number" name="offer_duration_hours" id="offer_duration_hours"
               min="1" max="720"
               value="{{ old('offer_duration_hours', $product->offer_duration_hours) }}"
               placeholder="24"
               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
        <p class="mt-1 text-xs text-ink-muted">Laissez vide pour désactiver. Ex. 24 = 1 jour, 72 = 3 jours.</p>
        @error('offer_duration_hours')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>





            {{-- Évaluation (étoiles) --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5"
                 x-data="{ rating: {{ (float) old('rating_value', $product->rating_value ?? 0) }} }">
                <h2 class="font-display text-lg font-semibold mb-1">Évaluation</h2>
                <p class="text-xs text-ink-muted mb-4">Note et nombre d'avis affichés sur la fiche publique.</p>

                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label for="rating_value" class="text-xs uppercase tracking-wider font-semibold text-ink-muted">Note</label>
                        <span class="text-sm font-display font-semibold tabular-nums" x-text="rating.toFixed(1) + ' / 5'"></span>
                    </div>
                    <div class="flex items-center gap-1 mb-2 text-amber-500">
                        <template x-for="i in 5" :key="i">
                            <i class="fa-solid"
                               :class="rating >= i ? 'fa-star' : (rating >= i - 0.5 ? 'fa-star-half-stroke' : 'fa-star text-ink-muted/20')"></i>
                        </template>
                    </div>
                    <input type="range" name="rating_value" id="rating_value"
                           min="0" max="5" step="0.1" x-model.number="rating"
                           class="w-full accent-terracotta-500">
                    @error('rating_value')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="rating_count" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nombre d'avis</label>
                    <input type="number" name="rating_count" id="rating_count" min="0"
                           value="{{ old('rating_count', $product->rating_count ?? 0) }}"
                           placeholder="0"
                           class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
                    @error('rating_count')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-8 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.products.index') }}" class="w-full sm:w-auto text-center px-6 h-11 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-xmark text-xs"></i>
            Annuler
        </a>
        <button type="submit" class="w-full sm:w-auto px-6 h-11 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-check text-xs"></i>
            Créer le produit
        </button>
    </div>
</form>

@endsection
