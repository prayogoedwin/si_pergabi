<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KtaVerifikasiController extends Controller
{
    public function show(Request $request): View
    {
        $kode = $this->kode($request);
        $anggota = $this->findByNomor($kode);

        if ($anggota) {
            $anggota->load(['pd', 'pc', 'statusLogs']);
        }

        return view('kta.verifikasi', [
            'kode' => $kode,
            'anggota' => $anggota,
            'valid' => $anggota?->isAktif() ?? false,
        ]);
    }

    public function foto(Request $request): StreamedResponse
    {
        $anggota = $this->findByNomor($this->kode($request));

        abort_unless($anggota !== null, 404);
        abort_unless(filled($anggota->foto_path) && Storage::disk('local')->exists($anggota->foto_path), 404);

        return Storage::disk('local')->response($anggota->foto_path, 'foto-anggota', [
            'Content-Type' => Storage::disk('local')->mimeType($anggota->foto_path) ?: 'image/jpeg',
        ]);
    }

    private function kode(Request $request): string
    {
        return trim((string) $request->query('kode', ''));
    }

    private function findByNomor(string $nomor): ?Anggota
    {
        if ($nomor === '') {
            return null;
        }

        return Anggota::query()
            ->where('nomor_anggota', $nomor)
            ->first();
    }
}
