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
            @if ($anggota->masa_berlaku_hingga)
                <p class="mt-1 text-sm {{ $anggota->isMasaBerlakuHabis() ? 'text-red-600' : 'text-gray-600 dark:text-gray-400' }}">
                    Masa berlaku {{ $anggota->masa_berlaku_hingga->format('d M Y') }}
                    @if ($anggota->isMasaBerlakuHabis())
                        · Habis
                    @endif
                </p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('anggota.qr', $anggota) }}" class="inline-flex items-center rounded-lg bg-navy-900 px-4 py-2 text-sm font-medium text-cream-100 hover:bg-navy-800">QR Code</a>
            @if ($anggota->user)
                <form action="{{ route('anggota.resend-verification', $anggota) }}" method="POST">
                    @csrf
                    <x-button type="secondary">Kirim ulang verifikasi</x-button>
                </form>
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

    @php
        $nilai = fn (mixed $value): string => filled($value) ? (string) $value : '—';
        $pasFoto = $anggota->dokumenTerbaru(\App\Models\AnggotaDokumen::PAS_FOTO);
        $kanalLabel = match ($anggota->kanal_verifikasi) {
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            default => 'Pendaftaran langsung',
        };
    @endphp

    <div
        class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm print:hidden dark:border-gray-700 dark:bg-gray-800"
        x-data="{
            tab: 'pribadi',
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
        <div class="overflow-x-auto border-b border-gray-200 dark:border-gray-700">
            <nav class="flex min-w-max" aria-label="Data pendaftaran">
                @foreach (['pribadi' => 'Pribadi', 'kontak' => 'Kontak', 'profesi' => 'Profesi', 'dokumen' => 'Dokumen', 'akun' => 'Akun'] as $key => $label)
                    <button type="button"
                        class="border-b-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                        :class="tab === @js($key) ? 'border-saffron-600 text-saffron-700' : 'border-transparent text-navy-800/60 hover:text-navy-900 dark:text-cream-100/60 dark:hover:text-cream-100'"
                        @click="tab = @js($key)">{{ $label }}</button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            <dl x-show="tab === 'pribadi'" class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                @if ($pasFoto)
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500">Pas foto</dt>
                        <dd class="mt-2">
                            <button type="button"
                                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600"
                                @click="show(
                                    @js($pasFoto->jenisLabel()),
                                    @js(route('anggota.dokumen', [$anggota, $pasFoto])),
                                    @js(route('anggota.dokumen', [$anggota, $pasFoto, 'download' => 1]))
                                )">
                                <img src="{{ route('anggota.dokumen', [$anggota, $pasFoto]) }}" alt="Pas foto {{ $anggota->nama }}" class="h-36 w-28 object-cover">
                            </button>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs text-gray-500">NIK</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->nik) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Nama</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->nama) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Gelar depan</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->gelar_depan) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Gelar belakang</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->gelar_belakang) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Jenis kelamin</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->jenisKelaminLabel()) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Agama</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->agama) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Tempat lahir</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->tempat_lahir) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Tanggal lahir</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $anggota->tanggal_lahir?->format('d M Y') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Status perkawinan</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->status_perkawinan) }}</dd>
                </div>
            </dl>

            <dl x-show="tab === 'kontak'" x-cloak class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">HP</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->hp) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">WhatsApp</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->whatsapp) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500">Email</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->email) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500">Alamat lengkap</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->alamat) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Provinsi / PD</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->provinsi?->nama ?? $anggota->provinsi_kode) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Kabupaten/Kota / PC</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->kabupaten?->nama ?? $anggota->kabupaten_kode) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Kecamatan</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->kecamatan?->nama ?? $anggota->kecamatan_kode) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Kelurahan</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->kelurahan?->nama ?? $anggota->kelurahan_kode) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Kode pos</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->kode_pos) }}</dd>
                </div>
            </dl>

            <dl x-show="tab === 'profesi'" x-cloak class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">Status guru</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->status_guru) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Jenjang</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->jenjang) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">NIP / NIPPPK</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->nip) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">NUPTK</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->nuptk) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Nama sekolah</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->nama_sekolah) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">NPSN</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->npsn) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Status sekolah</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->status_sekolah) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500">Alamat sekolah</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->alamat_sekolah) }}</dd>
                </div>
            </dl>

            <div x-show="tab === 'dokumen'" x-cloak>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach (\App\Models\AnggotaDokumen::formJenisLabels() as $jenis => $label)
                        @php $dokumen = $anggota->dokumenTerbaru($jenis); @endphp
                        <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div>
                                <p class="text-sm font-medium">{{ $label }}</p>
                                <p class="text-xs text-gray-500">{{ $dokumen?->nama_asli ?: (in_array($jenis, \App\Models\AnggotaDokumen::requiredJenis(), true) ? 'Belum diunggah' : 'Opsional, belum diunggah') }}</p>
                            </div>
                            @if ($dokumen)
                                <button type="button"
                                    class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    @click="show(
                                        @js($dokumen->jenisLabel()),
                                        @js(route('anggota.dokumen', [$anggota, $dokumen])),
                                        @js(route('anggota.dokumen', [$anggota, $dokumen, 'download' => 1]))
                                    )">Lihat</button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <dl x-show="tab === 'akun'" x-cloak class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">Email login</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->user?->email ?? $anggota->email) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Status verifikasi email</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $anggota->user?->hasVerifiedEmail() ? 'Sudah terverifikasi' : 'Belum terverifikasi' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Kanal verifikasi</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $kanalLabel }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Status keanggotaan</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $anggota->statusLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">PD PERGABI</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->pd?->nama ?? $anggota->pd_kode) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">PC PERGABI</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->pc?->nama ?? $anggota->pc_kode) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Nomor anggota</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $nilai($anggota->nomor_anggota) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Tanggal bergabung</dt>
                    <dd class="mt-0.5 text-sm font-medium">{{ $anggota->tanggal_bergabung?->format('d M Y') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Masa berlaku</dt>
                    <dd class="mt-0.5 text-sm font-medium {{ $anggota->isMasaBerlakuHabis() ? 'text-red-600' : '' }}">
                        {{ $anggota->masa_berlaku_hingga?->format('d M Y') ?: '—' }}
                        @if ($anggota->isMasaBerlakuHabis())
                            · Habis
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="font-semibold mb-1 print:hidden">Kartu tanda anggota</h2>
        @if ($hasKartu)
            @unless ($unlocked)
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 print:hidden">
                    <p class="font-semibold">Tidak aktif</p>
                    <p class="mt-1">Kartu tidak berlaku sampai keanggotaan aktif kembali.</p>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 print:hidden">Cetak ID card, A4, atau unduh gambar seperti di portal anggota.</p>
            @endunless
            @include('kta._cetak', ['allowPrint' => $unlocked])
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

    @if ($canToggleStatus)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 print:hidden">
            <h2 class="font-semibold mb-1">Status keanggotaan</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Status saat ini: {{ $anggota->statusLabel() }}. Nonaktifkan jika keanggotaan dihentikan; Kartu Tanda Anggota tidak berlaku selama status tidak aktif.</p>
            <form method="POST" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Catatan <span class="text-navy-800/40 dark:text-cream-100/40 font-normal">{{ $anggota->isAktif() ? '(wajib jika menonaktifkan)' : '(opsional)' }}</span></label>
                <textarea name="alasan" rows="3" class="w-full rounded-lg" placeholder="{{ $anggota->isAktif() ? 'Wajib diisi, misalnya mengundurkan diri atau masa keanggotaan berakhir.' : 'Opsional. Contoh: keanggotaan diperpanjang.' }}">{{ old('alasan') }}</textarea>
                <div class="flex flex-wrap gap-3">
                    @if ($anggota->isAktif())
                        <button formmethod="POST" formaction="{{ route('anggota.deactivate', $anggota) }}" class="px-4 py-2 rounded-lg bg-red-600 text-white" onclick="this.form.alasan.required = true; this.form.alasan.minLength = 5; return confirm('Nonaktifkan anggota ini? Kartu tanda anggota tidak berlaku sampai diaktifkan kembali.');">Nonaktifkan</button>
                    @else
                        <button formmethod="POST" formaction="{{ route('anggota.activate', $anggota) }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white" onclick="return confirm('Aktifkan kembali anggota ini?');">Aktifkan kembali</button>
                    @endif
                </div>
            </form>
        </div>
    @endif

    <div class="print:hidden">
        @include('anggota._status-log')
    </div>
</x-layouts.app>
