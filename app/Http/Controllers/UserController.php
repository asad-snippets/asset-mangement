<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\Role;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->companyId();

        $users = User::where(function ($query) use ($companyId) {
            $query->where('company_id', $companyId)
                ->orWhere('id', $companyId);
        })
            ->orderBy('id', 'desc')
            ->get();

        return response()->success($users, 'Users fetched');
    }

    public function show(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $user = User::where('id', $id)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('id', $companyId);
            })
            ->first();

        if (!$user) {
            return response()->error('User not found', 404);
        }

        return response()->success($user, 'User fetched');
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        $companyId = $actor->companyId();

        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'department' => [
                'required',
                'string',
                'max:255',
                Rule::in(Employee::DEPARTMENTS),
            ],
            'role' => 'required|string',
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
            'password' => 'required|string|min:6',
        ]);

        $role = $request->role;
        if ($actor->role === Role::MANAGER) {
            $role = Role::EMPLOYEE;
        }

        if (!in_array($role, Role::ROLES, true)) {
            return response()->error('Invalid role', 422);
        }

        $photoPath = null;
        $uploadedPhoto = $request->file('photo');
        if ($uploadedPhoto) {
            $storedPath = $uploadedPhoto->store('users', 'public');
            $photoPath = '/storage/' . ltrim($storedPath, '/');
        }

        $user = User::create([
            'name' => $request->fullName,
            'email' => $request->email,
            'department' => $request->department,
            'role' => $role,
            'status' => $request->status,
            'password' => Hash::make($request->password),
            'permissions' => [],
            'company_id' => $companyId,
            'user_photo' => $photoPath,
        ]);

        return response()->created($user, 'User created');
    }

    public function update(Request $request, $id)
    {
        $actor = $request->user();
        $companyId = $actor->companyId();

        $user = User::where('id', $id)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('id', $companyId);
            })
            ->first();

        if (!$user) {
            return response()->error('User not found', 404);
        }

        if ($actor->role === Role::MANAGER && $user->role !== Role::EMPLOYEE) {
            return response()->error('Managers can update employees only', 403);
        }

        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fullName' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'department' => [
                'required',
                'string',
                'max:255',
                Rule::in(Employee::DEPARTMENTS),
            ],
            'role' => 'required|string',
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
            'password' => 'nullable|string|min:6',
        ]);

        $role = $request->role;
        if ($actor->role === Role::MANAGER) {
            $role = Role::EMPLOYEE;
        }

        if (!in_array($role, Role::ROLES, true)) {
            return response()->error('Invalid role', 422);
        }

        $photoPath = $user->user_photo;
        $uploadedPhoto = $request->file('photo');
        if ($uploadedPhoto) {
            if ($user->user_photo) {
                $relativePath = $this->extractStoragePath($user->user_photo);
                if ($relativePath) {
                    Storage::disk('public')->delete($relativePath);
                }
            }

            $storedPath = $uploadedPhoto->store('users', 'public');
            $photoPath = '/storage/' . ltrim($storedPath, '/');
        }

        $payload = [
            'name' => $request->fullName,
            'email' => $request->email,
            'department' => $request->department,
            'role' => $role,
            'status' => $request->status,
            'user_photo' => $photoPath,
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->password);
        }

        $user->update($payload);

        return response()->success($user, 'User updated');
    }

    public function destroy(Request $request, $id)
    {
        $actor = $request->user();
        $companyId = $actor->companyId();

        $user = User::where('id', $id)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('id', $companyId);
            })
            ->first();

        if (!$user) {
            return response()->error('User not found', 404);
        }

        if ($actor->role === Role::MANAGER && $user->role !== Role::EMPLOYEE) {
            return response()->error('Managers can delete employees only', 403);
        }

        if ($user->user_photo) {
            $relativePath = $this->extractStoragePath($user->user_photo);
            if ($relativePath) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $user->delete();

        return ApiResponse::noContent('User deleted');
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
}
