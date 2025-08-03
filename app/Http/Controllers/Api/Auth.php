<?php

namespace App\Http\Controllers\Api;

use App\Enum\User\UserStatus;
use App\Models\User;
use App\Enum\User\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use function App\Helpers\jsonResponse;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\Api\User\UserResource;
use App\Http\Requests\User\VerifyEmailRequest;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Http\Requests\User\ReSendVerificationCode;
use App\Services\FormatPhoneNumber as ServicesFormatPhoneNumber;

class Auth extends Controller
{

    public function __construct(protected ServicesFormatPhoneNumber $phoneFormatter) {}

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $formattedNumber = $this->phoneFormatter->formatPhoneNumber($data['phone']);

        if (isset($data['avatar'])) {
            $path = $data['avatar']->storeAs('profile/images', 'profile_' . time() . '.' . $data['avatar']->getClientOriginalExtension(), 's3');
        }

        $user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $formattedNumber,
            'avatar' => $path ?? null,
            'password' => Hash::make($data['password']),
            'verify_otp' => rand(100000, 999999),
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        $resource = UserResource::make($user);

        return jsonResponse(message: __('user created successfully . Please check your email for verification.'), status: 'success', code: 201, data: $resource);
    }

    public function reSendVerificationCode(ReSendVerificationCode  $request)
    {

        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return jsonResponse(message: __('user not found'), status: 'error', code: 404);
        }

        $user->generateAndSendVerificationCode();

        return jsonResponse(message: __('verification code sent successfully . Please check your email.'), status: 'success', code: 200);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return jsonResponse(message: __('user not found'), status: 'error', code: 404);
        }

        if ($user->verify_otp !== $data['verification_code']) {
            return jsonResponse(message: __('invalid verification code'), status: 'error', code: 422);
        }

        if (now()->greaterThan($user->email_otp_expires_at)) {
            return jsonResponse(message: __('OTP expired. Please request a new one.'), status: 'error', code: 410);
        }

        $user->markEmailAsVerified();
        $user->verify_otp = null;
        $user->email_otp_expires_at = null;
        $user->status = UserStatus::ACTIVE->value;


        $user->save();

        return jsonResponse(message: __('email verified successfully'), status: 'success', code: 200, data: UserResource::make($user));
    }

    public function sendResetPasswordCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->sendPasswordResetCode();

        return jsonResponse(message: __('Password reset code sent successfully. Please check your email.'), status: 'success', code: 200);
    }
}
