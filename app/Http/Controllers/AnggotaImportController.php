<?php

namespace App\Http\Controllers;

use App\Exports\AnggotaImportTemplateExport;
use App\Http\Requests\ImportAnggotaRequest;
use App\Services\ImportAnggotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnggotaImportController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorizeImport($request);

        return view('anggota.import');
    }

    public function store(ImportAnggotaRequest $request, ImportAnggotaService $importer): RedirectResponse
    {
        $hasil = $importer->import(
            $request->file('file'),
            $request->user(),
            $request->boolean('akun_aktif'),
            $request->boolean('terverifikasi'),
        );

        $pesan = $hasil['imported'] === 0
            ? 'Tidak ada baris yang berhasil diimpor.'
            : $hasil['imported'].' anggota berhasil diimpor.';

        if ($hasil['failed'] > 0) {
            $pesan .= ' '.$hasil['failed'].' baris gagal.';
        }

        return redirect()
            ->route('anggota.import')
            ->with('status', $pesan)
            ->with('import_hasil', $hasil);
    }

    public function template(Request $request): BinaryFileResponse
    {
        $this->authorizeImport($request);

        return Excel::download(new AnggotaImportTemplateExport, 'template-import-anggota.xlsx');
    }

    private function authorizeImport(Request $request): void
    {
        $user = $request->user();

        abort_unless($user && ($user->isSuperAdmin() || $user->hasPermission('import-anggota')), 403);
    }
}
