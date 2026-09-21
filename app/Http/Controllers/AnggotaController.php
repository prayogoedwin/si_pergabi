<?php

namespace App\Http\Controllers;

use App\Helpers\Area;
use App\Http\Requests\UpdateAnggotaStatusRequest;
use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\Wilayah;
use App\Services\AnggotaStatusService;
use App\Services\SettingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('view-anggota') || $request->user()?->isSuperAdmin(), 403);

        $user = $request->user();
        $level = $user->organisasiLevel();
        $provinsi = $user->isNasional()
            ? $this->nullableCode($request->input('provinsi'))
            : ($level === Area::DAERAH ? $user->pd_kode : null);
        $kabupaten = $level === Area::CABANG
            ? null
            : $this->nullableCode($request->input('kabupaten'));

        if ($kabupaten && $provinsi && ! str_starts_with($kabupaten, $provinsi.'.')) {
            $kabupaten = null;
        }

        $anggota = Anggota::query()
            ->visibleTo($user, $provinsi, $kabupaten)
            ->with(['user', 'pd', 'pc'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $showProvinsiFilter = $user->isNasional();
        $showKabupatenFilter = $user->isNasional() || $level === Area::DAERAH;
        $kabupatenParent = $user->isNasional() ? $provinsi : $user->pd_kode;

        return view('anggota.index', [
            'anggota' => $anggota,
            'statusOptions' => Anggota::statusLabels(),
            'wilayahLabel' => $user->wilayahTugasLabel(),
            'showProvinsiFilter' => $showProvinsiFilter,
            'showKabupatenFilter' => $showKabupatenFilter,
            'filterProvinsi' => $provinsi,
            'filterKabupaten' => $kabupaten,
            'provinsiOptions' => $showProvinsiFilter
                ? Wilayah::query()->anak(null)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
            'kabupatenOptions' => ($showKabupatenFilter && filled($kabupatenParent))
                ? Wilayah::query()->anak($kabupatenParent)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
        ]);
    }

    public function show(Request $request, Anggota $anggota): View
    {
        abort_unless($request->user()?->hasPermission('show-anggota') || $request->user()?->isSuperAdmin(), 403);
        abort_unless($request->user()->canAccessAnggota($anggota), 403);

        $anggota->load(['user', 'dokumen', 'statusLogs.user', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'pd', 'pc']);

        $unlocked = $anggota->canAccessKartuDigital();

        return view('anggota.show', [
            'anggota' => $anggota,
            'canVerifyPc' => $this->canAct($request, 'verify-anggota-pc') && $anggota->status === Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
            'canValidatePd' => $this->canAct($request, 'validate-anggota-pd') && $anggota->status === Anggota::STATUS_MENUNGGU_VALIDASI_PD,
            'canApprovePp' => $this->canAct($request, 'approve-anggota-pp') && $anggota->status === Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
            'unlocked' => $unlocked,
            'verifikasiUrl' => $unlocked ? $anggota->urlVerifikasiQr() : null,
            'identitas' => app(SettingService::class)->identitas(),
            'fotoUrl' => $unlocked ? $anggota->urlFotoVerifikasiQr() : null,
        ]);
    }

    public function qr(Request $request, Anggota $anggota): View
    {
        abort_unless($request->user()?->hasPermission('show-anggota') || $request->user()?->isSuperAdmin(), 403);
        abort_unless($request->user()->canAccessAnggota($anggota), 403);

        $unlocked = $anggota->canAccessQrCode();

        return view('anggota.qr', [
            'anggota' => $anggota,
            'unlocked' => $unlocked,
            'verifikasiUrl' => $unlocked ? $anggota->urlVerifikasiQr() : null,
        ]);
    }

    public function resetPassword(Request $request, Anggota $anggota): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('show-anggota') || $request->user()?->isSuperAdmin(), 403);
        abort_unless($request->user()->canAccessAnggota($anggota), 403);

        $anggota->loadMissing('user');
        abort_unless($anggota->user !== null, 404);

        $plain = $anggota->user->resetPasswordToTemporary();

        return back()->with([
            'status' => 'Password akun anggota berhasil direset. Salin password baru ini dan berikan kepada anggota, lalu minta mereka mengganti sendiri.',
            'password_reset' => $plain,
        ]);
    }

    public function verifyPc(UpdateAnggotaStatusRequest $request, Anggota $anggota, AnggotaStatusService $status): RedirectResponse
    {
        $this->assertCanProcess($request, $anggota);

        return $this->handleTransition(fn () => $status->verifyByPc($anggota, $request->user(), $request->string('alasan')->toString()), 'Anggota diteruskan ke validasi PD.');
    }

    public function validatePd(UpdateAnggotaStatusRequest $request, Anggota $anggota, AnggotaStatusService $status): RedirectResponse
    {
        $this->assertCanProcess($request, $anggota);

        return $this->handleTransition(fn () => $status->validateByPd($anggota, $request->user(), $request->string('alasan')->toString()), 'Anggota diteruskan ke persetujuan PP.');
    }

    public function approvePp(UpdateAnggotaStatusRequest $request, Anggota $anggota, AnggotaStatusService $status): RedirectResponse
    {
        $this->assertCanProcess($request, $anggota);

        return $this->handleTransition(fn () => $status->approveByPp($anggota, $request->user(), $request->string('alasan')->toString()), 'Anggota disetujui. Nomor anggota telah terbit.');
    }

    public function reject(UpdateAnggotaStatusRequest $request, Anggota $anggota, AnggotaStatusService $status): RedirectResponse
    {
        $this->assertCanProcess($request, $anggota);

        return $this->handleTransition(fn () => $status->reject($anggota, $request->user(), $request->string('alasan')->toString()), 'Pendaftaran ditolak.');
    }

    public function dokumen(Request $request, Anggota $anggota, AnggotaDokumen $dokumen): StreamedResponse
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->id === $anggota->user_id || (($user->hasPermission('show-anggota') || $user->isSuperAdmin()) && $user->canAccessAnggota($anggota))),
            403,
        );
        abort_unless($dokumen->anggota_id === $anggota->id, 404);
        abort_unless(Storage::disk('local')->exists($dokumen->path), 404);

        $filename = $dokumen->nama_asli ?: basename($dokumen->path);
        $headers = [];

        if (filled($dokumen->mime)) {
            $headers['Content-Type'] = $dokumen->mime;
        }

        return Storage::disk('local')->response(
            $dokumen->path,
            $filename,
            $headers,
            $request->boolean('download') ? 'attachment' : 'inline',
        );
    }

    private function handleTransition(callable $callback, string $message): RedirectResponse
    {
        try {
            $callback();
        } catch (AuthorizationException $exception) {
            abort(403, $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['alasan' => $exception->getMessage()]);
        }

        return back()->with('status', $message);
    }

    private function canAct(Request $request, string $permission): bool
    {
        $user = $request->user();

        return $user !== null && ($user->isSuperAdmin() || $user->hasPermission($permission));
    }

    private function assertCanProcess(Request $request, Anggota $anggota): void
    {
        abort_unless($request->user()?->canAccessAnggota($anggota), 403);
    }

    private function nullableCode(mixed $value): ?string
    {
        $code = is_string($value) ? trim($value) : '';

        return $code === '' ? null : $code;
    }
}
