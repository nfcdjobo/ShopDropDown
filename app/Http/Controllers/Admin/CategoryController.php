<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * CRUD des catégories de produits, avec support de la hiérarchie parent/enfant.
 */
class CategoryController extends Controller
{
    private const IMAGE_DISK = 'public';
    private const IMAGE_DIR  = 'categories';

    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->ordered()
            ->paginate(30);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category'         => new Category(['is_active' => true]),
            'parentCategories' => Category::active()->roots()->ordered()->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($image = $request->file('image')) {
            $data['image'] = $image->store(self::IMAGE_DIR, self::IMAGE_DISK);
        }

        $category = Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Catégorie « {$category->name } » créée.");
    }

    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            // Exclut la catégorie en cours et ses descendants pour éviter les boucles
            'parentCategories' => Category::active()
                ->roots()
                ->where('id', '!=', $category->id)
                ->ordered()
                ->get(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        // Garde-fou : une catégorie ne peut pas être son propre parent.
        if (($data['parent_id'] ?? null) === $category->id) {
            $data['parent_id'] = null;
        }

        if ($image = $request->file('image')) {
            if ($category->image) {
                Storage::disk(self::IMAGE_DISK)->delete($category->image);
            }
            $data['image'] = $image->store(self::IMAGE_DIR, self::IMAGE_DISK);
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Les produits liés passent à category_id = null (nullOnDelete dans la migration).
        if ($category->image) {
            Storage::disk(self::IMAGE_DISK)->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
