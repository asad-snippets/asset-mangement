<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->companyId();

        $employees = Employee::where('user_id', $companyId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->success($employees, 'Employees fetched');
    }

    public function show(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $employee = Employee::where('user_id', $companyId)->find($id);

        if (!$employee) {
            return response()->error('Employee not found', 404);
        }

        return response()->success($employee, 'Employee fetched');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->companyId();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email_address' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email_address')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'department_name' => [
                'required',
                'string',
                'max:255',
                Rule::in(Employee::DEPARTMENTS),
            ],
            'job_title' => 'required|string|max:255',
            'employee_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $photoPath = null;
        $uploadedPhoto = $request->file('employee_photo') ?? $request->file('photo');
        if ($uploadedPhoto) {
            $storedPath = $uploadedPhoto->store('employees', 'public');
            $photoPath = '/storage/' . ltrim($storedPath, '/');
        }

        $employee = Employee::create([
            'user_id' => $companyId,
            'full_name' => $request->full_name,
            'email_address' => $request->email_address,
            'department_name' => $request->department_name,
            'job_title' => $request->job_title,
            'employee_photo' => $photoPath,
        ]);

        return response()->created($employee, 'Employee created');
    }

    public function update(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $employee = Employee::where('user_id', $companyId)->find($id);

        if (!$employee) {
            return response()->error('Employee not found', 404);
        }

        $ownerId = $employee->user_id ?? $companyId;

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email_address' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email_address')
                    ->where(function ($query) use ($ownerId) {
                        return $query->where('user_id', $ownerId);
                    })
                    ->ignore($employee->id),
            ],
            'department_name' => [
                'required',
                'string',
                'max:255',
                Rule::in(Employee::DEPARTMENTS),
            ],
            'job_title' => 'required|string|max:255',
            'employee_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $photoPath = $this->normalizePhotoUrl($employee->employee_photo);
        $uploadedPhoto = $request->file('employee_photo') ?? $request->file('photo');
        if ($uploadedPhoto) {
            if ($employee->employee_photo) {
                $relativePath = $this->extractStoragePath($employee->employee_photo);
                if ($relativePath) {
                    Storage::disk('public')->delete($relativePath);
                }
            }

            $storedPath = $uploadedPhoto->store('employees', 'public');
            $photoPath = '/storage/' . ltrim($storedPath, '/');
        }

        $employee->update([
            'full_name' => $request->full_name,
            'email_address' => $request->email_address,
            'department_name' => $request->department_name,
            'job_title' => $request->job_title,
            'employee_photo' => $photoPath,
        ]);

        return response()->success($employee, 'Employee updated');
    }

    public function destroy(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $employee = Employee::where('user_id', $companyId)->find($id);

        if (!$employee) {
            return response()->error('Employee not found', 404);
        }

        if ($employee->employee_photo) {
            $relativePath = $this->extractStoragePath($employee->employee_photo);
            if ($relativePath) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $employee->delete();

        return ApiResponse::noContent('Employee deleted');
    }

    private function extractStoragePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = $path;
        $parsed = parse_url($path);
        if ($parsed && isset($parsed['path'])) {
            $normalized = $parsed['path'];
        }

        if (str_starts_with($normalized, '/storage/')) {
            return ltrim(substr($normalized, strlen('/storage/')), '/');
        }

        if (str_starts_with($normalized, 'storage/')) {
            return ltrim(substr($normalized, strlen('storage/')), '/');
        }

        return ltrim($normalized, '/');
    }

    private function normalizePhotoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        return Storage::url(ltrim($path, '/'));
    }
}
