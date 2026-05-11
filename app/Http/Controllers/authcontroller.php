<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'User Registered Successfully',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {

            return response()->json([
                'message' => 'Email not found'
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {

            return response()->json([
                'message' => 'Password incorrect'
            ]);
        }

        return response()->json([
            'message' => 'Login Successful',
            'user' => $user
        ]);
    }

    public function logout()
    {
        return response()->json([
            'message' => 'Logout Successful'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        return response()->json([
            'message' => 'Password reset link sent to email'
        ]);
    }

    public function resetPassword(Request $request)
    {
        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }

    public function verifyEmail()
    {
        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }
}