<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\DashboardCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardCacheService $dashboardCache) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $user?->loadMissing(['roles', 'pd', 'pc']);

        if ($user?->usesMemberPortal()) {
            return redirect()->route('portal.show');
        }

        $canViewAnggota = $user !== null && ($user->isSuperAdmin() || $user->hasPermission('view-anggota'));
        $nasional = $user?->isNasional() ?? false;

        $provinsi = $nasional ? $this->nullableCode($request->input('provinsi')) : null;
        $kabupaten = $nasional ? $this->nullableCode($request->input('kabupaten')) : null;

        if ($kabupaten && $provinsi && ! str_starts_with($kabupaten, $provinsi.'.')) {
            $kabupaten = null;
        }

        /** @var Collection<string, int> $statusCounts */
        $statusCounts = collect();
        $totalAnggota = 0;
        $userCount = 0;

        if ($canViewAnggota && $user) {
            $cacheKey = $this->dashboardCache->keyFor($user, $provinsi, $kabupaten);
            $stats = $this->dashboardCache->remember($cacheKey, function () use ($user, $provinsi, $kabupaten, $nasional) {
                $counts = Anggota::query()
                    ->visibleTo($user, $provinsi, $kabupaten)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->map(fn ($total) => (int) $total)
                    ->all();

                return [
                    'statusCounts' => $counts,
                    'totalAnggota' => (int) array_sum($counts),
                    'userCount' => $nasional ? User::query()->count() : 0,
                ];
            });

            $statusCounts = collect($stats['statusCounts'] ?? []);
            $totalAnggota = (int) ($stats['totalAnggota'] ?? 0);
            $userCount = (int) ($stats['userCount'] ?? 0);
        } else {
            $userCount = User::query()->count();
        }

        $filterQuery = array_filter([
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
        ]);

        return view('dashboard', [
            'userCount' => $userCount,
            'canViewAnggota' => $canViewAnggota,
            'nasional' => $nasional,
            'wilayahLabel' => $user?->wilayahTugasLabel() ?? 'Nasional',
            'totalAnggota' => $totalAnggota,
            'statusCounts' => $statusCounts,
            'menungguTotal' => (int) $statusCounts->only([
                Anggota::STATUS_BELUM_VERIFIKASI_EMAIL,
                Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
                Anggota::STATUS_MENUNGGU_VALIDASI_PD,
                Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
            ])->sum(),
            'filterProvinsi' => $provinsi,
            'filterKabupaten' => $kabupaten,
            'filterQuery' => $filterQuery,
            'provinsiOptions' => $nasional
                ? Wilayah::query()->anak(null)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
            'kabupatenOptions' => ($nasional && $provinsi)
                ? Wilayah::query()->anak($provinsi)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
        ]);
    }

    private function nullableCode(mixed $value): ?string
    {
        $code = is_string($value) ? trim($value) : '';

        return $code === '' ? null : $code;
    }
}
