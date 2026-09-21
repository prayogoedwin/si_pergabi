<?php

namespace App\Services;

use App\Support\WebsitePost;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebsitePostService
{
    /**
     * @return array{posts: LengthAwarePaginator<int, WebsitePost>, failed: bool}
     */
    public function paginate(int $page, string $path): array
    {
        $page = max(1, $page);
        $perPage = max(1, (int) config('pergabi.wp.per_page', 12));
        $payload = $this->remember('pergabi.wp.kegiatan.v2.page.'.$perPage.'.'.$page, fn () => $this->fetchPage($page, $perPage));

        $items = collect(is_array($payload['items'] ?? null) ? $payload['items'] : [])
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => WebsitePost::fromCache($row))
            ->filter(fn (WebsitePost $post) => $post->id > 0)
            ->values();

        $paginator = new LengthAwarePaginator(
            $items,
            (int) ($payload['total'] ?? $items->count()),
            $perPage,
            $page,
            [
                'path' => $path,
                'pageName' => 'page',
            ],
        );

        return [
            'posts' => $paginator->withQueryString(),
            'failed' => (bool) ($payload['failed'] ?? true),
        ];
    }

    public function find(int $id): ?WebsitePost
    {
        $payload = $this->remember('pergabi.wp.kegiatan.v2.post.'.$id, fn () => $this->fetchPost($id));

        if (! is_array($payload) || ($payload['failed'] ?? false) || ! isset($payload['id'])) {
            return null;
        }

        $post = WebsitePost::fromCache($payload);

        return $post->id > 0 ? $post : null;
    }

    /**
     * @template T of array
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function remember(string $key, callable $callback): array
    {
        $ttl = (int) config('pergabi.wp.cache_ttl', 600);
        $payload = $ttl > 0
            ? Cache::remember($key, $ttl, $callback)
            : $callback();

        return is_array($payload) ? $payload : ['items' => [], 'total' => 0, 'failed' => true];
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int, failed: bool}
     */
    private function fetchPage(int $page, int $perPage): array
    {
        $empty = [
            'items' => [],
            'total' => 0,
            'failed' => true,
        ];

        try {
            $response = $this->client()->get($this->postsUrl(), [
                'categories' => $this->categoryId(),
                'per_page' => $perPage,
                'page' => $page,
                '_embed' => 1,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Gagal mengambil kegiatan dari website PERGABI.', [
                'message' => $exception->getMessage(),
            ]);

            return $empty;
        }

        if ($response->failed()) {
            Log::warning('Website PERGABI menolak permintaan kegiatan.', [
                'status' => $response->status(),
            ]);

            return $empty;
        }

        $items = collect($response->json())
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => WebsitePost::fromWp($row))
            ->filter(fn (WebsitePost $post) => $post->id > 0)
            ->map(fn (WebsitePost $post) => $post->toCache())
            ->values()
            ->all();

        return [
            'items' => $items,
            'total' => (int) $response->header('X-WP-Total', (string) count($items)),
            'failed' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPost(int $id): array
    {
        try {
            $response = $this->client()->get($this->postsUrl().'/'.$id, [
                '_embed' => 1,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Gagal mengambil detail kegiatan dari website PERGABI.', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return ['failed' => true];
        }

        if ($response->failed() || ! is_array($response->json())) {
            return ['failed' => true];
        }

        $payload = $response->json();

        if (! WebsitePost::belongsToCategory($payload, $this->categoryId())) {
            return ['failed' => true];
        }

        $post = WebsitePost::fromWp($payload);

        return $post->id > 0 ? $post->toCache() : ['failed' => true];
    }

    private function client(): PendingRequest
    {
        return Http::timeout(15)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'SI-PERGABI/1.0 (+https://pergabi.id)',
            ]);
    }

    private function postsUrl(): string
    {
        return rtrim((string) config('pergabi.wp.base_url'), '/').'/posts';
    }

    private function categoryId(): int
    {
        return (int) config('pergabi.wp.kegiatan_category', 11);
    }
}
