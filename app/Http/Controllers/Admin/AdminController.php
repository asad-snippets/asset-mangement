<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome To Admin Dashboard'
        ]);
    }
    public function users(Request $request)
    {
        $companyId = $request->user()->companyId();

        $users = User::where('company_id', $companyId)
            ->orWhere('id', $companyId)
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }
    public function singleUser(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $user = User::where('id', $id)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('id', $companyId);
            })
            ->first();

        return response()->json([
            'user' => $user
        ]);
    }

    // Delete User
    public function deleteUser(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $user = User::where('id', $id)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('id', $companyId);
            })
            ->first();

        if (!$user) {

            return response()->json([
                'message' => 'User not found'
            ]);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}