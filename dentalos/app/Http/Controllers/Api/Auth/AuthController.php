<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('Invalid credentials', [], 401);
        }

        if (!$user->is_active) {
            return $this->error('Account is inactive', [], 403);
        }

        if ($user->locked_until && $user->locked_until->isFuture()) {
            return $this->error('Account is locked. Try again later.', [], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->increment('failed_login_attempts');
            
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
                return $this->error('Too many failed attempts. Account locked for 15 minutes.', [], 403);
            }

            return $this->error('Invalid credentials', [], 401);
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user,
            'permissions' => [] // TODO: Integrate with PermissionService in 2.3
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['tenant', 'locations']);
        return $this->success([
            'user' => $user,
            'role' => $user->role,
            'permissions' => [] // TODO: Integrate with PermissionService
        ]);
    }

    public function setupMfa(Request $request): JsonResponse
    {
        $user = $request->user();
        $secret = Google2FA::generateSecretKey();
        
        $user->update(['mfa_secret' => $secret]); // Should be encrypted in real app

        $qrCodeUrl = Google2FA::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return $this->success([
            'qr_code' => $qrCodeUrl,
            'secret' => $secret
        ], 'MFA setup initiated');
    }

    public function verifyMfa(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required']);
        $user = $request->user();

        if (Google2FA::verifyKey($user->mfa_secret, $request->code)) {
            $backupCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $backupCodes[] = Str::random(10);
            }

            $user->update([
                'mfa_enabled' => true,
                'mfa_backup_codes' => array_map(fn($code) => Hash::make($code), $backupCodes)
            ]);

            return $this->success(['backup_codes' => $backupCodes], 'MFA enabled successfully');
        }

        return $this->error('Invalid MFA code');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->success(null, __($status))
            : $this->error(__($status));
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->success(null, __($status))
            : $this->error(__($status));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password incorrect');
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return $this->success(null, 'Password updated and other sessions invalidated');
    }
}
