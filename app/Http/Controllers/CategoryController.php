<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $query = Category::where('user_id', $companyId)->orderBy('name');

        $categories = $query->get();

        return response()->success($categories, 'Categories fetched');
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $category = Category::where('user_id', $companyId)->find($id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        return response()->success($category, 'Category fetched');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'description' => 'nullable|string|max:255',
            'icon' => 'required|string|max:100',
        ]);

        $category = Category::create([
            'user_id' => $companyId,
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return response()->created($category, 'Category created');
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $category = Category::where('user_id', $companyId)->find($id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->where(function ($query) use ($category) {
                        return $query->where('user_id', $category->user_id);
                    })
                    ->ignore($category->id),
            ],
            'description' => 'nullable|string|max:255',
            'icon' => 'required|string|max:100',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        Asset::where('category_id', $category->id)
            ->where('user_id', $category->user_id)
            ->update([
            'category' => $category->name,
        ]);

        return response()->success($category, 'Category updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $category = Category::where('user_id', $companyId)->find($id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        if (Asset::where('category_id', $category->id)
            ->where('user_id', $category->user_id)
            ->exists()) {
            return response()->error('Category has assets and cannot be deleted', 409);
        }

        $category->delete();

        return ApiResponse::noContent('Category deleted');
    }
}
