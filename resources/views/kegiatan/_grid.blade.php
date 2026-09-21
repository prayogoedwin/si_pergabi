@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Support\WebsitePost> $posts */
    $showRoute = $showRoute ?? 'kegiatan.show';
@endphp

@if ($failed ?? false)
    <div class="rounded-2xl border border-saffron-600/30 bg-saffron-50 dark:bg-navy-800 px-4 py-3 text-sm text-navy-900 dark:text-cream-100">
        Konten kegiatan sedang tidak dapat dimuat. Coba beberapa saat lagi.
    </div>
@elseif ($posts->isEmpty())
    <div class="rounded-2xl border border-[#e4ddd3] dark:border-gold-400/25 bg-white dark:bg-navy-900 px-4 py-10 text-center text-sm text-navy-800/60 dark:text-cream-100/60">
        Belum ada kegiatan yang ditampilkan.
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach ($posts as $kegiatan)
            <article class="flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25">
                <a href="{{ route($showRoute, $kegiatan->id) }}" class="block">
                    <div class="relative aspect-video w-full overflow-hidden bg-navy-900">
                        @if ($kegiatan->imageUrl)
                            <img src="{{ $kegiatan->imageUrl }}" alt="{{ $kegiatan->title }}"
                                class="absolute inset-0 h-full w-full object-cover object-center"
                                loading="lazy">
                        @else
                            <div class="absolute inset-0 grid place-items-center bg-navy-900">
                                <img src="{{ asset('images/logo-pergabi.png') }}" alt="Logo PERGABI"
                                    class="h-16 w-16 object-contain">
                            </div>
                        @endif
                    </div>
                </a>
                <div class="flex flex-1 flex-col p-3">
                    @if ($kegiatan->dateLabel() !== '')
                        <p class="text-[11px] uppercase tracking-wider text-saffron-600 dark:text-gold-400">{{ $kegiatan->dateLabel() }}</p>
                    @endif
                    <h2 class="mt-1 font-display text-lg leading-snug text-navy-900 dark:text-cream-100 line-clamp-2">
                        <a href="{{ route($showRoute, $kegiatan->id) }}" class="hover:text-saffron-600 dark:hover:text-gold-400">{{ $kegiatan->title }}</a>
                    </h2>
                    @if ($kegiatan->excerpt !== '')
                        <p class="mt-2 text-sm text-navy-800/70 dark:text-cream-100/70 line-clamp-2">{{ $kegiatan->excerpt }}</p>
                    @endif
                    <a href="{{ route($showRoute, $kegiatan->id) }}" class="mt-3 text-sm font-medium text-navy-800 hover:underline dark:text-gold-400">Baca selengkapnya</a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>
@endif
