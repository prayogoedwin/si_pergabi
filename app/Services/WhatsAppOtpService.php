<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WhatsAppOtpService
{
    public function __construct(private readonly FonnteService $fonnte) {}

    public function send(User $user, string $phone): void
    {
        $otp = (string) random_int(100000, 999999);

        Cache::put($this->key($user), Hash::make($otp), now()->addMinutes(10));
        Cache::put($this->throttleKey($user), true, now()->addMinute());

        if (app()->runningUnitTests()) {
            Cache::put($this->key($user).'.plain', $otp, now()->addMinutes(10));
        }

        $this->fonnte->send(
            $phone,
            "Kode verifikasi PERGABI: {$otp}. Berlaku 10 menit. Jangan bagikan kode ini.",
        );
    }

    public function canResend(User $user): bool
    {
        return ! Cache::has($this->throttleKey($user));
    }

    public function verify(User $user, string $otp): void
    {
        $hashed = Cache::get($this->key($user));

        if (! is_string($hashed) || ! Hash::check($otp, $hashed)) {
            throw ValidationException::withMessages([
                'otp' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        Cache::forget($this->key($user));
        Cache::forget($this->throttleKey($user));
    }

    private function key(User $user): string
    {
        return "pergabi.otp.wa.{$user->id}";
    }

    private function throttleKey(User $user): string
    {
        return "pergabi.otp.wa.throttle.{$user->id}";
    }
}
