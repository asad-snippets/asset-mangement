<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'industry' => 'required|in:pharmacutical,technology,manufacturing,logistics,energy & utilities',
            'company_size' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'admin_full_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:50',
            'password' => 'required|min:6',
        ]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiresAt = now()->addMinutes(10);

        $payload = [
            'name' => $request->admin_full_name,
            'email' => $request->email_address,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'industry' => $request->industry,
            'company_size' => $request->company_size,
            'location' => $request->location,
            'contact_number' => $request->contact_number,
            'role' => Role::SUPER_ADMIN,
            'permissions' => [],
        ];

        DB::table('pending_registrations')->updateOrInsert(
            ['email' => $request->email_address],
            [
                'otp_code' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'payload' => json_encode($payload),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Mail::raw(
            "Your OTP for registration is: {$otp}. It expires in 10 minutes.",
            function ($message) use ($request) {
                $message->to($request->email_address)
                    ->subject('Your Registration OTP');
            }
        );

        return response()->json([
            'message' => 'User Registered Successfully. Verify OTP to continue.',
            'otp_sent' => true,
            'email' => $request->email_address,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $record = DB::table('pending_registrations')
            ->where('email', $request->email_address)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Invalid OTP request'
            ], 400);
        }

        if ($record->otp_code !== $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        $expiresAt = $record->otp_expires_at ? Carbon::parse($record->otp_expires_at) : null;

        if ($expiresAt !== null && now()->greaterThan($expiresAt)) {
            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        $payload = json_decode($record->payload, true);

        if (!is_array($payload)) {
            return response()->json([
                'message' => 'Invalid registration payload'
            ], 400);
        }

        $payload['otp_verified_at'] = now();

        $user = User::create($payload);

        if ($user->company_id === null) {
            $user->company_id = $user->id;
            $user->save();
        }

        DB::table('pending_registrations')
            ->where('email', $request->email_address)
            ->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
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

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiresAt = now()->addMinutes(10);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => now()
            ]
        );

        Mail::raw(
            "Your OTP for password reset is: {$otp}. It expires in 10 minutes.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Your Password Reset OTP');
            }
        );

        return response()->json([
            'message' => 'OTP sent to your email',
            'otp_sent' => true,
            'otp_expires_at' => $otpExpiresAt->toDateTimeString()
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required_without:token|string|size:6',
            'token' => 'required_without:otp|string',
            'password' => 'required|min:6'
        ]);

        $token = $request->otp ?? $request->token;

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $token)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Invalid token'
            ], 400);
        }

        $expiresAt = $record->created_at ? Carbon::parse($record->created_at)->addMinutes(10) : null;

        if ($expiresAt !== null && now()->greaterThan($expiresAt)) {
            return response()->json([
                'message' => 'OTP expired'
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