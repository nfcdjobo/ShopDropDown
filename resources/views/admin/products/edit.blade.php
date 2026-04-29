@extends('admin.layouts.app')

@section('title', 'Modifier : ' . $product->name)
@section('page_title', 'Modifier le produit')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <a href="{{ route('admin.products.index') }}" class="hover:text-terracotta-500">Produits</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span class="truncate max-w-[30vw]">{{ $product->name }}</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('products.show', $product->slug) }}" target="_blank"
       class="hidden sm:inline-flex items-center gap-2 px-4 h-10 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors">
        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
        Voir sur la boutique
    </a>
@endsection

@section('content')

{{-- =================================================================
     BANDEAU TYPE — info contextuelle (lecture seule en édition)
     ================================================================= --}}
<div class="max-w-6xl mb-6 flex items-center gap-3 px-5 py-3 rounded-2xl ring-1 {{ $product->isPackage() ? 'bg-violet-50 ring-violet-200' : 'bg-ivory-100 ring-ink/5' }}">
    <span class="w-9 h-9 rounded-xl flex items-center justify-center {{ $product->isPackage() ? 'bg-white text-violet-700' : 'bg-white text-ink' }}">
        <i class="fa-solid {{ $product->isPackage() ? 'fa-boxes-stacked' : 'fa-box' }}"></i>
    </span>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold {{ $product->isPackage() ? 'text-violet-900' : '' }}">
            {{ $product->isPackage() ? 'Package (offre groupée)' : 'Produit simple' }}
        </p>
        <p class="text-xs text-ink-muted">
            @if($product->isPackage())
                Gérez les sous-produits dans la section dédiée plus bas.
            @else
                Article unique vendu seul.
            @endif
        </p>
    </div>
</div>

{{-- =================================================================
     FORM PRINCIPAL — attributs du produit
     ================================================================= --}}
<form id="product-form" action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="max-w-6xl">
    @csrf @method('PATCH')

    {{-- Le type reste modifiable mais hors du form principal pour clarté.
         Si vous voulez le verrouiller, transformez ce bloc en lecture seule. --}}
    <input type="hidden" name="type" value="{{ $product->type }}">

    <div class="grid xl:grid-cols-3 gap-6 lg:gap-8">

        {{-- COLONNE PRINCIPALE --}}
        <div class="xl:col-span-2 flex flex-col gap-6">

            {{-- Infos générales --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">Informations générales</h2>
                <p class="text-xs text-ink-muted mb-5">Modifier le slug change l'URL publique — pensez aux redirections.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom du produit *</label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $product->name) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Slug</label>
                        <input type="text" name="slug" id="slug"
                               value="{{ old('slug', $product->slug) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-mono">
                        @error('slug')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">SKU</label>
                        <input type="text" name="sku" id="sku"
                               value="{{ old('sku', $product->sku) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-mono">
                        @error('sku')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="short_description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Accroche courte</label>
                        <input type="text" name="short_description" id="short_description" maxlength="500"
                               value="{{ old('short_description', $product->short_description) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Description complète</label>
                        <textarea name="description" id="description" rows="6"
                                  class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-y">{{ old('description', $product->description) }}</textarea>
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
                    </div>
                </div>
            </div>

            {{-- Prix --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">Tarification</h2>
                @if($product->isPackage())
                    <p class="text-xs text-ink-muted mb-5">Prix global du package — les sous-produits n'ont pas de prix individuel.</p>
                @else
                    <p class="text-xs text-ink-muted mb-5">Tous les montants sont en FCFA.</p>
                @endif

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label for="price" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prix de vente *</label>
                        <div class="relative">
                            <input type="number" name="price" id="price" required min="0" step="1"
                                   value="{{ old('price', $product->price) }}"
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
                                   class="w-full pl-4 pr-16 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-ink-muted font-semibold">FCFA</span>
                        </div>
                    </div>
                    <div>
                        <label for="cost_price" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prix d'achat</label>
                        <div class="relative">
                            <input type="number" name="cost_price" id="cost_price" min="0" step="1"
                                   value="{{ old('cost_price', $product->cost_price) }}"
                                   class="w-full pl-4 pr-16 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-ink-muted font-semibold">FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6"
                 x-data="{ mainPreview: @js($product->featured_image_url), newGallery: [] }">
                <h2 class="font-display text-lg font-semibold mb-1">Images</h2>
                <p class="text-xs text-ink-muted mb-5">Importer une nouvelle image principale remplace l'actuelle. Cochez les images de la galerie pour les supprimer.</p>

                <div class="mb-6">
                    <label for="featured_image" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Image principale</label>
                    <label for="featured_image" class="block cursor-pointer group">
                        <div class="aspect-video rounded-2xl border-2 border-dashed border-ink/15 bg-ivory-100 flex flex-col items-center justify-center text-ink-muted hover:border-terracotta-500 transition-colors overflow-hidden relative"
                             :class="mainPreview ? 'border-solid border-ink/5 p-0' : ''">
                            <template x-if="!mainPreview">
                                <div class="flex flex-col items-center">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                    <p class="mt-3 text-sm font-semibold">Cliquer pour changer l'image</p>
                                </div>
                            </template>
                            <template x-if="mainPreview">
                                <div class="absolute inset-0">
                                    <img :src="mainPreview" class="w-full h-full object-cover" alt="">
                                    <div class="absolute inset-0 bg-ink/0 group-hover:bg-ink/30 transition-colors flex items-center justify-center">
                                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white text-ink text-xs font-semibold px-4 py-2 rounded-full">
                                            <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i>
                                            Remplacer
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*" class="hidden"
                               @change="const f=$event.target.files[0]; if(f) mainPreview=URL.createObjectURL(f)">
                    </label>
                </div>

                @if($product->images->isNotEmpty())
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-wider font-semibold text-ink-muted mb-3">
                            Galerie actuelle
                            <span class="ml-1 text-ink-muted/60 normal-case font-normal">(cochez pour supprimer)</span>
                        </p>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach($product->images as $img)
                                <label class="relative group cursor-pointer" title="Cocher pour supprimer">
                                    <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="sr-only peer">
                                    <div class="aspect-square rounded-xl overflow-hidden ring-2 ring-transparent peer-checked:ring-red-500 transition-all">
                                        <img src="{{ Storage::url($img->path) }}"
                                             class="w-full h-full object-cover peer-checked:opacity-40 group-hover:opacity-80 transition-opacity" alt="">
                                    </div>
                                    <div class="absolute inset-0 rounded-xl flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">
                                        <i class="fa-solid fa-trash text-red-500 text-lg drop-shadow"></i>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Ajouter à la galerie</p>
                    <label for="gallery" class="block cursor-pointer group">
                        <div class="border-2 border-dashed border-ink/15 rounded-2xl p-5 bg-ivory-100 hover:border-terracotta-500 transition-colors">
                            <div x-show="newGallery.length === 0" class="flex flex-col items-center text-ink-muted">
                                <i class="fa-solid fa-photo-film text-2xl"></i>
                                <p class="mt-2 text-sm font-semibold">Sélectionner des images</p>
                            </div>
                            <div x-show="newGallery.length > 0" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                                <template x-for="(src, i) in newGallery" :key="i">
                                    <div class="aspect-square rounded-lg overflow-hidden ring-2 ring-terracotta-500/30">
                                        <img :src="src" class="w-full h-full object-cover" alt="">
                                    </div>
                                </template>
                            </div>
                        </div>
                        <input type="file" name="gallery[]" id="gallery" accept="image/*" multiple class="hidden"
                               @change="newGallery = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                    </label>
                </div>
            </div>


            {{-- Preuves visuelles --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6"
     x-data="{ newProofs: [] }">
    <h2 class="font-display text-lg font-semibold mb-1">Preuves visuelles</h2>
    <p class="text-xs text-ink-muted mb-5">Captures d'écran de résultats, témoignages clients. Modifiez les légendes existantes ou cochez la corbeille pour supprimer une preuve.</p>

    {{-- Preuves existantes --}}
    @if($product->proofs->isNotEmpty())
        <div class="mb-6">
            <p class="text-xs uppercase tracking-wider font-semibold text-ink-muted mb-3">
                Preuves actuelles
                <span class="ml-1 text-ink-muted/60 normal-case font-normal">({{ $product->proofs->count() }})</span>
            </p>
            <div class="flex flex-col gap-3">
                @foreach($product->proofs as $proof)
                    <div class="flex gap-3 p-3 rounded-xl bg-ivory-100"
                         x-data="{ markedForDelete: false }">
                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 relative"
                             :class="markedForDelete ? 'opacity-40' : ''">
                            <img src="{{ $proof->url }}" class="w-full h-full object-cover" alt="{{ $proof->alt_text }}">
                            <div x-show="markedForDelete" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <i class="fa-solid fa-trash text-red-500 text-lg drop-shadow"></i>
                            </div>
                        </div>
                        <div class="flex-1 flex flex-col gap-2 min-w-0">
                            <input type="text" name="existing_proofs[{{ $proof->id }}][caption]" maxlength="200"
                                   value="{{ old("existing_proofs.{$proof->id}.caption", $proof->caption) }}"
                                   placeholder="Légende"
                                   :disabled="markedForDelete"
                                   class="w-full px-3 h-9 rounded-lg bg-white border border-transparent focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm disabled:opacity-50">
                            <input type="text" name="existing_proofs[{{ $proof->id }}][alt_text]" maxlength="200"
                                   value="{{ old("existing_proofs.{$proof->id}.alt_text", $proof->alt_text) }}"
                                   placeholder="Texte alternatif"
                                   :disabled="markedForDelete"
                                   class="w-full px-3 h-9 rounded-lg bg-white border border-transparent focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm disabled:opacity-50">
                        </div>
                        <label class="flex items-start cursor-pointer pt-1.5" title="Cocher pour supprimer">
                            <input type="checkbox" name="delete_proofs[]" value="{{ $proof->id }}"
                                   x-model="markedForDelete" class="sr-only peer">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-ink-muted hover:bg-red-50 hover:text-red-600 peer-checked:bg-red-50 peer-checked:text-red-600 transition-colors">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Ajouter de nouvelles preuves --}}
    <p class="text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Ajouter de nouvelles preuves</p>
    <label for="proofs" class="block cursor-pointer group">
        <div class="border-2 border-dashed border-ink/15 rounded-2xl p-5 bg-ivory-100 hover:border-terracotta-500 transition-colors mb-4">
            <div x-show="newProofs.length === 0" class="flex flex-col items-center text-ink-muted">
                <i class="fa-solid fa-camera-retro text-2xl"></i>
                <p class="mt-2 text-sm font-semibold">Sélectionner des images</p>
            </div>
            <div x-show="newProofs.length > 0" class="flex items-center gap-2 text-ink-muted">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span class="text-sm" x-text="newProofs.length + ' nouvelle(s) preuve(s) — voir ci-dessous'"></span>
            </div>
        </div>
        <input type="file" name="proofs[]" id="proofs" accept="image/*" multiple class="hidden"
               @change="newProofs = Array.from($event.target.files).map(f => ({ url: URL.createObjectURL(f), name: f.name }))">
    </label>

    <div x-show="newProofs.length > 0" class="flex flex-col gap-3" x-cloak>
        <template x-for="(p, i) in newProofs" :key="i">
            <div class="flex gap-3 p-3 rounded-xl bg-emerald-50/50 ring-1 ring-emerald-200">
                <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                    <img :src="p.url" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 flex flex-col gap-2 min-w-0">
                    <input type="text" :name="`proof_captions[${i}]`" maxlength="200"
                           placeholder="Légende (optionnelle)"
                           class="w-full px-3 h-9 rounded-lg bg-white border border-transparent focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    <input type="text" :name="`proof_alt_texts[${i}]`" maxlength="200"
                           placeholder="Texte alternatif"
                           class="w-full px-3 h-9 rounded-lg bg-white border border-transparent focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                </div>
            </div>
        </template>
    </div>
    @error('proofs.*')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

            {{-- SEO --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-5">SEO</h2>
                <div class="flex flex-col gap-4">
                    <div>
                        <label for="meta_title" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Titre SEO</label>
                        <input type="text" name="meta_title" id="meta_title" maxlength="150"
                               value="{{ old('meta_title', $product->meta_title) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                    </div>
                    <div>
                        <label for="meta_description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Méta-description SEO</label>
                        <textarea name="meta_description" id="meta_description" rows="3" maxlength="300"
                                  class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-none">{{ old('meta_description', $product->meta_description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="flex flex-col gap-6">

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
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}
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
                               {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-amber-400 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>
                </div>
            </div>

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

            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Stock</h2>
                @if($product->isPackage())
                    <p class="text-xs text-ink-muted mb-4 -mt-2 italic">Représente le nombre de packages disponibles à la vente.</p>
                @endif

                <div class="flex flex-col gap-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold">Suivi du stock</p>
                            <p class="text-xs text-ink-muted mt-0.5">Bloque si rupture</p>
                        </div>
                        <input type="hidden" name="track_stock" value="0">
                        <input type="checkbox" name="track_stock" id="track_stock" value="1"
                               {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="stock" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Quantité *</label>
                            <input type="number" name="stock" id="stock" required min="0"
                                   value="{{ old('stock', $product->stock) }}"
                                   class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-semibold text-center">
                        </div>
                        <div>
                            <label for="low_stock_threshold" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Seuil alerte</label>
                            <input type="number" name="low_stock_threshold" id="low_stock_threshold" min="0"
                                   value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}"
                                   class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
                        </div>
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

            {{-- Évaluation --}}
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
                </div>

                <div>
                    <label for="rating_count" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nombre d'avis</label>
                    <input type="number" name="rating_count" id="rating_count" min="0"
                           value="{{ old('rating_count', $product->rating_count) }}"
                           class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm text-center">
                </div>
            </div>


            {{-- Statistiques --}}
            <div class="bg-ivory-100 rounded-2xl ring-1 ring-ink/5 p-5">
                <h3 class="text-xs uppercase tracking-widest font-semibold text-ink-muted mb-3">Statistiques</h3>
                <dl class="text-xs space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink-muted flex items-center gap-1.5"><i class="fa-solid fa-eye w-3 text-center"></i> Vues</dt>
                        <dd class="font-semibold">{{ number_format($product->views_count) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-muted flex items-center gap-1.5"><i class="fa-solid fa-cart-shopping w-3 text-center"></i> Ventes</dt>
                        <dd class="font-semibold">{{ number_format($product->orders_count) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-muted flex items-center gap-1.5"><i class="fa-solid fa-calendar w-3 text-center"></i> Créé</dt>
                        <dd>{{ $product->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-muted flex items-center gap-1.5"><i class="fa-solid fa-clock-rotate-left w-3 text-center"></i> Modifié</dt>
                        <dd>{{ $product->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Actions principales --}}
    <div class="mt-8 flex flex-col-reverse sm:flex-row items-center justify-between gap-4">
        <button type="button"
                onclick="if(confirm('Archiver « {{ $product->name }} » ? Il ne sera plus visible sur la boutique.')) document.getElementById('archive-form').submit();"
                class="text-sm font-semibold text-red-600 hover:text-red-700 inline-flex items-center gap-2">
            <i class="fa-solid fa-box-archive"></i>
            Archiver le produit
        </button>

        <div class="flex flex-col-reverse sm:flex-row items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.products.index') }}" class="w-full sm:w-auto text-center px-6 h-11 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors inline-flex items-center justify-center gap-2">
                Retour
            </a>
            <button type="submit" class="w-full sm:w-auto px-6 h-11 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-check text-xs"></i>
                Enregistrer
            </button>
        </div>
    </div>
</form>

{{-- Form d'archivage en dehors du form principal --}}
<form id="archive-form" action="{{ route('admin.products.destroy', $product) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
</form>

{{-- =================================================================
     SOUS-PRODUITS DU PACKAGE (uniquement si type = package)
     ================================================================= --}}
@if($product->isPackage())
    <section class="max-w-6xl mt-10" x-data="{ openItemModal: false }">
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h2 class="font-display text-lg font-semibold">Sous-produits du package</h2>
                    <p class="text-xs text-ink-muted mt-1">
                        Le produit principal est mis en avant ; les bonus complètent l'offre.
                        @if($product->principalItem)
                            <span class="text-emerald-700 font-semibold">Principal défini.</span>
                        @else
                            <span class="text-amber-700 font-semibold">Aucun produit principal défini.</span>
                        @endif
                    </p>
                </div>
                <button type="button" @click="openItemModal = true"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Ajouter
                </button>
            </div>

            @if($product->items->isEmpty())
                <div class="py-10 text-center bg-ivory-100 rounded-xl">
                    <i class="fa-solid fa-boxes-stacked text-2xl text-ink-muted"></i>
                    <p class="mt-3 text-sm text-ink-muted">Aucun sous-produit. Ajoutez d'abord le produit principal, puis les bonus.</p>
                </div>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach($product->items as $item)
                        <li class="py-4 flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0">
                                @if($item->featured_image_url)
                                    <img src="{{ $item->featured_image_url }}" class="w-full h-full object-cover" alt="">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                        <i class="fa-regular fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold truncate">{{ $item->name }}</p>
                                    @if($item->package_role === \App\Models\Product::ROLE_PRINCIPAL)
                                        <span class="text-[10px] font-bold uppercase text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                                            <i class="fa-solid fa-crown text-[9px]"></i> Principal
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold uppercase text-violet-700 bg-violet-50 px-1.5 py-0.5 rounded">
                                            <i class="fa-solid fa-gift text-[9px]"></i> Bonus
                                        </span>
                                    @endif
                                </div>
                                @if($item->short_description)
                                    <p class="text-xs text-ink-muted truncate mt-0.5">{{ $item->short_description }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.products.items.destroy', [$product, $item]) }}"
                                  onsubmit="return confirm('Retirer « {{ $item->name }} » du package ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-lg hover:bg-red-50 text-ink-muted hover:text-red-600 flex items-center justify-center transition-colors" title="Retirer">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Modal : ajouter un sous-produit --}}
        <div x-show="openItemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
            <div @click="openItemModal = false" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

            <form action="{{ route('admin.products.items.store', $product) }}" method="POST" enctype="multipart/form-data"
                  class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                  x-transition>
                @csrf

                <div class="sticky top-0 bg-white px-6 py-4 border-b border-ink/5 flex items-center justify-between z-10">
                    <h3 class="font-display text-lg font-semibold">Ajouter un sous-produit</h3>
                    <button type="button" @click="openItemModal = false" class="w-9 h-9 rounded-lg hover:bg-ivory-100 flex items-center justify-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Rôle dans le package *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="package_role" value="principal" class="sr-only peer" @if(!$product->principalItem) checked @endif>
                                <div class="p-3 rounded-xl border-2 border-ink/10 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 text-center transition-all">
                                    <i class="fa-solid fa-crown text-emerald-600"></i>
                                    <p class="text-xs font-semibold mt-1">Principal</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="package_role" value="bonus" class="sr-only peer" @if($product->principalItem) checked @endif>
                                <div class="p-3 rounded-xl border-2 border-ink/10 peer-checked:border-violet-500 peer-checked:bg-violet-50 text-center transition-all">
                                    <i class="fa-solid fa-gift text-violet-600"></i>
                                    <p class="text-xs font-semibold mt-1">Bonus</p>
                                </div>
                            </label>
                        </div>
                        @if($product->principalItem)
                            <p class="mt-1.5 text-xs text-amber-700">Un produit principal existe déjà — celui-ci sera nécessairement un bonus.</p>
                        @endif
                    </div>

                    <div>
                        <label for="item_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom *</label>
                        <input type="text" name="name" id="item_name" required maxlength="160"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                    </div>

                    <div>
                        <label for="item_short_description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Accroche courte</label>
                        <input type="text" name="short_description" id="item_short_description" maxlength="500"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                    </div>

                    <div>
                        <label for="item_description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Description</label>
                        <textarea name="description" id="item_description" rows="4"
                                  class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm resize-y"></textarea>
                    </div>

                    <div>
                        <label for="item_video_url" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">URL Vidéo</label>
                        <input type="url" name="video_url" id="item_video_url" placeholder="https://youtube.com/..."
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                    </div>

                    <div>
                        <label for="item_featured_image" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Image principale</label>
                        <input type="file" name="featured_image" id="item_featured_image" accept="image/*"
                               class="w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-ink file:text-ivory-50 file:text-xs file:font-semibold hover:file:bg-terracotta-500 file:cursor-pointer">
                    </div>

                    <div>
                        <label for="item_gallery" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Galerie</label>
                        <input type="file" name="gallery[]" id="item_gallery" accept="image/*" multiple
                               class="w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-ivory-100 file:text-ink file:text-xs file:font-semibold hover:file:bg-ivory-200 file:cursor-pointer">
                    </div>
                </div>

                <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-ink/5 flex justify-end gap-3">
                    <button type="button" @click="openItemModal = false" class="px-5 h-10 rounded-full bg-ivory-100 text-sm font-semibold hover:bg-ivory-200 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 h-10 rounded-full bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition-colors inline-flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        Ajouter au package
                    </button>
                </div>
            </form>
        </div>
    </section>
@endif

{{-- =================================================================
     AVIS CLIENTS (max 3)
     ================================================================= --}}
@php $reviewsLimitReached = $product->reviews->count() >= \App\Models\Product::MAX_REVIEWS; @endphp

<section class="max-w-6xl mt-8" x-data="{ openReviewModal: false, rating: 5 }">
    <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <h2 class="font-display text-lg font-semibold">Avis clients</h2>
                <p class="text-xs text-ink-muted mt-1">
                    {{ $product->reviews->count() }} / {{ \App\Models\Product::MAX_REVIEWS }} avis publiés
                </p>
            </div>
            @if($reviewsLimitReached)
                <span class="inline-flex items-center gap-1.5 px-4 h-10 rounded-full bg-ivory-100 text-ink-muted text-xs font-semibold">
                    <i class="fa-solid fa-circle-info"></i>
                    Limite atteinte
                </span>
            @else
                <button type="button" @click="openReviewModal = true"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Ajouter un avis
                </button>
            @endif
        </div>

        @if($product->reviews->isEmpty())
            <div class="py-10 text-center bg-ivory-100 rounded-xl">
                <i class="fa-solid fa-star text-2xl text-ink-muted"></i>
                <p class="mt-3 text-sm text-ink-muted">Aucun avis. Ajoutez-en pour rassurer vos visiteurs.</p>
            </div>
        @else
            <ul class="divide-y divide-ink/5">
                @foreach($product->reviews as $review)
                    <li class="py-4 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-ivory-100 flex items-center justify-center font-display font-bold text-sm text-ink-muted flex-shrink-0">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->author_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-sm">{{ $review->author_name }}</p>
                                @if($review->author_location)
                                    <span class="text-xs text-ink-muted">· {{ $review->author_location }}</span>
                                @endif
                                @if($review->reviewed_on)
                                    <span class="text-xs text-ink-muted">· {{ $review->reviewed_on->format('d/m/Y') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-0.5 mt-1 text-amber-500">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star text-xs {{ $i <= $review->rating ? '' : 'text-ink-muted/20' }}"></i>
                                @endfor
                            </div>
                            @if($review->title)
                                <p class="font-semibold text-sm mt-1.5">{{ $review->title }}</p>
                            @endif
                            <p class="text-sm text-ink-muted mt-1 leading-relaxed">{{ $review->comment }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.products.reviews.destroy', [$product, $review]) }}"
                              onsubmit="return confirm('Supprimer cet avis ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-9 h-9 rounded-lg hover:bg-red-50 text-ink-muted hover:text-red-600 flex items-center justify-center transition-colors" title="Supprimer">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Modal : ajouter un avis --}}
    <div x-show="openReviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div @click="openReviewModal = false" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

        <form action="{{ route('admin.products.reviews.store', $product) }}" method="POST"
              class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto" x-transition>
            @csrf

            <div class="sticky top-0 bg-white px-6 py-4 border-b border-ink/5 flex items-center justify-between z-10">
                <h3 class="font-display text-lg font-semibold">Ajouter un avis</h3>
                <button type="button" @click="openReviewModal = false" class="w-9 h-9 rounded-lg hover:bg-ivory-100 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="review_author_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom de l'auteur *</label>
                        <input type="text" name="author_name" id="review_author_name" required maxlength="120"
                               placeholder="Aïcha K."
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                    </div>
                    <div>
                        <label for="review_author_location" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Localité</label>
                        <input type="text" name="author_location" id="review_author_location" maxlength="120"
                               placeholder="Cocody, Abidjan"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Note *</label>
                    <div class="flex items-center gap-1.5">
                        <template x-for="i in 5" :key="i">
                            <button type="button" @click="rating = i"
                                    class="text-2xl text-amber-500 hover:scale-110 transition-transform"
                                    :class="rating >= i ? '' : 'text-ink-muted/20'">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </template>
                        <span class="ml-3 text-sm font-semibold tabular-nums" x-text="rating + ' / 5'"></span>
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                </div>

                <div>
                    <label for="review_title" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Titre</label>
                    <input type="text" name="title" id="review_title" maxlength="160"
                           placeholder="Très satisfaite !"
                           class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                </div>

                <div>
                    <label for="review_comment" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Commentaire *</label>
                    <textarea name="comment" id="review_comment" rows="4" required maxlength="1000"
                              placeholder="Le produit est de très bonne qualité, livraison rapide…"
                              class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm resize-y"></textarea>
                </div>

                <div>
                    <label for="review_reviewed_on" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Date d'affichage</label>
                    <input type="date" name="reviewed_on" id="review_reviewed_on" max="{{ date('Y-m-d') }}"
                           value="{{ date('Y-m-d') }}"
                           class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                    <p class="mt-1 text-xs text-ink-muted">Antidatez pour donner un effet « historique » crédible.</p>
                </div>
            </div>

            <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-ink/5 flex justify-end gap-3">
                <button type="button" @click="openReviewModal = false" class="px-5 h-10 rounded-full bg-ivory-100 text-sm font-semibold hover:bg-ivory-200 transition-colors">
                    Annuler
                </button>
                <button type="submit" class="px-5 h-10 rounded-full bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-check text-xs"></i>
                    Publier l'avis
                </button>
            </div>
        </form>
    </div>
</section>

{{-- =================================================================
     FAQs (max 3)
     ================================================================= --}}
@php $faqsLimitReached = $product->faqs->count() >= \App\Models\Product::MAX_FAQS; @endphp

<section class="max-w-6xl mt-8 mb-12" x-data="{ openFaqModal: false }">
    <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <h2 class="font-display text-lg font-semibold">Questions fréquentes</h2>
                <p class="text-xs text-ink-muted mt-1">
                    {{ $product->faqs->count() }} / {{ \App\Models\Product::MAX_FAQS }} questions publiées
                </p>
            </div>
            @if($faqsLimitReached)
                <span class="inline-flex items-center gap-1.5 px-4 h-10 rounded-full bg-ivory-100 text-ink-muted text-xs font-semibold">
                    <i class="fa-solid fa-circle-info"></i>
                    Limite atteinte
                </span>
            @else
                <button type="button" @click="openFaqModal = true"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700 transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Ajouter
                </button>
            @endif
        </div>

        @if($product->faqs->isEmpty())
            <div class="py-10 text-center bg-ivory-100 rounded-xl">
                <i class="fa-solid fa-circle-question text-2xl text-ink-muted"></i>
                <p class="mt-3 text-sm text-ink-muted">Aucune FAQ. Anticipez les questions de vos clients.</p>
            </div>
        @else
            <ul class="divide-y divide-ink/5">
                @foreach($product->faqs as $faq)
                    <li class="py-4 flex items-start gap-4">
                        <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center text-sky-700 flex-shrink-0">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm">{{ $faq->question }}</p>
                            <p class="text-sm text-ink-muted mt-1 leading-relaxed">{{ $faq->answer }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.products.faqs.destroy', [$product, $faq]) }}"
                              onsubmit="return confirm('Supprimer cette question ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-9 h-9 rounded-lg hover:bg-red-50 text-ink-muted hover:text-red-600 flex items-center justify-center transition-colors" title="Supprimer">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Modal : ajouter une FAQ --}}
    <div x-show="openFaqModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div @click="openFaqModal = false" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

        <form action="{{ route('admin.products.faqs.store', $product) }}" method="POST"
              class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto" x-transition>
            @csrf

            <div class="sticky top-0 bg-white px-6 py-4 border-b border-ink/5 flex items-center justify-between z-10">
                <h3 class="font-display text-lg font-semibold">Ajouter une question</h3>
                <button type="button" @click="openFaqModal = false" class="w-9 h-9 rounded-lg hover:bg-ivory-100 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-4">
                <div>
                    <label for="faq_question" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Question *</label>
                    <input type="text" name="question" id="faq_question" required maxlength="255"
                           placeholder="Le produit est-il livré rapidement ?"
                           class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm">
                </div>

                <div>
                    <label for="faq_answer" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Réponse *</label>
                    <textarea name="answer" id="faq_answer" rows="5" required maxlength="2000"
                              placeholder="Oui, livraison sous 24h à Abidjan et 48-72h dans le reste du pays."
                              class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none text-sm resize-y"></textarea>
                </div>
            </div>

            <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-ink/5 flex justify-end gap-3">
                <button type="button" @click="openFaqModal = false" class="px-5 h-10 rounded-full bg-ivory-100 text-sm font-semibold hover:bg-ivory-200 transition-colors">
                    Annuler
                </button>
                <button type="submit" class="px-5 h-10 rounded-full bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700 transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-check text-xs"></i>
                    Publier la question
                </button>
            </div>
        </form>
    </div>
</section>

@endsection
