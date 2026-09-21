<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final readonly class WebsitePost
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public string $excerpt,
        public string $content,
        public ?string $imageUrl,
        public ?string $link,
        public ?CarbonInterface $publishedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromWp(array $payload): self
    {
        return new self(
            id: (int) ($payload['id'] ?? 0),
            slug: (string) ($payload['slug'] ?? ''),
            title: self::plainText($payload['title']['rendered'] ?? $payload['title'] ?? ''),
            excerpt: self::plainText($payload['excerpt']['rendered'] ?? $payload['excerpt'] ?? ''),
            content: self::sanitizeHtml((string) ($payload['content']['rendered'] ?? $payload['content'] ?? '')),
            imageUrl: self::featuredImage($payload),
            link: filled($payload['link'] ?? null) ? (string) $payload['link'] : null,
            publishedAt: filled($payload['date'] ?? null) ? Carbon::parse((string) $payload['date']) : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromCache(array $payload): self
    {
        return new self(
            id: (int) ($payload['id'] ?? 0),
            slug: (string) ($payload['slug'] ?? ''),
            title: (string) ($payload['title'] ?? ''),
            excerpt: (string) ($payload['excerpt'] ?? ''),
            content: (string) ($payload['content'] ?? ''),
            imageUrl: filled($payload['image_url'] ?? null) ? (string) $payload['image_url'] : null,
            link: filled($payload['link'] ?? null) ? (string) $payload['link'] : null,
            publishedAt: filled($payload['date'] ?? null) ? Carbon::parse((string) $payload['date']) : null,
        );
    }

    /**
     * @return array{id: int, slug: string, title: string, excerpt: string, content: string, image_url: ?string, link: ?string, date: ?string}
     */
    public function toCache(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'image_url' => $this->imageUrl,
            'link' => $this->link,
            'date' => $this->publishedAt?->toIso8601String(),
        ];
    }

    public function dateLabel(): string
    {
        return $this->publishedAt?->timezone(config('app.timezone'))->format('d M Y') ?? '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function belongsToCategory(array $payload, int $categoryId): bool
    {
        $categories = $payload['categories'] ?? [];

        if (! is_array($categories)) {
            return false;
        }

        return in_array($categoryId, array_map('intval', $categories), true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function featuredImage(array $payload): ?string
    {
        $media = $payload['_embedded']['wp:featuredmedia'][0] ?? null;

        if (! is_array($media)) {
            return null;
        }

        $sizes = $media['media_details']['sizes'] ?? [];

        foreach (['et-pb-post-main-image', 'medium_large', 'large', 'medium'] as $size) {
            $url = $sizes[$size]['source_url'] ?? null;

            if (filled($url)) {
                return (string) $url;
            }
        }

        $source = $media['source_url'] ?? null;

        return filled($source) ? (string) $source : null;
    }

    private static function plainText(mixed $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private static function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return $html;
    }
}
