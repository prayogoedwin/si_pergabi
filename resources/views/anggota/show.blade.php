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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <p class="text-sm font-semibold mb-3">Dokumen</p>
            <ul class="space-y-2 text-sm">
                @foreach ($anggota->dokumen as $dokumen)
                    <li>
                        <a href="{{ route('anggota.dokumen', [$anggota, $dokumen]) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $dokumen->jenisLabel() }}
                        </a>
                    </li>
                @endforeach
            </ul>
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
