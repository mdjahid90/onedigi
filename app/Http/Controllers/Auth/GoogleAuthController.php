<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

use GuzzleHttp\Exception\ClientException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $redirectUrl = (string) config('services.google.redirect');

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $redirectUrl = (string) config('services.google.redirect');

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->user();
        } catch (InvalidStateException $e) {
            try {
                $googleUser = Socialite::driver('google')
                    ->redirectUrl($redirectUrl)
                    ->stateless()
                    ->user();
            } catch (ClientException $e2) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Google login failed (invalid_grant). Check GOOGLE_REDIRECT_URI matches the Google Console redirect URI exactly, then try again.',
                ]);
            }
        } catch (ClientException $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google login failed (invalid_grant). Check GOOGLE_REDIRECT_URI matches the Google Console redirect URI exactly, then try again.',
            ]);
        }

        $googleId = (string) $googleUser->getId();
        $email = (string) ($googleUser->getEmail() ?? '');

        if ($googleId === '' || $email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Google login failed. Please try again.',
            ]);
        }

        $user = User::query()
            ->where('google_id', $googleId)
            ->first();

        if ($user && empty($user->email_verified_at)) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        if (!$user) {
            $user = User::query()->where('email', $email)->first();

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            } else {
                $user = User::create([
                    'name' => (string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User'),
                    'email' => $email,
                    'google_id' => $googleId,
                    'has_local_password' => false,
                    'password' => Str::random(40),
                ]);

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
