<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerpanjanganRequest;
use App\Http\Requests\UpdatePortalProfilRequest;
use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\Wilayah;
use App\Services\AnggotaStatusService;
use App\Services\PendaftaranAnggotaService;
use App\Services\SettingService;
use App\Services\WebsitePostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function __construct(private readonly AnggotaStatusService $status) {}

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

        $this->status->expireIfOverdue($anggota);
        $anggota->refresh();
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
        $this->status->expireIfOverdue($anggota);
        $anggota->refresh();
        $anggota->load(['pd', 'pc', 'statusLogs']);

        $hasKartu = filled($anggota->nomor_anggota);
        $unlocked = $anggota->canAccessKartuDigital();

        return view('portal.kta', [
            'anggota' => $anggota,
            'hasKartu' => $hasKartu,
            'unlocked' => $unlocked,
            'verifikasiUrl' => $hasKartu ? $anggota->urlVerifikasiQr() : null,
            'identitas' => app(SettingService::class)->identitas(),
            'fotoUrl' => route('portal.foto'),
        ]);
    }

    public function qr(Request $request): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $anggota = $this->anggota($request);
        $this->status->expireIfOverdue($anggota);
        $anggota->refresh();
        $unlocked = $anggota->canAccessQrCode();

        return view('portal.qr', [
            'anggota' => $anggota,
            'unlocked' => $unlocked,
            'verifikasiUrl' => $unlocked ? $anggota->urlVerifikasiQr() : null,
        ]);
    }

    public function profil(Request $request): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $anggota = $this->anggota($request);
        $anggota->load(['dokumen', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan']);

        $provinsi = old('provinsi_kode', $anggota->provinsi_kode);
        $kabupaten = old('kabupaten_kode', $anggota->kabupaten_kode);
        $kecamatan = old('kecamatan_kode', $anggota->kecamatan_kode);

        return view('portal.profil', [
            'anggota' => $anggota,
            'user' => $request->user(),
            'activeTab' => $this->profilTab($request),
            'jenisKelamin' => Anggota::jenisKelaminOptions(),
            'agama' => Anggota::agamaOptions(),
            'statusPerkawinan' => Anggota::statusPerkawinanOptions(),
            'statusGuru' => Anggota::statusGuruOptions(),
            'jenjang' => Anggota::jenjangOptions(),
            'statusSekolah' => Anggota::statusSekolahOptions(),
            'provinsiOptions' => $this->wilayahAnak(null),
            'kabupatenOptions' => $this->wilayahAnak($provinsi),
            'kecamatanOptions' => $this->wilayahAnak($kabupaten),
            'kelurahanOptions' => $this->wilayahAnak($kecamatan),
        ]);
    }

    public function updateProfil(
        UpdatePortalProfilRequest $request,
        PendaftaranAnggotaService $pendaftaran,
    ): RedirectResponse {
        $pendaftaran->updateProfile($request->user(), $request->validated(), $request->dokumenUploads());

        return redirect()
            ->route('portal.profil', ['tab' => $request->input('tab', 'pribadi')])
            ->with('status', 'Data profil berhasil diperbarui.');
    }

    public function kegiatan(Request $request, WebsitePostService $websitePosts): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $result = $websitePosts->paginate(
            max(1, (int) $request->integer('page', 1)),
            $request->url(),
        );

        return view('portal.kegiatan', [
            'posts' => $result['posts'],
            'failed' => $result['failed'],
            'showRoute' => 'portal.kegiatan.show',
        ]);
    }

    public function kegiatanShow(Request $request, int $post, WebsitePostService $websitePosts): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $kegiatan = $websitePosts->find($post);

        abort_if($kegiatan === null, 404);

        return view('portal.kegiatan-show', [
            'post' => $kegiatan,
            'indexRoute' => 'portal.kegiatan',
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

        return redirect()
            ->route('portal.profil', ['tab' => 'akun'])
            ->with('status', 'Password berhasil diperbarui.');
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

    public function perpanjang(Request $request, SettingService $settings): View|RedirectResponse
    {
        $redirect = $this->redirectPengurus($request);

        if ($redirect) {
            return $redirect;
        }

        $anggota = $this->anggota($request);
        $this->status->expireIfOverdue($anggota);
        $anggota->refresh();

        if (! $anggota->canRenew()) {
            return redirect()->route('portal.show')
                ->withErrors(['status' => 'Keanggotaan ini belum bisa diajukan ulang.']);
        }

        return view('pendaftaran.create', [
            'renewal' => true,
            'storeUrl' => route('portal.perpanjang.store'),
            'prefill' => $this->prefill($anggota),
            'jenisKelamin' => Anggota::jenisKelaminOptions(),
            'agama' => Anggota::agamaOptions(),
            'statusPerkawinan' => Anggota::statusPerkawinanOptions(),
            'statusGuru' => Anggota::statusGuruOptions(),
            'jenjang' => Anggota::jenjangOptions(),
            'statusSekolah' => Anggota::statusSekolahOptions(),
            'dokumenWajib' => AnggotaDokumen::requiredJenis(),
            'dokumenLabels' => AnggotaDokumen::formJenisLabels(),
            'wizardConfig' => $settings->wizardConfig(),
        ]);
    }

    public function storePerpanjang(
        StorePerpanjanganRequest $request,
        PendaftaranAnggotaService $pendaftaran,
    ): JsonResponse {
        $anggota = $pendaftaran->renew($request->user(), $request->validated(), $request->dokumenUploads());

        return response()->json([
            'redirect' => route('portal.show'),
            'nomor' => $anggota->nomor_anggota,
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function prefill(Anggota $anggota): array
    {
        return [
            'nik' => $anggota->nik,
            'gelar_depan' => $anggota->gelar_depan,
            'nama' => $anggota->nama,
            'gelar_belakang' => $anggota->gelar_belakang,
            'jenis_kelamin' => $anggota->jenis_kelamin,
            'tempat_lahir' => $anggota->tempat_lahir,
            'tanggal_lahir' => $anggota->tanggal_lahir?->toDateString(),
            'agama' => $anggota->agama,
            'status_perkawinan' => $anggota->status_perkawinan,
            'hp' => $anggota->hp,
            'whatsapp' => $anggota->whatsapp,
            'email' => $anggota->email,
            'alamat' => $anggota->alamat,
            'provinsi_kode' => $anggota->provinsi_kode,
            'kabupaten_kode' => $anggota->kabupaten_kode,
            'kecamatan_kode' => $anggota->kecamatan_kode,
            'kelurahan_kode' => $anggota->kelurahan_kode,
            'kode_pos' => $anggota->kode_pos,
            'status_guru' => $anggota->status_guru,
            'nip' => $anggota->nip,
            'nuptk' => $anggota->nuptk,
            'jenjang' => $anggota->jenjang,
            'nama_sekolah' => $anggota->nama_sekolah,
            'npsn' => $anggota->npsn,
            'status_sekolah' => $anggota->status_sekolah,
            'alamat_sekolah' => $anggota->alamat_sekolah,
        ];
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

    private function profilTab(Request $request): string
    {
        $tabs = [
            'pribadi' => ['nik', 'nama', 'gelar_depan', 'gelar_belakang', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status_perkawinan', 'pas_foto'],
            'kontak' => ['hp', 'whatsapp', 'email', 'alamat', 'provinsi_kode', 'kabupaten_kode', 'kecamatan_kode', 'kelurahan_kode', 'kode_pos'],
            'profesi' => ['status_guru', 'nip', 'nuptk', 'jenjang', 'nama_sekolah', 'npsn', 'status_sekolah', 'alamat_sekolah'],
            'dokumen' => ['sk_mengajar'],
            'akun' => ['current_password', 'password'],
        ];

        foreach ($tabs as $tab => $fields) {
            if ($request->session()->get('errors')?->hasAny($fields)) {
                return $tab;
            }
        }

        $requested = $request->old('tab', $request->query('tab', 'pribadi'));

        return array_key_exists($requested, $tabs) ? $requested : 'pribadi';
    }

    /**
     * @return list<array{kode: string, nama: string}>
     */
    private function wilayahAnak(?string $parent): array
    {
        if ($parent === null || $parent === '') {
            $parent = null;
        }

        return Wilayah::query()
            ->anak($parent)
            ->orderBy('nama')
            ->get(['kode', 'nama'])
            ->map(fn (Wilayah $wilayah): array => [
                'kode' => $wilayah->kode,
                'nama' => $wilayah->nama,
            ])
            ->values()
            ->all();
    }
}
