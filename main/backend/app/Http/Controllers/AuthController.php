<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_blocked' => false,
            'is_admin' => false,
        ]);

        return response()->json([
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Nepareizs e-pasts vai parole.'],
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['Šis konts ir bloķēts. Piekļuve liegta.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function adminPanelLogin(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($data['password'] !== config('admin.panel_password')) {
            throw ValidationException::withMessages([
                'password' => ['Nepareiza parole.'],
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'panel-admin@laimigas-kepas.local'],
            [
                'name' => 'Panel administrators',
                'password' => Hash::make(bin2hex(random_bytes(24))),
                'is_admin' => true,
                'is_blocked' => false,
            ]
        );

        if (! $user->is_admin) {
            $user->forceFill(['is_admin' => true])->save();
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('admin-panel')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->forceFill(['last_logout_at' => now()])->save();
            $user->currentAccessToken()?->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function user(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_blocked' => (bool) $user->is_blocked,
            'is_admin' => (bool) $user->is_admin,
            'is_owner' => (bool) $user->is_owner,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'last_logout_at' => $user->last_logout_at?->toIso8601String(),
        ];
    }
}
