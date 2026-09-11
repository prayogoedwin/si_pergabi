<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class KeanggotaanController extends Controller
{
    public function show(Request $request): View
    {
        $anggota = $request->user()?->anggota()->first();

        abort_if($anggota === null, 404);

        $anggota->load(['dokumen', 'statusLogs.user', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'pd', 'pc']);

        return view('keanggotaan.show', compact('anggota'));
    }
}
