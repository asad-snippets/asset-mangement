<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->companyId();

        $maintenances = Maintenance::where('user_id', $companyId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->success($maintenances, 'Maintenance records fetched');
    }

    public function show(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $maintenance = Maintenance::where('user_id', $companyId)->find($id);

        if (!$maintenance) {
            return response()->error('Maintenance record not found', 404);
        }

        return response()->success($maintenance, 'Maintenance record fetched');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->companyId();

        $request->validate([
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'maintenance_type' => 'required|string|max:100',
            'priority' => 'required|string|max:50',
            'scheduled_date' => 'required|date',
            'assigned_to' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $asset = Asset::where('user_id', $companyId)->find($request->asset_id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $maintenance = Maintenance::create([
            'user_id' => $companyId,
            'asset_id' => $asset->id,
            'maintenance_type' => $request->maintenance_type,
            'priority' => $request->priority,
            'scheduled_date' => $request->scheduled_date,
            'assigned_to' => $request->assigned_to,
            'description' => $request->description,
            'estimated_cost' => $request->estimated_cost,
        ]);

        return response()->created($maintenance, 'Maintenance record created');
    }

    public function update(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $maintenance = Maintenance::where('user_id', $companyId)->find($id);

        if (!$maintenance) {
            return response()->error('Maintenance record not found', 404);
        }

        $request->validate([
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'maintenance_type' => 'required|string|max:100',
            'priority' => 'required|string|max:50',
            'scheduled_date' => 'required|date',
            'assigned_to' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $asset = Asset::where('user_id', $companyId)->find($request->asset_id);

        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $maintenance->update([
            'asset_id' => $asset->id,
            'maintenance_type' => $request->maintenance_type,
            'priority' => $request->priority,
            'scheduled_date' => $request->scheduled_date,
            'assigned_to' => $request->assigned_to,
            'description' => $request->description,
            'estimated_cost' => $request->estimated_cost,
        ]);

        return response()->success($maintenance, 'Maintenance record updated');
    }

    public function destroy(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $maintenance = Maintenance::where('user_id', $companyId)->find($id);

        if (!$maintenance) {
            return response()->error('Maintenance record not found', 404);
        }

        $maintenance->delete();

        return ApiResponse::noContent('Maintenance record deleted');
    }
}
