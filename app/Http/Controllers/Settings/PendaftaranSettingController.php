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
        ]);

        return back()->with('status', 'Pengaturan pendaftaran disimpan.');
    }
}
