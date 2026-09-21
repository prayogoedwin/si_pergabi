@php
    /** @var \App\Support\WebsitePost $post */
    $indexRoute = $indexRoute ?? 'kegiatan.index';
@endphp

<a href="{{ route($indexRoute) }}" class="text-sm text-navy-800/70 hover:text-navy-900 dark:text-gold-400">← Kembali ke kegiatan</a>

<article class="mt-4 overflow-hidden rounded-2xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25">
    <div class="relative aspect-video w-full overflow-hidden bg-navy-900">
        @if ($post->imageUrl)
            <img src="{{ $post->imageUrl }}" alt="{{ $post->title }}"
                class="absolute inset-0 h-full w-full object-cover object-center">
        @else
            <div class="absolute inset-0 grid place-items-center bg-navy-900">
                <img src="{{ asset('images/logo-pergabi.png') }}" alt="Logo PERGABI"
                    class="h-24 w-24 object-contain">
            </div>
        @endif
    </div>
    <div class="p-5 sm:p-6">
        @if ($post->dateLabel() !== '')
            <p class="text-[11px] uppercase tracking-wider text-saffron-600 dark:text-gold-400">{{ $post->dateLabel() }}</p>
        @endif
        <h1 class="mt-1 font-display text-3xl font-bold leading-tight text-navy-900 dark:text-cream-100">{{ $post->title }}</h1>
        <div class="kegiatan-prose mt-5 text-sm leading-relaxed text-navy-900 dark:text-cream-100">
            {!! $post->content !!}
        </div>
        @if ($post->link)
            <p class="mt-6 text-sm">
                <a href="{{ $post->link }}" target="_blank" rel="noopener noreferrer" class="text-navy-800 hover:underline dark:text-gold-400">Buka di website PERGABI</a>
            </p>
        @endif
    </div>
</article>

<style>
    .kegiatan-prose p { margin: 0 0 1rem; }
    .kegiatan-prose h2, .kegiatan-prose h3 { font-family: "Cormorant Garamond", serif; font-weight: 700; margin: 1.25rem 0 0.5rem; }
    .kegiatan-prose ul, .kegiatan-prose ol { margin: 0 0 1rem 1.25rem; }
    .kegiatan-prose a { color: #c94b10; text-decoration: underline; }
    .kegiatan-prose img, .kegiatan-prose figure, .kegiatan-prose iframe, .kegiatan-prose video {
        display: block;
        width: 100%;
        max-width: 100%;
        height: auto;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        object-position: center;
        border-radius: 0.75rem;
        background: #071422;
        margin: 1rem 0;
    }
    .kegiatan-prose figure { overflow: hidden; }
    .kegiatan-prose figure img { margin: 0; }
</style>
