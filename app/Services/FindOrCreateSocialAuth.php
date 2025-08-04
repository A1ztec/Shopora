<?php


namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;


class FindOrCreateSocialAuth
{

    private const SUPPORTED_PROVIDERS = ['facebook', 'google'];

    public function findOrCreate($socialUser, $provider)
    {
        $this->validateProvider($provider);
        $this->validateSocialUser($socialUser);

        $providerField = $provider . '_id';
        $providerId = $socialUser->getId();
        $name = $socialUser->getName();
        $email = $socialUser->getEmail();
        $avatar = $socialUser->getAvatar();

        $existingUser = User::where($providerField, $providerId)->first();

        if ($existingUser) {
           return $this->updateExistingUser(user: $existingUser, name: $name, avatar: $avatar);

        }

        if($email){
            $user = User::where('email', $email)->first();
            if ($user){
               return $this->linkProviderIdToUser($user , $providerField , $providerId, $avatar);
            }
        }


        return $this->createUser($name , $email, $providerField, $providerId, $avatar);
    }


    private function validateProvider(string $provider) : void
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            throw new InvalidArgumentException("Unsupported provider: {$provider}");
        }
    }

    public function validateSocialUser($socialUser): void
    {
        $requiredMethods = ['getId', 'getName', 'getEmail', 'getAvatar'];

        foreach ($requiredMethods as $method) {
            if (!method_exists($socialUser, $method)) {
                throw new InvalidArgumentException("social user object missing method : {$method}");
            }
        }
    }

    private function updateExistingUser(User $user,  ?string $name, ?string $avatar) : User
    {
        $updatedData = [];

        if ($name && $name !== $user->name) {
            $updatedData['name'] = $name;
        }


        if ($avatar && $avatar !== $user->avatar) {
            $updatedData['avatar'] = $avatar;
        }

        if (!empty($updatedData)) {
            $user->update($updatedData);
        }

        return $user;
    }

    private function linkProviderIdToUser(User $user , string $providerField , string $providerId , ?string $avatar = null) : User
    {
        $updatedData = [$providerField => $providerId];

        if($avatar && $avatar !== $user->avatar) {
            $updatedData['avatar'] = $avatar;
        }

        $user->update($updatedData);

        return $user;
    }

    private function createUser(String $name , ?string $email, string $providerField, string $providerId, ?string $avatar = null) : User
    {
       return User::create([
            'name' => $name ?? 'unknown',
            'email' => $email,
            $providerField => $providerId,
            'avatar' => $avatar,
            'email_verified_at' => $email ? now() : null,
            'password' => bcrypt(Str::random(16)),
        ]);

    }
}
