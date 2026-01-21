<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::with('children')->whereNull('parent_id')->get());
    }

    public function show(Category $category)
    {
        return response()->json($category->load('children'));
    }

    // Admin Methods
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        // Check if has courses
        if ($category->courses()->exists()) {
            return response()->json(['message' => 'Cannot delete category with associated courses'], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 204);
    }
}
