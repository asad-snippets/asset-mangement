<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return response()->success($categories, 'Categories fetched');
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        return response()->success($category, 'Category fetched');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:255',
            'icon' => 'required|string|max:100',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return response()->created($category, 'Category created');
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => 'nullable|string|max:255',
            'icon' => 'required|string|max:100',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        Asset::where('category_id', $category->id)->update([
            'category' => $category->name,
        ]);

        return response()->success($category, 'Category updated');
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        if (Asset::where('category_id', $category->id)->exists()) {
            return response()->error('Category has assets and cannot be deleted', 409);
        }

        $category->delete();

        return response()->noContent('Category deleted');
    }
}
