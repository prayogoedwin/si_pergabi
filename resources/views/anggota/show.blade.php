<x-layouts.app>
    <div class="mb-6 flex items-center text-sm print:hidden">
        <a href="{{ route('anggota.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Anggota</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">{{ $anggota->namaLengkap() }}</span>
    </div>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $anggota->namaLengkap() }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $anggota->statusLabel() }}</p>
            @if ($anggota->nomor_anggota)
                <p class="mt-2 font-semibold">Nomor anggota {{ $anggota->nomor_anggota }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('anggota.qr', $anggota) }}" class="inline-flex items-center rounded-lg bg-navy-900 px-4 py-2 text-sm font-medium text-cream-100 hover:bg-navy-800">QR Code</a>
            @if ($anggota->user)
                <form action="{{ route('anggota.reset-password', $anggota) }}" method="POST" onsubmit="return confirm('Reset password akun login anggota ini? Password baru akan ditampilkan sekali.')">
                    @csrf
                    <x-button type="secondary">Reset password</x-button>
                </form>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm print:hidden">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 print:hidden">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3 text-sm">
            <p><span class="text-gray-500">NIK</span> · {{ $anggota->nik }}</p>
            <p><span class="text-gray-500">TTL</span> · {{ $anggota->tempat_lahir }}, {{ $anggota->tanggal_lahir?->format('d M Y') }}</p>
            <p><span class="text-gray-500">Kontak</span> · {{ $anggota->hp }} / WA {{ $anggota->whatsapp }}</p>
            <p>{{ $anggota->alamat }}</p>
            <p>{{ $anggota->kelurahan?->nama }}, {{ $anggota->kecamatan?->nama }}, {{ $anggota->kabupaten?->nama }}, {{ $anggota->provinsi?->nama }} {{ $anggota->kode_pos }}</p>
            <p class="pt-2"><span class="text-gray-500">Profesi</span> · {{ $anggota->status_guru }} · {{ $anggota->jenjang }} · {{ $anggota->nama_sekolah }}</p>
        </div>
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
            x-data="{
                open: false,
                title: '',
                previewUrl: '',
                downloadUrl: '',
                show(title, previewUrl, downloadUrl) {
                    this.title = title;
                    this.previewUrl = previewUrl;
                    this.downloadUrl = downloadUrl;
                    this.open = true;
                },
                close() {
                    this.open = false;
                    this.previewUrl = '';
                }
            }"
            @keydown.escape.window="close()"
        >
            <style>[x-cloak]{display:none!important}</style>
            <p class="text-sm font-semibold mb-3">Dokumen</p>
            <ul class="space-y-2 text-sm">
                @forelse ($anggota->dokumen as $dokumen)
                    <li>
                        <button type="button"
                            class="text-blue-600 dark:text-blue-400 hover:underline text-left"
                            @click="show(
                                @js($dokumen->jenisLabel()),
                                @js(route('anggota.dokumen', [$anggota, $dokumen])),
                                @js(route('anggota.dokumen', [$anggota, $dokumen, 'download' => 1]))
                            )">
                            {{ $dokumen->jenisLabel() }}
                        </button>
                    </li>
                @empty
                    <li class="text-navy-800/50 dark:text-cream-100/50">Belum ada dokumen.</li>
                @endforelse
            </ul>

            <div x-show="open" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="display: none;">
                <div class="absolute inset-0 bg-navy-950/70" @click="close()"></div>
                <div class="relative z-10 flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-navy-900">
                    <div class="flex items-center justify-between gap-3 border-b border-[#e4ddd3] px-4 py-3 dark:border-gold-400/25">
                        <h2 class="min-w-0 truncate font-semibold text-navy-900 dark:text-cream-100" x-text="title">Dokumen</h2>
                        <button type="button" class="text-sm text-navy-800/70 hover:text-navy-900 dark:text-gold-400" @click="close()">Tutup</button>
                    </div>
                    <iframe :src="previewUrl" class="h-[70vh] w-full bg-[#f7f3ee]" title="Pratinjau dokumen"></iframe>
                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-[#e4ddd3] px-4 py-3 dark:border-gold-400/25">
                        <button type="button" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700" @click="close()">Tutup</button>
                        <a :href="downloadUrl" class="rounded-lg bg-saffron-600 px-4 py-2 text-sm font-medium text-white hover:bg-saffron-700">Download</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="font-semibold mb-1 print:hidden">Kartu tanda anggota</h2>
        @if ($unlocked)
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 print:hidden">Cetak ID card, A4, atau unduh gambar seperti di portal anggota.</p>
            @include('kta._cetak')
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">Kartu digital dan cetak terbuka setelah pendaftaran disetujui Pengurus Pusat.</p>
            <p class="text-sm text-gray-500 mt-1">Status saat ini: {{ $anggota->statusLabel() }}</p>
        @endif
    </div>

    @if ($canVerifyPc || $canValidatePd || $canApprovePp)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 print:hidden">
            <h2 class="font-semibold mb-3">Proses verifikasi</h2>
            <form method="POST" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Catatan <span class="text-navy-800/40 dark:text-cream-100/40 font-normal">(wajib jika menolak)</span></label>
                <textarea name="alasan" rows="3" class="w-full rounded-lg" placeholder="Opsional untuk verifikasi. Wajib diisi jika menolak.">{{ old('alasan') }}</textarea>
                <div class="flex flex-wrap gap-3">
                    @if ($canVerifyPc)
                        <button formmethod="POST" formaction="{{ route('anggota.verify-pc', $anggota) }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Verifikasi PC</button>
                    @endif
                    @if ($canValidatePd)
                        <button formmethod="POST" formaction="{{ route('anggota.validate-pd', $anggota) }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Validasi PD</button>
                    @endif
                    @if ($canApprovePp)
                        <button formmethod="POST" formaction="{{ route('anggota.approve-pp', $anggota) }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Setujui PP</button>
                    @endif
                    <button formmethod="POST" formaction="{{ route('anggota.reject', $anggota) }}" class="px-4 py-2 rounded-lg bg-red-600 text-white" onclick="this.form.alasan.required = true; this.form.alasan.minLength = 5;">Tolak</button>
                </div>
            </form>
        </div>
    @endif

    <div class="print:hidden">
        @include('anggota._status-log')
    </div>
</x-layouts.app>
