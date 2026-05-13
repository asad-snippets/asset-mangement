<?php

namespace App\Http\Controllers;

use App\Helpers\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function createUser(Request $request)
    {
        $actor = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
            'permissions' => 'nullable|array',
        ]);

        $role = $request->role;
        if ($actor->role === Role::MANAGER) {
            $role = Role::EMPLOYEE;
        }

        if (!in_array($role, Role::ROLES, true)) {
            return response()->json([
                'message' => 'Invalid role'
            ], 422);
        }

        $allowedPermissions = $this->allowedPermissionsForRole($role);
        $permissionCheck = $this->validatePermissionsInput($request->permissions, $allowedPermissions);

        if (!$permissionCheck['ok']) {
            return response()->json([
                'message' => $permissionCheck['message'],
                'allowed_permissions' => $allowedPermissions,
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'department' => $request->department,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'permissions' => $permissionCheck['permissions'],
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function updateUserPermissions(Request $request, $id)
    {
        $actor = $request->user();

        $request->validate([
            'permissions' => 'required|array'
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($actor->role === Role::MANAGER && $user->role !== Role::EMPLOYEE) {
            return response()->json([
                'message' => 'Managers can update employees only'
            ], 403);
        }

        $allowedPermissions = $this->allowedPermissionsForRole($user->role);
        $permissionCheck = $this->validatePermissionsInput($request->permissions, $allowedPermissions);

        if (!$permissionCheck['ok']) {
            return response()->json([
                'message' => $permissionCheck['message'],
                'allowed_permissions' => $allowedPermissions,
            ], 422);
        }

        $user->permissions = $permissionCheck['permissions'];
        $user->save();

        return response()->json([
            'message' => 'Permissions updated',
            'user' => $user,
        ]);
    }

    private function allowedPermissionsForRole(string $role): array
    {
        if ($role === Role::EMPLOYEE) {
            return [Role::PERMISSION_SHOW_ASSETS];
        }

        return Role::ALL_PERMISSIONS;
    }

    private function validatePermissionsInput($permissions, array $allowed): array
    {
        if ($permissions === null) {
            return ['ok' => true, 'permissions' => []];
        }

        if (!is_array($permissions)) {
            return ['ok' => false, 'message' => 'Permissions must be an array'];
        }

        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                return ['ok' => false, 'message' => 'Permissions must be strings'];
            }
        }

        $permissions = array_values(array_unique($permissions));
        $invalid = array_diff($permissions, $allowed);

        if (!empty($invalid)) {
            return ['ok' => false, 'message' => 'Invalid permissions provided'];
        }

        return ['ok' => true, 'permissions' => $permissions];
    }
}
