<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::withCount('items')->orderBy('category_name')->get();
        return view('master.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:master_item_categories,category_name',
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah ada.',
        ]);

        ItemCategory::create($validated);

        return redirect()->route('master.categories.index')
            ->with('success', 'Kategori item berhasil ditambahkan.');
    }

    public function update(Request $request, ItemCategory $category)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:master_item_categories,category_name,' . $category->id,
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah ada.',
        ]);

        $category->update($validated);

        return redirect()->route('master.categories.index')
            ->with('success', 'Kategori item berhasil diperbarui.');
    }

    public function destroy(ItemCategory $category)
    {
        if ($category->items()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh beberapa item.');
        }

        $category->delete();

        return redirect()->route('master.categories.index')
            ->with('success', 'Kategori item berhasil dihapus.');
    }
}
