@extends('admin.layouts.app')

@section('title', 'Catégories')
@section('page_title', 'Catégories')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>Catégories</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft">
        <i class="fa-solid fa-plus"></i>
        <span class="hidden sm:inline">Nouvelle catégorie</span>
        <span class="sm:hidden">Nouvelle</span>
    </a>
@endsection

@section('content')

{{-- En-tête --}}
<div class="mb-6 lg:mb-8">
    <p class="text-ink-muted text-sm">Organisez votre catalogue en catégories pour faciliter la navigation sur la boutique.</p>
</div>

{{-- =============== LISTE =============== --}}
<div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

    @if($categories->isEmpty())
        <div class="py-20 text-center px-6">
            <div class="w-20 h-20 rounded-full bg-ivory-100 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-layer-group text-2xl text-ink-muted"></i>
            </div>
            <h3 class="mt-6 font-display text-xl font-semibold">Aucune catégorie pour l'instant</h3>
            <p class="mt-2 text-sm text-ink-muted max-w-md mx-auto">Commencez par créer votre première catégorie pour classer vos produits.</p>
            <a href="{{ route('admin.categories.create') }}" class="mt-6 inline-flex items-center gap-2 px-5 h-11 rounded-full bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors">
                <i class="fa-solid fa-plus"></i>
                Créer une catégorie
            </a>
        </div>
    @else
        {{-- Table desktop --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ivory-100 text-left text-xs uppercase tracking-wider text-ink-muted font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Catégorie</th>
                        <th class="px-6 py-3.5">Parent</th>
                        <th class="px-6 py-3.5 text-center">Produits</th>
                        <th class="px-6 py-3.5 text-center">Ordre</th>
                        <th class="px-6 py-3.5">Statut</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/5">
                    @foreach($categories as $category)
                        <tr class="hover:bg-ivory-100/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0">
                                        @if($category->image)
                                            <img src="{{ \Storage::disk('public')->url($category->image) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                                <i class="fa-solid fa-layer-group text-sm"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate">{{ $category->name }}</p>
                                        <p class="text-xs text-ink-muted truncate font-mono">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($category->parent)
                                    <span class="text-sm text-ink-muted inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-turn-up rotate-90 text-[10px]"></i>
                                        {{ $category->parent->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-ink-muted/60">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full bg-ivory-100 text-xs font-bold">
                                    {{ $category->products_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-ink-muted">{{ $category->sort_order ?? 0 }}</td>
                            <td class="px-6 py-4">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-muted bg-ivory-100 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-ink-muted/50"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="w-9 h-9 rounded-lg hover:bg-ivory-100 flex items-center justify-center text-ink-muted hover:text-terracotta-500 transition-colors"
                                       title="Modifier">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Supprimer la catégorie « {{ $category->name }} » ? Les produits liés ne seront pas supprimés mais n\'auront plus de catégorie.');"
                                          class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-9 h-9 rounded-lg hover:bg-red-50 flex items-center justify-center text-ink-muted hover:text-red-600 transition-colors" title="Supprimer">
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
            @foreach($categories as $category)
                <div class="p-4 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0">
                        @if($category->image)
                            <img src="{{ \Storage::disk('public')->url($category->image) }}" class="w-full h-full object-cover" alt="">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold truncate">{{ $category->name }}</p>
                            @if(!$category->is_active)
                                <span class="text-[10px] font-bold uppercase text-ink-muted bg-ivory-100 px-1.5 py-0.5 rounded">OFF</span>
                            @endif
                        </div>
                        <p class="text-xs text-ink-muted truncate">
                            {{ $category->products_count }} produit{{ $category->products_count > 1 ? 's' : '' }}
                            @if($category->parent) · ⤵ {{ $category->parent->name }} @endif
                        </p>
                    </div>
                    <a href="{{ route('admin.categories.edit', $category) }}"
                       class="w-10 h-10 rounded-full bg-ivory-100 flex items-center justify-center text-ink flex-shrink-0">
                        <i class="fa-solid fa-chevron-right text-sm"></i>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    @if($categories->hasPages())
        <div class="px-5 lg:px-6 py-4 border-t border-ink/5 bg-ivory-100/50">
            {{ $categories->links() }}
        </div>
    @endif
</div>

@endsection
