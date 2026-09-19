<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrasiSettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
}
