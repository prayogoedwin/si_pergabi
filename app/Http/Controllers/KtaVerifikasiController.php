<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KtaVerifikasiController extends Controller
{
    public function show(string $nomor): View
    {
        $anggota = $this->findByNomor($nomor);
        $anggota->load(['pd', 'pc', 'statusLogs']);

        return view('kta.verifikasi', compact('anggota'));
    }

    public function foto(string $nomor): StreamedResponse
    {
        $anggota = $this->findByNomor($nomor);

        abort_unless(filled($anggota->foto_path) && Storage::disk('local')->exists($anggota->foto_path), 404);

        return Storage::disk('local')->response($anggota->foto_path, 'foto-anggota', [
            'Content-Type' => Storage::disk('local')->mimeType($anggota->foto_path) ?: 'image/jpeg',
        ]);
    }

    private function findByNomor(string $nomor): Anggota
    {
        return Anggota::query()
            ->where('nomor_anggota', $nomor)
            ->firstOrFail();
    }
}
