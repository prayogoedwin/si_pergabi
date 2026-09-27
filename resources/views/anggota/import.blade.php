<x-layouts.app>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="mb-2 flex items-center text-sm">
                <a href="{{ route('anggota.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Anggota</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-500">Import</span>
            </div>
            <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Import anggota</h1>
            <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Unggah Excel. Provinsi dan kabupaten/kota (PD/PC) dibaca dari NTA.</p>
        </div>
        <a href="{{ route('anggota.import.template') }}">
            <x-button type="secondary" buttonType="button" tag="span">Unduh template Excel</x-button>
        </a>
    </div>

    <form method="POST" action="{{ route('anggota.import.store') }}" enctype="multipart/form-data" class="mb-6 rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-5 space-y-5 max-w-3xl">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">File Excel <span class="text-red-600">*</span></label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="block w-full text-sm">
            <p class="text-xs text-navy-800/50 dark:text-cream-100/50 mt-1">xlsx, xls, atau csv. Maksimal 5 MB. Kolom minimal: NTA, NAMA, HP, EMAIL, TEMPAT LAHIR, JL, ALAMAT. Tanpa tanggal lahir. Semua baris dihitung pendaftar baru, masa aktif 5 tahun ke depan.</p>
            @error('file')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <p class="text-sm font-medium mb-2">Akun langsung aktif?</p>
            <div class="flex gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="akun_aktif" value="1" @checked(old('akun_aktif', '1') === '1')>
                    Ya
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="akun_aktif" value="0" @checked(old('akun_aktif') === '0')>
                    Tidak
                </label>
            </div>
            <p class="text-xs text-navy-800/50 dark:text-cream-100/50 mt-1">Ya = email dianggap sudah terverifikasi, anggota bisa langsung login. Password awal tampil di hasil impor.</p>
            @error('akun_aktif')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <p class="text-sm font-medium mb-2">Anggota langsung terverifikasi seluruh jenjang?</p>
            <div class="flex gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="terverifikasi" value="1" @checked(old('terverifikasi', '1') === '1')>
                    Ya
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="terverifikasi" value="0" @checked(old('terverifikasi') === '0')>
                    Tidak
                </label>
            </div>
            <p class="text-xs text-navy-800/50 dark:text-cream-100/50 mt-1">Ya = status Aktif (PC, PD, dan PP dilewati). Tidak = masuk antrean verifikasi PC. Nomor anggota memakai NTA dari file.</p>
            @error('terverifikasi')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <x-button>Import sekarang</x-button>
        </div>
    </form>

    @if (session('import_hasil'))
        @php($hasil = session('import_hasil'))
        <div class="rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 overflow-x-auto">
            <div class="px-4 py-3 border-b border-[#e4ddd3] dark:border-gold-400/20">
                <h2 class="text-sm font-semibold">Hasil impor</h2>
                <p class="text-xs text-navy-800/60 dark:text-cream-100/60 mt-1">Salin password sekarang. Setelah meninggalkan halaman ini, password tidak ditampilkan lagi.</p>
            </div>
            <table class="min-w-full divide-y divide-[#e4ddd3] dark:divide-gold-400/20">
                <thead class="bg-[#f7f3ee] dark:bg-navy-950">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Baris</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">NTA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Password</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e4ddd3] dark:divide-gold-400/15">
                    @foreach ($hasil['rows'] as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $row['baris'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['nta'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['nama'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['email'] }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $row['password'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm {{ $row['ok'] ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">{{ $row['pesan'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
