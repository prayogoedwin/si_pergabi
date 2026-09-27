@php
    $ornamen = (isset($ornamen) && is_array($ornamen) && $ornamen !== [])
        ? $ornamen
        : app(\App\Services\SettingService::class)->ktaOrnamen();
@endphp
<div class="kta-ornamen" aria-hidden="true">
    @if (! empty($ornamen['watermark']))
        <img src="{{ $ornamen['watermark'] }}" alt="" class="kta-watermark">
    @endif
    @if (! empty($ornamen['kiri_atas']))
        <img src="{{ $ornamen['kiri_atas'] }}" alt="" class="kta-ornamen-kiri-atas">
    @endif
    @if (! empty($ornamen['kanan_atas']))
        <img src="{{ $ornamen['kanan_atas'] }}" alt="" class="kta-ornamen-kanan-atas">
    @endif
    @if (! empty($ornamen['kanan_bawah']))
        <img src="{{ $ornamen['kanan_bawah'] }}" alt="" class="kta-ornamen-kanan-bawah">
    @endif
    @if (! empty($ornamen['lambang']))
        <img src="{{ $ornamen['lambang'] }}" alt="" class="kta-lambang">
    @endif
</div>
