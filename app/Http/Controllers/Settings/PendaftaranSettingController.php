<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePendaftaranSettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PendaftaranSettingController extends Controller
{
    public function edit(SettingService $settings): View
    {
        return view('settings.pendaftaran', [
            'values' => $settings->formValues(),
            'tipeOptions' => SettingService::tipeOptions(),
        ]);
    }

    public function update(UpdatePendaftaranSettingRequest $request, SettingService $settings): RedirectResponse
    {
        $settings->putMany([
            'pendaftaran.tipe' => $request->string('pendaftaran_tipe')->toString(),
            'verifikasi.email_aktif' => $request->boolean('email_aktif') ? '1' : '0',
            'verifikasi.whatsapp_aktif' => $request->boolean('whatsapp_aktif') ? '1' : '0',
            'whatsapp.fonnte_token' => $request->input('fonnte_token'),
            'mail.mailer' => $request->string('mail_mailer')->toString(),
            'mail.host' => $request->input('mail_host'),
            'mail.port' => $request->input('mail_port'),
            'mail.username' => $request->input('mail_username'),
            'mail.password' => $request->input('mail_password'),
            'mail.scheme' => $request->input('mail_scheme'),
            'mail.from_address' => $request->input('mail_from_address'),
            'mail.from_name' => $request->input('mail_from_name'),
        ]);

        $settings->applyMailConfig();

        return back()->with('status', 'Pengaturan pendaftaran disimpan.');
    }
}
