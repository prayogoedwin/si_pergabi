<?php

namespace App\Http\Controllers;

use App\Services\WebsitePostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function __construct(private readonly WebsitePostService $websitePosts) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()?->usesMemberPortal()) {
            return redirect()->route('portal.kegiatan', $request->query());
        }

        $result = $this->websitePosts->paginate(
            max(1, (int) $request->integer('page', 1)),
            $request->url(),
        );

        return view('kegiatan.index', [
            'posts' => $result['posts'],
            'failed' => $result['failed'],
            'showRoute' => 'kegiatan.show',
        ]);
    }

    public function show(Request $request, int $post): View|RedirectResponse
    {
        if ($request->user()?->usesMemberPortal()) {
            return redirect()->route('portal.kegiatan.show', $post);
        }

        $kegiatan = $this->websitePosts->find($post);

        abort_if($kegiatan === null, 404);

        return view('kegiatan.show', [
            'post' => $kegiatan,
            'indexRoute' => 'kegiatan.index',
        ]);
    }
}
