@php
    $rekening = config('pergabi.rekening');
@endphp

<section {{ $attributes->class(['rounded-2xl bg-white border border-gold-400/25 p-4']) }}>
    <p class="text-sm font-semibold text-navy-900">{{ $rekening['judul'] }}</p>
    <div class="mt-2 text-sm text-navy-900 space-y-0.5">
        <p class="font-bold">{{ $rekening['bank'] }}</p>
        <p class="font-bold">No. {{ $rekening['nomor'] }}</p>
        <p class="font-bold">A.n. {{ $rekening['atas_nama'] }}</p>
    </div>
    <p class="mt-2 text-sm text-navy-800/70">{{ $rekening['narasi'] }}</p>
    {{ $slot }}
</section>
