<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestIntegrasiSmtpRequest;
use App\Http\Requests\UpdateIntegrasiSettingRequest;
use App\Mail\SmtpTestMail;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class IntegrasiSettingController extends Controller
{
    public function edit(SettingService $settings): View
    {
        return view('settings.integrasi', [
            'values' => $settings->integrasiFormValues(),
        ]);
    }

    public function update(UpdateIntegrasiSettingRequest $request, SettingService $settings): RedirectResponse
    {
        $settings->putMany([
            'mail.mailer' => $request->string('mail_mailer')->toString(),
            'mail.host' => $request->input('mail_host'),
            'mail.port' => $request->input('mail_port'),
            'mail.username' => $request->input('mail_username'),
            'mail.password' => $request->input('mail_password'),
            'mail.scheme' => $request->input('mail_scheme'),
            'mail.from_address' => $request->input('mail_from_address'),
            'mail.from_name' => $request->input('mail_from_name'),
            'google.client_id' => $request->input('google_client_id'),
            'google.client_secret' => $request->input('google_client_secret'),
            'google.redirect_uri' => $request->input('google_redirect_uri'),
            'whatsapp.fonnte_token' => $request->input('fonnte_token'),
        ]);

        $settings->applyMailConfig();
        $settings->applyGoogleConfig();

        return back()->with('status', 'Pengaturan integrasi disimpan.');
    }

    public function testSmtp(TestIntegrasiSmtpRequest $request, SettingService $settings): RedirectResponse
    {
        $settings->applyMailConfig();

        $to = $request->string('email')->toString();
        $mailer = (string) config('mail.default');

        try {
            Mail::to($to)->send(new SmtpTestMail($mailer));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('smtp_error', 'Gagal mengirim email uji: '.$this->safeMailError($e));
        }

        $note = match ($mailer) {
            'log' => ' Mailer sedang "Log (uji lokal)", jadi pesan hanya dicatat di log server, bukan lewat SMTP. Ganti ke SMTP, simpan, lalu uji lagi.',
            'array' => ' Mailer array (mode uji) — pengiriman dianggap berhasil tanpa SMTP nyata.',
            default => ' Cek kotak masuk dan folder spam.',
        };

        return back()->with('status', "Email uji terkirim ke {$to}.".$note);
    }

    private function safeMailError(Throwable $e): string
    {
        $message = $e->getMessage();
        $message = preg_replace('/passwor(d|t)\s*[=:].+/i', 'password=***', $message) ?? $message;

        return Str::limit($message, 180);
    }
}
