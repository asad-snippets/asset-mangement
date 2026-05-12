<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome To Admin Dashboard'
        ]);
    }
    public function users()
    {
        $users = User::all();

        return response()->json([
            'users' => $users
        ]);
    }
    public function singleUser($id)
    {
        $user = User::find($id);

        return response()->json([
            'user' => $user
        ]);
    }

    // Delete User
    public function deleteUser($id)
    {
        $user = User::find($id);

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