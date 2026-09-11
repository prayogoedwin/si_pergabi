<x-layouts.portal title="Portal Anggota">
    <section class="rounded-2xl bg-navy-900 text-cream-100 p-4 sm:p-5 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl overflow-hidden bg-navy-800 border border-gold-400/30 shrink-0">
                @if ($anggota->foto_path)
                    <img src="{{ route('portal.foto') }}" alt="Foto {{ $anggota->nama }}" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full grid place-items-center text-gold-400 text-xl font-semibold">{{ strtoupper(substr($anggota->nama, 0, 1)) }}</div>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-wider text-gold-400">Portal anggota</p>
                <h1 class="font-display text-2xl sm:text-3xl leading-tight truncate">{{ $anggota->namaLengkap() }}</h1>
                <p class="text-sm text-cream-100/70 mt-1">{{ $anggota->statusLabel() }}</p>
            </div>
        </div>
    </section>

    @if ($anggota->status === \App\Models\Anggota::STATUS_DITOLAK)
        <div class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            Pendaftaran ditolak. Hubungi pengurus cabang jika ingin mengajukan ulang.
        </div>
    @endif

    @php $steps = $anggota->portalSteps(); @endphp
    @if ($steps !== [])
        <section class="mt-4 rounded-2xl bg-white border border-gold-400/25 p-4">
            <p class="text-xs font-medium text-navy-800/60 mb-3">Progres verifikasi</p>
            <ol class="grid grid-cols-5 gap-1">
                @foreach ($steps as $step)
                    <li class="text-center">
                        <span @class([
                            'mx-auto mb-1 flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-semibold',
                            'bg-saffron-600 text-white' => $step['done'] || $step['current'],
                            'bg-cream-100 text-navy-800/40' => ! $step['done'] && ! $step['current'],
                        ])>{{ $loop->iteration }}</span>
                        <span @class([
                            'block text-[10px] sm:text-xs leading-tight',
                            'text-saffron-700 font-semibold' => $step['current'] || $step['done'],
                            'text-navy-800/50' => ! $step['done'] && ! $step['current'],
                        ])>{{ $step['label'] }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-2xl bg-white border border-gold-400/25 p-4">
            <p class="text-xs text-navy-800/50">Nomor anggota</p>
            <p class="mt-1 font-semibold text-navy-900">{{ $anggota->nomor_anggota ?: 'Belum terbit' }}</p>
            <p class="text-xs text-navy-800/50 mt-3">Masa berlaku</p>
            <p class="mt-1 text-sm">{{ $anggota->masa_berlaku_hingga?->format('d M Y') ?: '—' }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-gold-400/25 p-4">
            <p class="text-xs text-navy-800/50">Wilayah</p>
            <p class="mt-1 text-sm font-medium">PD {{ $anggota->pd?->nama ?? $anggota->pd_kode }}</p>
            <p class="text-sm">PC {{ $anggota->pc?->nama ?? $anggota->pc_kode }}</p>
        </div>
    </div>

    <a href="{{ route('portal.kta') }}"
        class="mt-4 flex items-center justify-between rounded-2xl p-4 {{ $anggota->canAccessKartuDigital() ? 'bg-saffron-600 text-white' : 'bg-white border border-gold-400/25' }}">
        <div>
            <p class="font-semibold {{ $anggota->canAccessKartuDigital() ? '' : 'text-navy-900' }}">Kartu tanda anggota digital</p>
            <p class="text-xs mt-1 {{ $anggota->canAccessKartuDigital() ? 'text-white/80' : 'text-navy-800/60' }}">
                {{ $anggota->canAccessKartuDigital() ? 'Lihat, unduh, dan cetak KTA' : 'Tersedia setelah persetujuan PP' }}
            </p>
        </div>
        <span class="text-xl">›</span>
    </a>

    <section class="mt-4 rounded-2xl bg-white border border-gold-400/25 p-4">
        <h2 class="text-sm font-semibold">Data saya</h2>
        <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-navy-800/50 text-xs">NIK</dt>
                <dd>{{ $anggota->nik }}</dd>
            </div>
            <div>
                <dt class="text-navy-800/50 text-xs">Kontak</dt>
                <dd>{{ $anggota->hp }} · {{ $anggota->email }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-navy-800/50 text-xs">Alamat</dt>
                <dd>{{ $anggota->alamat }}</dd>
            </div>
            <div>
                <dt class="text-navy-800/50 text-xs">Profesi</dt>
                <dd>{{ $anggota->status_guru }} · {{ $anggota->jenjang }}</dd>
            </div>
            <div>
                <dt class="text-navy-800/50 text-xs">Sekolah</dt>
                <dd>{{ $anggota->nama_sekolah }}</dd>
            </div>
        </dl>
    </section>

    <section class="mt-4 rounded-2xl bg-white border border-gold-400/25 overflow-hidden">
        <div class="px-4 py-3 border-b border-gold-400/20">
            <h2 class="text-sm font-semibold">Riwayat status</h2>
        </div>
        <ol class="divide-y divide-cream-100">
            @forelse ($anggota->statusLogs as $log)
                <li class="px-4 py-3">
                    <p class="text-sm font-medium">{{ $log->statusKeLabel() }}</p>
                    <p class="text-xs text-navy-800/60 mt-0.5">{{ $log->alasan ?: '—' }}</p>
                    <p class="text-[11px] text-navy-800/45 mt-1">{{ $log->user?->name ?? 'Sistem' }} · {{ $log->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                </li>
            @empty
                <li class="px-4 py-3 text-sm text-navy-800/50">Belum ada riwayat.</li>
            @endforelse
        </ol>
    </section>
</x-layouts.portal>
