<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enum\User\UserRole;
use Illuminate\Http\Request;
use App\Enum\User\UserStatus;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\Api\User\UserResource;
use App\Http\Requests\User\VerifyEmailRequest;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\ReSendVerificationCode;
use App\Services\FormatPhoneNumber as ServicesFormatPhoneNumber;

class Auth extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected ServicesFormatPhoneNumber $phoneFormatter) {}

    public function register(RegisterRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                $formattedNumber = $this->phoneFormatter->formatPhoneNumber($data['phone']);

                $path = null;
                if (isset($data['avatar'])) {
                    $path = $data['avatar']->storeAs('profile/images', 'profile_' . time() . '.' . $data['avatar']->getClientOriginalExtension(), 's3');
                }

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $formattedNumber,
                    'avatar' => $path,
                    'password' => Hash::make($data['password']),
                    'status' => UserStatus::PENDING_VERIFICATION,
                ]);

                $resource = UserResource::make($user);

                return $this->successResponse(
                    data: $resource,
                    message: __('User created successfully. Please check your email for verification.'),
                    code: 201
                );
            });
        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage());
            return $this->errorResponse(__('Registration failed. Please try again.'));
        }
    }

    public function reSendVerificationCode(ReSendVerificationCode $request)
    {
        try {
            $data = $request->validated();

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                return $this->notFoundResponse(__('User not found'));
            }

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse(__('Email is already verified'), 400);
            }

            $user->generateAndSendVerificationCode();

            return $this->successResponse(
                message: __('Verification code sent successfully. Please check your email.')
            );
        } catch (\Exception $e) {
            Log::error('Resend verification failed: ' . $e->getMessage());
            return $this->errorResponse(__('Failed to send verification code'));
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                $user = User::where('email', $data['email'])->first();

                if (!$user) {
                    return $this->notFoundResponse(__('User not found'));
                }

                if ($user->hasVerifiedEmail()) {
                    return $this->errorResponse(__('Email is already verified'), 400);
                }

                if ($user->verify_otp !== $data['verification_code']) {
                    return $this->errorResponse(__('Invalid verification code'), 422);
                }

                if (now()->greaterThan($user->email_otp_expires_at)) {
                    return $this->errorResponse(__('OTP expired. Please request a new one.'), 410);
                }

                $user->markEmailAsVerified();
                $user->verify_otp = null;
                $user->email_otp_expires_at = null;
                $user->status = UserStatus::ACTIVE;
                $user->save();

                return $this->successResponse(
                    data: UserResource::make($user),
                    message: __('Email verified successfully')
                );
            });
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return $this->errorResponse(__('Email verification failed'));
        }
    }

    public function sendResetPasswordCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->notFoundResponse(__('User not found'));
            }

            
            if (
                $user->password_reset_expires_at &&
                now()->lessThan($user->password_reset_expires_at->subMinutes(8))
            ) {
                return $this->errorResponse(
                    __('Please wait before requesting another reset code.'),
                    429
                );
            }

            $user->sendPasswordResetCode();

            return $this->successResponse(
                message: __('Password reset code sent successfully. Please check your email.')
            );
        } catch (\Exception $e) {
            Log::error('Password reset code failed: ' . $e->getMessage());
            return $this->errorResponse(__('Failed to send password reset code'));
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                $user = User::where('email', $data['email'])->first();

                if (!$user) {
                    return $this->notFoundResponse(__('User not found'));
                }


                if ($user->password_reset_code !== $data['reset_code']) {
                    return $this->errorResponse(__('Invalid reset code'), 422);
                }


                if (
                    !$user->password_reset_expires_at ||
                    now()->greaterThan($user->password_reset_expires_at)
                ) {
                    return $this->errorResponse(__('Reset code expired. Please request a new one.'), 410);
                }


                $user->password = Hash::make($data['password']);
                $user->password_reset_code = null;
                $user->password_reset_expires_at = null;
                $user->save();

                $user->tokens()->delete();

                Log::info('Password reset successful', ['user_id' => $user->id]);

                return $this->successResponse(
                    message: __('Password reset successfully. Please login with your new password.')
                );
            });
        } catch (\Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage());
            return $this->errorResponse(__('Password reset failed. Please try again.'));
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();

            $loginField = filter_var($data['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $loginValue = $data['identifier'];
            if ($loginField === 'phone') {
                $loginValue = $this->phoneFormatter->formatPhoneNumber($data['identifier']);
            }

            $user = User::where($loginField, $loginValue)->first();

            if (!$user) {
                return $this->notFoundResponse(__('User not found'));
            }
            if (!Hash::check($data['password'], $user->password)) {
                return $this->errorResponse(__('Invalid credentials'), 401);
            }


            if ($user->status !== UserStatus::ACTIVE) {
                return $this->errorResponse(__('User account is not active'), 403);
            }

            if (!$user->hasVerifiedEmail()) {
                return $this->errorResponse(message: __('Please verify your email address before logging in.'), code: 403, data: [
                    'requires_verification' => true,
                    'email' => $user->email,
                ]);
            }

            $tokenName = 'auth_token_' . now()->timestamp;
            $token = $user->createToken($tokenName)->plainTextToken;

            $user->tokens()->delete();

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'tokenName' => $tokenName,
                'loginField' => $loginField,
                'request_ip' => request()->ip()
            ]);

            return $this->successResponse(
                data: [
                    'user' => UserResource::make($user),
                    'token' => $token,
                ],
                message: __('Login successful'),
                code: 200
            );
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage(), [
                'identifier' => $data['identifier'],
                'request_ip' => request()->ip()
            ]);
            return $this->errorResponse(__('Login failed. Please try again.'));
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if(!$user){
                return $this->errorResponse(__('User not authenticated'), 401);
            }

            $user->currentAccessToken()->delete();

            Log::info('User logged out successfully', ['user_id' => $user->id]);

            return $this->successResponse(message: __('Logged out successfully'));

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return $this->errorResponse(__('Logout failed. Please try again.'));
        }
    }
}
