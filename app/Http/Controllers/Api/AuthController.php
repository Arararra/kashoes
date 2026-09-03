<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->string('name')->trim()->toString(),
                'email' => $request->string('email')->lower()->toString(),
                'phone' => $request->string('phone')->trim()->toString(),
                'password' => Hash::make($request->string('password')->toString()),
            ]);

            $user->assignRole('customer');
            Customer::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => '',
                'is_member' => false,
            ]);

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $clientId = config('services.google.client_id');
        abort_if(blank($clientId), 503, 'Google Sign-In belum dikonfigurasi pada server.');

        $googleResponse = Http::timeout(10)
            ->acceptJson()
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $request->string('id_token')->toString(),
            ]);

        $profile = $googleResponse->successful() ? $googleResponse->json() : null;
        $emailVerified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! is_array($profile)
            || ! hash_equals((string) $clientId, (string) ($profile['aud'] ?? ''))
            || ! $emailVerified
            || blank($profile['sub'] ?? null)
            || blank($profile['email'] ?? null)) {
            throw ValidationException::withMessages([
                'id_token' => 'Token Google tidak valid atau tidak ditujukan untuk aplikasi ini.',
            ]);
        }

        $email = strtolower(trim((string) $profile['email']));
        $googleId = (string) $profile['sub'];
        $name = trim((string) ($profile['name'] ?? $email));

        $user = User::where('email', $email)->first();

        if ($user?->hasAnyRole(['admin', 'super_admin'])) {
            abort(403, 'Akun staf harus masuk menggunakan email dan password.');
        }

        if ($user?->google_id && ! hash_equals($user->google_id, $googleId)) {
            throw ValidationException::withMessages([
                'id_token' => 'Akun Google tidak cocok dengan akun yang sudah terhubung.',
            ]);
        }

        $user = DB::transaction(function () use ($user, $email, $googleId, $name): User {
            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'password' => Hash::make(str()->random(40)),
                ]);
                $user->assignRole('customer');
            } elseif (! $user->google_id) {
                $user->forceFill(['google_id' => $googleId])->save();
            }

            Customer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'phone' => $user->phone ?? '',
                    'address' => $user->address ?? '',
                    'is_member' => false,
                ],
            );

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Google Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles'); // Load roles so frontend knows

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}
