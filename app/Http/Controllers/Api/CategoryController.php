<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * FR-03 - Kategorisasi Tiket
 */
class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::orderBy('category_name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_name' => ['required', 'string', 'max:100', 'unique:categories,category_name'],
            'description'   => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Category::create($data), 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'category_name' => [
                'sometimes', 'string', 'max:100',
                'unique:categories,category_name,' . $category->category_id . ',category_id',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh tiket.',
            ], 409);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
