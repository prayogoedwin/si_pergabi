<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AnggotaStatusService;
use App\Services\SettingService;
use App\Services\WhatsAppOtpService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class VerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($request->user()->homeRoute());
        }

        if ($request->user()->anggota?->kanal_verifikasi === SettingService::KANAL_WHATSAPP) {
            return view('auth.verify-whatsapp', [
                'whatsapp' => $request->user()->anggota?->whatsapp,
            ]);
        }

        return view('auth.verify-email');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($request->user()->homeRoute());
        }

        if ($request->user()->anggota?->kanal_verifikasi === SettingService::KANAL_WHATSAPP) {
            return redirect()->route('verification.notice');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    public function verifyWhatsApp(Request $request, WhatsAppOtpService $otp, AnggotaStatusService $status): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($user->homeRoute());
        }

        $otp->verify($user, $request->string('otp')->toString());

        $user->forceFill(['email_verified_at' => now()])->save();

        if ($user->anggota) {
            $status->verifyWhatsApp($user->anggota, $user);
        }

        return redirect()->intended($user->homeRoute(query: ['verified' => 1]));
    }

    public function resendWhatsApp(Request $request, WhatsAppOtpService $otp): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($user->homeRoute());
        }

        if ($user->anggota?->kanal_verifikasi !== SettingService::KANAL_WHATSAPP) {
            return redirect()->route('verification.notice');
        }

        if (! $otp->canResend($user)) {
            return back()->withErrors(['otp' => 'Tunggu sebentar sebelum mengirim ulang kode.']);
        }

        try {
            $otp->send($user, (string) $user->anggota?->whatsapp);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['otp' => $exception->getMessage()]);
        }

        return back()->with('status', 'otp-sent');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($request->user()->homeRoute(query: ['verified' => 1]));
        }

        if ($request->user()->markEmailAsVerified()) {
            /** @var MustVerifyEmail $user */
            $user = $request->user();

            event(new Verified($user));
        }

        return redirect()->intended($request->user()->homeRoute(query: ['verified' => 1]));
    }
}
