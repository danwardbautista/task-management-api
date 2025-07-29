<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function register(RegisterRequest $request)
    {
        try {
            // Create new user with hashed password
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // Generate sanctum token for immediate login
            $token = $user->createToken('auth_token')->plainTextToken;

            $this->auditLogger->logSuccess('auth.register', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return ApiResponse::success('User registered successfully', [
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            $this->auditLogger->logError('auth.register', $e, [
                'email' => $request->input('email')
            ]);

            return ApiResponse::error('Registration failed. Please try again later.');
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');
            
            // Cache for failed login attempts this is temporary solution, decide later if want to use database or remove this altogether
            $attemptKey = 'login_attempts_' . hash('sha256', $request->ip() . $email);
            
            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Increment failed attempts in cache
                $attempts = Cache::get($attemptKey, 0) + 1;
                Cache::put($attemptKey, $attempts, now()->addMinutes(15));
                
                $this->auditLogger->logError('auth.login', new \Exception('User not found'), [
                    'email' => $email,
                    'attempts' => $attempts
                ]);
                return ApiResponse::error('Invalid credentials.', null, 401);
            }

            // Check if account is locked from previous failed attempts
            if ($user->isLocked()) {
                $this->auditLogger->logError('auth.login', new \Exception('Account locked'), [
                    'user_id' => $user->id,
                    'locked_until' => $user->locked_until
                ]);
                return ApiResponse::error('Account is temporarily locked. Please try again later.', null, 423);
            }

            // Verify password
            if (!Hash::check($password, $user->password)) {
                // Increment failed attempts in cache
                $attempts = Cache::get($attemptKey, 0) + 1;
                Cache::put($attemptKey, $attempts, now()->addMinutes(15));
                
                // Lock account after 5 failed attempts
                if ($attempts >= 5) {
                    $user->lockAccount(15); // Lock for 15 minutes
                    Cache::forget($attemptKey); // Clear attempts after locking
                    
                    $this->auditLogger->logError('auth.login', new \Exception('Account locked due to failed attempts'), [
                        'user_id' => $user->id,
                        'attempts' => $attempts
                    ]);
                    return ApiResponse::error('Account locked due to too many failed attempts. Try again in 15 minutes.', null, 423);
                }
                
                $this->auditLogger->logError('auth.login', new \Exception('Invalid password'), [
                    'user_id' => $user->id,
                    'attempts' => $attempts
                ]);
                return ApiResponse::error('Invalid credentials.', null, 401);
            }

            // Successful login we need to clear failed attempts and unlock account
            Cache::forget($attemptKey);
            $user->unlockAccount();
            
            // Generate new access token
            $token = $user->createToken('auth_token')->plainTextToken;

            $this->auditLogger->logSuccess('auth.login', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return ApiResponse::success('Login successful', [
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            $this->auditLogger->logError('auth.login', $e);
            return ApiResponse::error('Login failed. Please try again later.');
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revoke current access token, decide later if want to delete all tokens
            $user = $request->user();
            $user->currentAccessToken()->delete();

            $this->auditLogger->logSuccess('auth.logout', [
                'user_id' => $user->id
            ]);

            return ApiResponse::success('Logged out successfully');

        } catch (\Exception $e) {
            $this->auditLogger->logError('auth.logout', $e);
            return ApiResponse::error('Logout failed. Please try again later.');
        }
    }

    public function user(Request $request)
    {
        try {
            // Get authenticated user info
            $user = $request->user();

            $this->auditLogger->logSuccess('auth.user', [
                'user_id' => $user->id
            ]);

            return ApiResponse::success('User retrieved successfully', $user);

        } catch (\Exception $e) {
            $this->auditLogger->logError('auth.user', $e);
            return ApiResponse::error('Failed to retrieve user. Please try again later.');
        }
    }
}