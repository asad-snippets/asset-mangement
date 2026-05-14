<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::orderBy('id', 'desc')->get();

        return response()->success($assets, 'Assets fetched');
    }

    public function show($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        return response()->success($asset, 'Asset fetched');
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', Asset::CATEGORIES),
            'asset_code' => 'required|string|max:100|unique:assets,asset_code',
            'description' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'condition' => 'required|in:' . implode(',', Asset::CONDITIONS),
            'location' => 'required|string|max:255',
        ]);

        $asset = Asset::create([
            'asset_name' => $request->asset_name,
            'category' => $request->category,
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
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', Asset::CATEGORIES),
            'asset_code' => 'required|string|max:100|unique:assets,asset_code,' . $asset->id,
            'description' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'condition' => 'required|in:' . implode(',', Asset::CONDITIONS),
            'location' => 'required|string|max:255',
        ]);

        $asset->update([
            'asset_name' => $request->asset_name,
            'category' => $request->category,
            'asset_code' => $request->asset_code,
            'description' => $request->description,
            'purchase_date' => $request->purchase_date,
            'purchase_cost' => $request->purchase_cost,
            'condition' => $request->condition,
            'location' => $request->location,
        ]);

        return response()->success($asset, 'Asset updated');
    }

    public function destroy($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $asset->delete();

        return response()->noContent('Asset deleted');
    }
}
