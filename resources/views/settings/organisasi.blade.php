<x-layouts.app>
    <div class="mb-6">
        <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Identitas organisasi</h1>
        <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Data resmi PERGABI untuk kartu cetak KTA. Unggah baru jika ingin mengganti berkas default.</p>
    </div>

    @php
        $ktaPreview = [
            'nama_lengkap' => old('nama_lengkap', $values['nama_lengkap']),
            'singkatan' => old('singkatan', $values['singkatan']),
            'alamat' => old('alamat', $values['alamat']),
            'nama_ketua_umum' => old('nama_ketua_umum', $values['nama_ketua_umum']),
            'nama_sekretaris_jenderal' => old('nama_sekretaris_jenderal', $values['nama_sekretaris_jenderal']),
            'visi' => old('visi', $values['visi']),
            'logo_url' => $values['logo_url'],
            'stempel_url' => $values['stempel_url'],
            'ttd_ketua_umum_url' => $values['ttd_ketua_umum_url'],
            'ttd_sekretaris_jenderal_url' => $values['ttd_sekretaris_jenderal_url'],
            'defaults' => [
                'logo_url' => asset(config('pergabi.files.logo')),
                'stempel_url' => asset(config('pergabi.files.stempel')),
                'ttd_ketua_umum_url' => asset(config('pergabi.files.ttd_ketua_umum')),
                'ttd_sekretaris_jenderal_url' => asset(config('pergabi.files.ttd_sekretaris_jenderal')),
            ],
        ];
    @endphp

    <script>
        window.__ktaIdentitasPreview = @json($ktaPreview);
    </script>

    <div class="space-y-6" x-data="ktaIdentitasPreview(window.__ktaIdentitasPreview)">
        @include('settings._kta-contoh')

        <form method="POST" action="{{ route('settings.organisasi.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white dark:bg-navy-900 rounded-xl border border-[#e4ddd3] dark:border-gold-400/25 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-navy-900 dark:text-cream-100">Nama organisasi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Nama lengkap <span class="text-red-600">*</span></label>
                        <input name="nama_lengkap" x-model="nama_lengkap" value="{{ old('nama_lengkap', $values['nama_lengkap']) }}" class="w-full rounded-lg">
                        @error('nama_lengkap')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Singkatan <span class="text-red-600">*</span></label>
                        <input name="singkatan" x-model="singkatan" value="{{ old('singkatan', $values['singkatan']) }}" class="w-full rounded-lg">
                        @error('singkatan')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Alamat sekretariat <span class="text-red-600">*</span></label>
                        <textarea name="alamat" x-model="alamat" rows="2" class="w-full rounded-lg">{{ old('alamat', $values['alamat']) }}</textarea>
                        @error('alamat')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Ketua Umum <span class="text-red-600">*</span></label>
                        <input name="nama_ketua_umum" x-model="nama_ketua_umum" value="{{ old('nama_ketua_umum', $values['nama_ketua_umum']) }}" class="w-full rounded-lg">
                        @error('nama_ketua_umum')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Sekretaris Jenderal <span class="text-red-600">*</span></label>
                        <input name="nama_sekretaris_jenderal" x-model="nama_sekretaris_jenderal" value="{{ old('nama_sekretaris_jenderal', $values['nama_sekretaris_jenderal']) }}" class="w-full rounded-lg">
                        @error('nama_sekretaris_jenderal')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Visi <span class="text-red-600">*</span></label>
                    <textarea name="visi" x-model="visi" rows="3" class="w-full rounded-lg">{{ old('visi', $values['visi']) }}</textarea>
                    @error('visi')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Misi <span class="text-red-600">*</span></label>
                    <textarea name="misi" rows="3" class="w-full rounded-lg">{{ old('misi', $values['misi']) }}</textarea>
                    @error('misi')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bg-white dark:bg-navy-900 rounded-xl border border-[#e4ddd3] dark:border-gold-400/25 p-6">
                <h2 class="text-lg font-semibold text-navy-900 dark:text-cream-100 mb-4">Berkas kartu</h2>
                <p class="text-sm text-navy-800/50 dark:text-cream-100/60 mb-4">Default ada di <code class="text-xs">public/images/organisasi/</code>. Unggah baru untuk mengganti; hapus unggahan untuk kembali ke berkas default. Contoh tampilan di atas ikut berubah.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ([
                        'logo' => 'Logo',
                        'stempel' => 'Stempel',
                        'ttd_ketua_umum' => 'TTD Ketua Umum',
                        'ttd_sekretaris_jenderal' => 'TTD Sekretaris Jenderal',
                    ] as $field => $label)
                        @php
                            $url = $values[$field.'_url'] ?? null;
                            $custom = (bool) ($values[$field.'_custom'] ?? false);
                        @endphp
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                            <input type="file" name="{{ $field }}" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm" @change="previewFile($event, '{{ $field }}_url')">
                            @if ($url)
                                <img :src="{{ $field }}_url" alt="{{ $label }}" class="mt-3 h-24 max-w-full object-contain rounded-lg border border-[#e4ddd3] dark:border-gold-400/30 bg-white p-2">
                                @if ($custom)
                                    <label class="mt-2 flex items-center gap-2 text-sm text-navy-800/70 dark:text-cream-100/70">
                                        <input type="checkbox" name="{{ $field }}_hapus" value="1" @change="restoreIfChecked($event, '{{ $field }}_url')">
                                        Kembalikan ke berkas default
                                    </label>
                                @else
                                    <p class="mt-2 text-xs text-navy-800/50 dark:text-cream-100/60">Berkas default</p>
                                @endif
                            @endif
                            @error($field)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="px-6 py-3 rounded-lg bg-saffron-600 hover:bg-saffron-700 text-white font-semibold">Simpan identitas</button>
        </form>
    </div>

    <script>
        function ktaIdentitasPreview(initial) {
            return {
                nama_lengkap: initial.nama_lengkap,
                singkatan: initial.singkatan,
                alamat: initial.alamat,
                nama_ketua_umum: initial.nama_ketua_umum,
                nama_sekretaris_jenderal: initial.nama_sekretaris_jenderal,
                visi: initial.visi,
                logo_url: initial.logo_url,
                stempel_url: initial.stempel_url,
                ttd_ketua_umum_url: initial.ttd_ketua_umum_url,
                ttd_sekretaris_jenderal_url: initial.ttd_sekretaris_jenderal_url,
                defaults: initial.defaults,
                init() {
                    this.$nextTick(() => this.drawQr());
                },
                previewFile(event, key) {
                    const file = event.target.files?.[0];
                    if (! file) {
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = () => { this[key] = reader.result; };
                    reader.readAsDataURL(file);
                },
                restoreIfChecked(event, key) {
                    if (event.target.checked) {
                        this[key] = this.defaults[key];
                    }
                },
                drawQr() {
                    const canvas = this.$refs.qr;
                    if (! canvas) {
                        return;
                    }
                    const ctx = canvas.getContext('2d');
                    const n = 21;
                    const s = canvas.width / n;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#111111';
                    const finder = (ox, oy) => {
                        for (let i = 0; i < 7; i++) {
                            for (let j = 0; j < 7; j++) {
                                if (i === 0 || j === 0 || i === 6 || j === 6 || (i >= 2 && i <= 4 && j >= 2 && j <= 4)) {
                                    ctx.fillRect((ox + i) * s, (oy + j) * s, s, s);
                                }
                            }
                        }
                    };
                    finder(0, 0);
                    finder(14, 0);
                    finder(0, 14);
                    let seed = 2026;
                    for (let y = 0; y < n; y++) {
                        for (let x = 0; x < n; x++) {
                            if ((x < 8 && y < 8) || (x > 12 && y < 8) || (x < 8 && y > 12)) {
                                continue;
                            }
                            seed = (seed * 1103515245 + 12345) & 0x7fffffff;
                            if (seed % 3 === 0) {
                                ctx.fillRect(x * s, y * s, s, s);
                            }
                        }
                    }
                },
            };
        }
    </script>
</x-layouts.app>
