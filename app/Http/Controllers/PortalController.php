<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $anggota = $request->user()?->anggota;

        if ($anggota === null) {
            return view('portal.empty');
        }

        $anggota->load(['dokumen', 'statusLogs.user', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'pd', 'pc']);

        return view('portal.show', compact('anggota'));
    }

    public function kta(Request $request): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $anggota = $this->anggota($request);
        $anggota->load(['pd', 'pc', 'statusLogs']);

        $unlocked = $anggota->canAccessKartuDigital();

        return view('portal.kta', [
            'anggota' => $anggota,
            'unlocked' => $unlocked,
            'verifikasiUrl' => $unlocked ? route('kta.verifikasi', $anggota->nomor_anggota) : null,
            'identitas' => app(SettingService::class)->identitas(),
            'fotoUrl' => route('portal.foto'),
        ]);
    }

    public function profil(Request $request): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $anggota = $this->anggota($request);

        return view('portal.profil', [
            'anggota' => $anggota,
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $this->anggota($request);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Rules\Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password berhasil diperbarui.');
    }

    public function updateFoto(Request $request): RedirectResponse
    {
        $anggota = $this->anggota($request);

        $validated = $request->validate([
            'foto' => ['required', 'image', 'max:2048'],
        ]);

        $file = $validated['foto'];
        $path = $file->store("anggota/{$anggota->id}", 'local');

        $anggota->update(['foto_path' => $path]);

        $dokumen = $anggota->dokumen()->where('jenis', AnggotaDokumen::PAS_FOTO)->first();

        $payload = [
            'jenis' => AnggotaDokumen::PAS_FOTO,
            'path' => $path,
            'nama_asli' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'ukuran' => $file->getSize() ?: 0,
        ];

        if ($dokumen) {
            $dokumen->update($payload);
        } else {
            $anggota->dokumen()->create($payload);
        }

        return back()->with('status', 'Foto profil berhasil diperbarui.');
    }

    public function foto(Request $request): StreamedResponse
    {
        $anggota = $this->anggota($request);

        abort_unless(filled($anggota->foto_path) && Storage::disk('local')->exists($anggota->foto_path), 404);

        return Storage::disk('local')->response($anggota->foto_path, 'foto-anggota', [
            'Content-Type' => Storage::disk('local')->mimeType($anggota->foto_path) ?: 'image/jpeg',
        ]);
    }

    private function redirectPengurus(Request $request): ?RedirectResponse
    {
        if ($request->user()?->isPengurus()) {
            return redirect()->route('dashboard');
        }

        abort_unless($request->user()?->usesMemberPortal(), 403);

        return null;
    }

    private function anggota(Request $request): Anggota
    {
        $anggota = $request->user()?->anggota;

        abort_if($anggota === null, 404);

        return $anggota;
    }
}
