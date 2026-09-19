<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleLoginController extends Controller
{
    public function redirect(SettingService $settings): RedirectResponse
    {
        abort_unless($settings->googleLoginEnabled(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, SettingService $settings): RedirectResponse
    {
        abort_unless($settings->googleLoginEnabled(), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Login Google dibatalkan atau gagal. Silakan coba lagi, atau masuk dengan email dan kata sandi.']);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Akun Google tidak menyertakan email. Gunakan pendaftaran manual, lalu masuk dengan email dan kata sandi.']);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($user === null) {
            return redirect()
                ->route('daftar')
                ->with('status', 'Email Google tersebut belum terdaftar. Silakan daftar terlebih dahulu. Setelah akun aktif, Anda bisa masuk dengan Google.')
                ->with('google_email', $email);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($user->homeRoute());
    }
}
