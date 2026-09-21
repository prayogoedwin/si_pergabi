<x-layouts.portal title="Kegiatan" :wide="true">
    <div class="mb-5">
        <p class="text-[11px] uppercase tracking-wider text-saffron-600">Portal anggota</p>
        <h1 class="font-display text-3xl font-bold leading-tight">Kegiatan</h1>
        <p class="mt-1 text-sm text-navy-800/60">Program, acara, dan aktivitas PERGABI</p>
    </div>

    @include('kegiatan._grid')
</x-layouts.portal>
