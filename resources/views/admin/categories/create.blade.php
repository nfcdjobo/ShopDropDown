@extends('admin.layouts.app')

@section('title', 'Nouvelle catégorie')
@section('page_title', 'Nouvelle catégorie')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <a href="{{ route('admin.categories.index') }}" class="hover:text-terracotta-500">Catégories</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>Nouvelle</span>
@endsection

@section('content')

<form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="max-w-5xl">
    @csrf

    <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

        {{-- ======== COLONNE PRINCIPALE ======== --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Infos générales --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">Informations générales</h2>
                <p class="text-xs text-ink-muted mb-5">Le nom est obligatoire, le slug sera généré automatiquement si vide.</p>

                <div class="flex flex-col gap-4">
                    <div>
                        <label for="name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom *</label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $category->name) }}"
                               placeholder="Ex. Mode femme"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Slug</label>
                        <input type="text" name="slug" id="slug"
                               value="{{ old('slug', $category->slug) }}"
                               placeholder="mode-femme (auto)"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm font-mono">
                        @error('slug')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  placeholder="Description courte affichée sur la boutique…"
                                  class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-none">{{ old('description', $category->description) }}</textarea>
                        @error('description')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Image --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6"
                 x-data="{ preview: null, fileName: '' }">
                <h2 class="font-display text-lg font-semibold mb-1">Image</h2>
                <p class="text-xs text-ink-muted mb-5">JPG, PNG ou WebP. 2 Mo maximum.</p>

                <label for="image" class="block group cursor-pointer">
                    <div class="aspect-video rounded-2xl border-2 border-dashed border-ink/15 bg-ivory-100 flex flex-col items-center justify-center text-ink-muted hover:border-terracotta-500 hover:text-terracotta-500 transition-colors overflow-hidden relative"
                         :class="{ 'border-solid border-terracotta-500 p-0': preview }">
                        <template x-if="!preview">
                            <div class="flex flex-col items-center">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                                <p class="mt-3 text-sm font-semibold">Cliquez pour importer une image</p>
                                <p class="text-xs mt-1">ou glissez-déposez un fichier ici</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <img :src="preview" class="absolute inset-0 w-full h-full object-cover" alt="Aperçu">
                        </template>
                    </div>
                    <p x-show="fileName" x-text="fileName" class="mt-2 text-xs text-ink-muted text-center"></p>
                    <input type="file" name="image" id="image" accept="image/*" class="hidden"
                           @change="
                                const f = $event.target.files[0];
                                if (f) { preview = URL.createObjectURL(f); fileName = f.name; }
                           ">
                </label>
                @error('image')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- ======== SIDEBAR PARAMÈTRES ======== --}}
        <div class="flex flex-col gap-6">

            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Visibilité</h2>

                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-sm font-semibold">Catégorie active</p>
                        <p class="text-xs text-ink-muted mt-0.5">Visible sur la boutique</p>
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                           class="sr-only peer">
                    <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5"></span>
                </label>
            </div>

            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Organisation</h2>

                <div class="flex flex-col gap-4">
                    <div>
                        <label for="parent_id" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Catégorie parente</label>
                        <select name="parent_id" id="parent_id"
                                class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            <option value="">— Aucune (racine)</option>
                            @foreach($parentCategories as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id', $category->parent_id) === $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Ordre d'affichage</label>
                        <input type="number" name="sort_order" id="sort_order" min="0"
                               value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        <p class="mt-1 text-xs text-ink-muted">Plus petit = affiché en premier</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-8 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.categories.index') }}" class="w-full sm:w-auto text-center px-6 h-11 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-xmark text-xs"></i>
            Annuler
        </a>
        <button type="submit" class="w-full sm:w-auto px-6 h-11 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-check text-xs"></i>
            Créer la catégorie
        </button>
    </div>
</form>

@endsection
