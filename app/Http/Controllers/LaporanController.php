<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Helpers\Area;
use App\Models\Wilayah;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function __construct(private readonly LaporanService $laporan) {}

    public function index(Request $request): View
    {
        abort_unless($this->canView($request), 403);

        $filters = $this->filters($request);

        return view('laporan.index', [
            ...$filters,
            'sections' => $this->laporan->sections(
                $request->user(),
                $filters['filterProvinsi'],
                $filters['filterKabupaten'],
                $filters['filterTahun'],
            ),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        abort_unless($this->canDownload($request), 403);

        $filters = $this->filters($request);
        $sections = $this->laporan->sections(
            $request->user(),
            $filters['filterProvinsi'],
            $filters['filterKabupaten'],
            $filters['filterTahun'],
        );

        return Excel::download(
            new LaporanExport($sections),
            'laporan-anggota-'.$filters['filterTahun'].'-'.now()->format('Y-m-d').'.xlsx',
        );
    }

    /**
     * @return array{
     *     wilayahLabel: string,
     *     showProvinsiFilter: bool,
     *     showKabupatenFilter: bool,
     *     filterProvinsi: ?string,
     *     filterKabupaten: ?string,
     *     filterTahun: int,
     *     tahunOptions: list<int>,
     *     filterQuery: array<string, string>,
     *     provinsiOptions: Collection<int, Wilayah>,
     *     kabupatenOptions: Collection<int, Wilayah>
     * }
     */
    private function filters(Request $request): array
    {
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

        $tahun = (int) $request->integer('tahun', (int) now()->year);

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) now()->year;
        }

        $showProvinsiFilter = $user->isNasional();
        $showKabupatenFilter = $user->isNasional() || $level === Area::DAERAH;
        $kabupatenParent = $user->isNasional() ? $provinsi : $user->pd_kode;

        $filterQuery = array_filter([
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'tahun' => (string) $tahun,
        ]);

        return [
            'wilayahLabel' => $user->wilayahTugasLabel(),
            'showProvinsiFilter' => $showProvinsiFilter,
            'showKabupatenFilter' => $showKabupatenFilter,
            'filterProvinsi' => $provinsi,
            'filterKabupaten' => $kabupaten,
            'filterTahun' => $tahun,
            'tahunOptions' => range((int) now()->year, (int) now()->year - 5),
            'filterQuery' => $filterQuery,
            'provinsiOptions' => $showProvinsiFilter
                ? Wilayah::query()->anak(null)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
            'kabupatenOptions' => ($showKabupatenFilter && filled($kabupatenParent))
                ? Wilayah::query()->anak($kabupatenParent)->orderBy('nama')->get(['kode', 'nama'])
                : collect(),
        ];
    }

    private function canView(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && ($user->isSuperAdmin() || $user->hasPermission('view-laporan'));
    }

    private function canDownload(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && ($user->isSuperAdmin() || $user->hasPermission('download-laporan'));
    }

    private function nullableCode(mixed $value): ?string
    {
        $code = is_string($value) ? trim($value) : '';

        return $code === '' ? null : $code;
    }
}
