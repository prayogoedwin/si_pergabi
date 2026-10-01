<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkInformasiRequest;
use App\Http\Requests\UpdateLinkInformasiRequest;
use App\Models\LinkInformasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LinkInformasiController extends Controller
{
    public function index(): View
    {
        $links = LinkInformasi::query()
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('link-informasi.index', [
            'links' => $links,
        ]);
    }

    public function create(): View
    {
        return view('link-informasi.create');
    }

    public function store(StoreLinkInformasiRequest $request): RedirectResponse
    {
        LinkInformasi::query()->create($request->validated());

        return to_route('link-informasi.index')->with('status', 'Link informasi berhasil ditambah.');
    }

    public function show(LinkInformasi $linkInformasi): View
    {
        return view('link-informasi.show', [
            'link' => $linkInformasi,
        ]);
    }

    public function edit(LinkInformasi $linkInformasi): View
    {
        return view('link-informasi.edit', [
            'link' => $linkInformasi,
        ]);
    }

    public function update(UpdateLinkInformasiRequest $request, LinkInformasi $linkInformasi): RedirectResponse
    {
        $linkInformasi->update($request->validated());

        return to_route('link-informasi.index')->with('status', 'Link informasi berhasil diubah.');
    }

    public function destroy(LinkInformasi $linkInformasi): RedirectResponse
    {
        $linkInformasi->delete();

        return to_route('link-informasi.index')->with('status', 'Link informasi berhasil dihapus.');
    }
}
