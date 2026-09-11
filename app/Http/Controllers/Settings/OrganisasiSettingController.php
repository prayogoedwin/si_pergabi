<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrganisasiSettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrganisasiSettingController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const FILES = [
        'logo' => 'organisasi.logo',
        'stempel' => 'organisasi.stempel',
        'ttd_ketua_umum' => 'organisasi.ttd_ketua_umum',
        'ttd_sekretaris_jenderal' => 'organisasi.ttd_sekretaris_jenderal',
    ];

    public function edit(SettingService $settings): View
    {
        return view('settings.organisasi', [
            'values' => $settings->organisasiFormValues(),
        ]);
    }

    public function update(UpdateOrganisasiSettingRequest $request, SettingService $settings): RedirectResponse
    {
        $settings->putMany([
            'organisasi.nama_lengkap' => $request->string('nama_lengkap')->toString(),
            'organisasi.singkatan' => $request->string('singkatan')->toString(),
            'organisasi.alamat' => $request->string('alamat')->toString(),
            'organisasi.nama_ketua_umum' => $request->string('nama_ketua_umum')->toString(),
            'organisasi.nama_sekretaris_jenderal' => $request->string('nama_sekretaris_jenderal')->toString(),
            'organisasi.visi' => $request->string('visi')->toString(),
            'organisasi.misi' => $request->string('misi')->toString(),
        ]);

        foreach (self::FILES as $field => $key) {
            if ($request->boolean($field.'_hapus')) {
                $settings->forgetPublicFile($key);
            }

            $file = $request->file($field);

            if ($file) {
                $settings->storePublicFile($key, $file);
            }
        }

        return back()->with('status', 'Identitas organisasi disimpan. Siap dipakai di kartu cetak.');
    }
}
