<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftaranRequest;
use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\Wilayah;
use App\Services\PendaftaranAnggotaService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PendaftaranController extends Controller
{
    public function create(SettingService $settings): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->homeRoute());
        }

        return view('pendaftaran.create', [
            'jenisKelamin' => Anggota::jenisKelaminOptions(),
            'agama' => Anggota::agamaOptions(),
            'statusPerkawinan' => Anggota::statusPerkawinanOptions(),
            'statusGuru' => Anggota::statusGuruOptions(),
            'jenjang' => Anggota::jenjangOptions(),
            'statusSekolah' => Anggota::statusSekolahOptions(),
            'dokumenWajib' => AnggotaDokumen::requiredJenis(),
            'dokumenLabels' => AnggotaDokumen::jenisLabels(),
            'wizardConfig' => $settings->wizardConfig(),
        ]);
    }

    public function wilayah(Request $request): JsonResponse
    {
        $parent = $request->string('parent')->toString() ?: null;

        $wilayah = Wilayah::query()
            ->anak($parent)
            ->orderBy('nama')
            ->get(['kode', 'nama']);

        return response()->json($wilayah);
    }

    public function store(StorePendaftaranRequest $request, PendaftaranAnggotaService $pendaftaran): JsonResponse
    {
        $user = $pendaftaran->register($request->validated(), $request->dokumenUploads());

        Auth::login($user);

        try {
            $redirect = $pendaftaran->afterRegister($user);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            return response()->json([
                'redirect' => route('verification.notice'),
                'warning' => $exception->getMessage(),
            ], 201);
        }

        return response()->json([
            'redirect' => $redirect,
        ], 201);
    }
}
