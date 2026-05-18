<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $query = Asset::where('user_id', $companyId)->orderBy('id', 'desc');

        $assets = $query->get();

        return response()->success($assets, 'Assets fetched');
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $asset = Asset::where('user_id', $companyId)->find($id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        return response()->success($asset, 'Asset fetched');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'asset_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assets', 'asset_code')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'description' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'condition' => 'required|in:' . implode(',', Asset::CONDITIONS),
            'location' => 'required|string|max:255',
        ]);

        $category = Category::select('id', 'name')
            ->where('user_id', $companyId)
            ->find($request->category_id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        $asset = Asset::create([
            'user_id' => $companyId,
            'asset_name' => $request->asset_name,
            'category_id' => $category->id,
            'category' => $category->name,
            'asset_code' => $request->asset_code,
            'description' => $request->description,
            'purchase_date' => $request->purchase_date,
            'purchase_cost' => $request->purchase_cost,
            'condition' => $request->condition,
            'location' => $request->location,
        ]);

        return response()->created($asset, 'Asset created');
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $asset = Asset::where('user_id', $companyId)->find($id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $ownerId = $asset->user_id ?? $companyId;

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($ownerId) {
                    return $query->where('user_id', $ownerId);
                }),
            ],
            'asset_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assets', 'asset_code')
                    ->where(function ($query) use ($ownerId) {
                        return $query->where('user_id', $ownerId);
                    })
                    ->ignore($asset->id),
            ],
            'description' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'condition' => 'required|in:' . implode(',', Asset::CONDITIONS),
            'location' => 'required|string|max:255',
        ]);

        $category = Category::select('id', 'name')
            ->where('user_id', $ownerId)
            ->find($request->category_id);

        if (!$category) {
            return response()->error('Category not found', 404);
        }

        $asset->update([
            'asset_name' => $request->asset_name,
            'category_id' => $category->id,
            'category' => $category->name,
            'asset_code' => $request->asset_code,
            'description' => $request->description,
            'purchase_date' => $request->purchase_date,
            'purchase_cost' => $request->purchase_cost,
            'condition' => $request->condition,
            'location' => $request->location,
        ]);

        return response()->success($asset, 'Asset updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->companyId();

        $asset = Asset::where('user_id', $companyId)->find($id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $asset->delete();

        return ApiResponse::noContent('Asset deleted');
    }
}
