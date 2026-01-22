<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\UserInfo\SocialAccount;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends APIController
{
    protected array $providers = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        $socialUser = Socialite::driver($provider)
            ->stateless()
            ->user();

        $account = SocialAccount::where([
            'provider'    => $provider,
            'provider_id' => $socialUser->getId(),
        ])->first();

        if ($account) {
            $user = $account->user;
        } else {
            // ищем пользователя по email
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name'              => $socialUser->getName() ?? 'User',
                    'email'             => $socialUser->getEmail(),
                    'password'          => bcrypt(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }

            $user->socialAccounts()->create([
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        $token = $user->createToken('social')->plainTextToken;

        return redirect(
            config('app.frontend_url') .
            '/auth/success?token=' . $token
        );
    }
}