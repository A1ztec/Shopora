<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\User\SocialAuthRequest;
use App\Services\FindOrCreateSocialAuth;

class SocialAuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(public FindOrCreateSocialAuth $socialAuthService)
    {

    }
    public function auth(SocialAuthRequest $request)
    {
        $data = $request->validated();

        $socialUser = Socialite::driver($data['provider'])->stateless()->userFromToken($data['access_token']);

        if (!$socialUser) {
            return $this->errorResponse(__('Failed to authenticate with social provider'), 401);
        }

        $user = $this->socialAuthService->findOrCreate($socialUser, $data['provider']);

        if (!$user) {
            return $this->errorResponse(__('Failed to create or find user'), 500);
        }

        $token = $user->createToken('SocialAuthToken')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ] , message : __('Successfully authenticated with social provider'), code : 200);

    }
}
