<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'staff';
        $data['status'] = $data['status'] ?? 'active';
        $data['is_active'] = ($data['status'] === 'active');

        $user = $this->userRepository->create($data);

        // Assign default Spatie role if applicable
        try {
            $user->assignRole($data['role']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to assign Spatie role during registration', ['email' => $user->email, 'role' => $data['role']]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials, string $ip)
    {
        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $ip);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60); // lock for 1 minute after 5 attempts
            \Illuminate\Support\Facades\Log::warning('Failed login attempt', ['email' => $credentials['email'], 'ip' => $ip]);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (isset($user->is_active) && !$user->is_active) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact support.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();
    }

    public function sendResetLink(array $data)
    {
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            // Silently return success to prevent email enumeration attacks
            return true;
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Here we normally send email: Mail::to($user->email)->send(new ResetPasswordMail($token));
        // Since we may not have mail configured, we'll just log it or assume it's sent.
        // Returning the token just for demo/verification purposes (normally don't return token to API client directly!)
        return $token; 
    }

    public function resetPassword(array $data)
    {
        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$record || !Hash::check($data['token'], $record->token)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid or expired password reset token.'],
            ]);
        }

        // Check if token is older than 60 minutes
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw ValidationException::withMessages([
                'email' => ['Password reset token has expired.'],
            ]);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return true;
    }
}
