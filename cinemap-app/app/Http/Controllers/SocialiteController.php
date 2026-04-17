<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    //
    public function redirectToDiscord()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function handleDiscordCallback()
    {
        $discordUser = Socialite::driver('discord')->user();

        $user = User::firstOrCreate(
            ['oauth_id' => $discordUser->getId()],
            [
                'name' => $discordUser->getName(),
                'email' => $discordUser->getEmail(),
                'password' => bcrypt(str()->random(32)),
            ]
        );

        Auth::login($user);

        return redirect('/home');
    }
}
