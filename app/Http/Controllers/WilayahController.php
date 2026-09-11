<?php

namespace App\Http\Controllers;

use App\Exports\WilayahExport;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = Wilayah::query();

            if ($request->filled('tingkat')) {
                $query->tingkat((int) $request->integer('tingkat'));
            }

            return DataTables::of($query)
                ->addColumn('tingkat', fn (Wilayah $wilayah) => e($wilayah->tingkatLabel()))
                ->addColumn('actions', function (Wilayah $wilayah) {
                    $actions = '';

                    if (auth()->user()->hasPermission('show-wilayah')) {
                        $actions .= '<a href="'.route('wilayah.show', $wilayah).'" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }

                    if (auth()->user()->hasPermission('edit-wilayah')) {
                        $actions .= '<a href="'.route('wilayah.edit', $wilayah).'" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }

                    if (auth()->user()->hasPermission('delete-wilayah')) {
                        $actions .= '<form action="'.route('wilayah.destroy', $wilayah).'" method="POST" class="inline" onsubmit="return confirm(\'Hapus wilayah ini?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }

                    return $actions ?: '-';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('wilayah.index', [
            'tingkatOptions' => Wilayah::tingkatOptions(),
        ]);
    }

    public function export()
    {
        return Excel::download(new WilayahExport, 'wilayah-'.date('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        return view('wilayah.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:13', 'regex:/^[0-9.]+$/', 'unique:wilayah,kode'],
            'nama' => ['required', 'string', 'max:100'],
        ]);

        Wilayah::query()->create($validated);

        return to_route('wilayah.index')->with('status', 'Wilayah created successfully.');
    }

    public function show(Wilayah $wilayah): View
    {
        return view('wilayah.show', [
            'wilayah' => $wilayah,
            'parent' => $wilayah->parent(),
        ]);
    }

    public function edit(Wilayah $wilayah): View
    {
        return view('wilayah.edit', compact('wilayah'));
    }

    public function update(Request $request, Wilayah $wilayah): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
        ]);

        $wilayah->update($validated);

        return to_route('wilayah.index')->with('status', 'Wilayah updated successfully.');
    }

    public function destroy(Wilayah $wilayah): RedirectResponse
    {
        $wilayah->delete();

        return to_route('wilayah.index')->with('status', 'Wilayah deleted successfully.');
    }
}
