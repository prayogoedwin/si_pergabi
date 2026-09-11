<?php

namespace App\Http\Controllers;

use App\Services\DashboardCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CacheController extends Controller
{
    public function __construct(private readonly DashboardCacheService $dashboardCache) {}

    public function index(): View
    {
        return view('cache.index', [
            'entries' => $this->dashboardCache->entries(),
            'prefixes' => DashboardCacheService::prefixGroups(),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $key = $request->string('key')->toString();

        abort_unless($this->dashboardCache->isManaged($key), 422);

        $this->dashboardCache->forgetKey($key);

        return back()->with('status', "Cache {$key} dihapus.");
    }

    public function destroyPrefix(Request $request): RedirectResponse
    {
        $prefix = $request->string('prefix')->toString();

        abort_unless($this->dashboardCache->isManaged($prefix), 422);

        $deleted = $this->dashboardCache->forgetPrefix($prefix);

        return back()->with('status', "Cache prefix {$prefix} dihapus ({$deleted} kunci).");
    }

    public function flush(): RedirectResponse
    {
        $this->dashboardCache->flushAll();

        return back()->with('status', 'Semua cache dibersihkan.');
    }
}
