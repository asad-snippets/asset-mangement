<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'industry' => 'required|in:pharmacutical,technology,manufacturing,logistics,energy & utilities',
            'company_size' => 'required|string|max:255',
            'company_size_employees' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'admin_full_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:50',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->admin_full_name,
            'email' => $request->email_address,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'industry' => $request->industry,
            'company_size' => $request->company_size,
            'company_size_employees' => $request->company_size_employees,
            'location' => $request->location,
            'contact_number' => $request->contact_number,
            'role' => Role::ADMIN,
            'permissions' => [],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User Registered Successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Successful'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        return response()->json([
            'message' => 'Reset token generated',
            'token' => $token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Invalid token'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }
}