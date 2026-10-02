<x-layouts.portal title="Profil">
    @php
        $input = 'w-full rounded-xl border border-navy-800/15 px-3 py-2.5 bg-cream-50';
        $pasFoto = $anggota->dokumenTerbaru(\App\Models\AnggotaDokumen::PAS_FOTO);
    @endphp

    <div class="mb-4">
        <h1 class="font-display text-3xl text-navy-900">Profil</h1>
        <p class="text-sm text-navy-800/70 mt-1">Lengkapi dan perbarui data seperti saat mendaftar: identitas, kontak, profesi, dokumen, dan akun.</p>
        @if ($anggota->nomor_anggota)
            <p class="mt-2 text-sm font-medium">Nomor anggota {{ $anggota->nomor_anggota }} · {{ $anggota->statusLabel() }}</p>
        @endif
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div
        class="rounded-2xl bg-white border border-gold-400/25 overflow-hidden"
        x-data="portalProfil({
            tab: @js($activeTab),
            statusGuru: @js(old('status_guru', $anggota->status_guru)),
            wilayahUrl: @js(route('daftar.wilayah')),
            provinsi: @js(old('provinsi_kode', $anggota->provinsi_kode)),
            kabupaten: @js(old('kabupaten_kode', $anggota->kabupaten_kode)),
            kecamatan: @js(old('kecamatan_kode', $anggota->kecamatan_kode)),
            kelurahan: @js(old('kelurahan_kode', $anggota->kelurahan_kode)),
            kabupatenOptions: @js($kabupatenOptions),
            kecamatanOptions: @js($kecamatanOptions),
            kelurahanOptions: @js($kelurahanOptions),
        })"
    >
        <style>[x-cloak]{display:none!important}</style>
        <div class="overflow-x-auto border-b border-gold-400/20">
            <nav class="flex min-w-max" aria-label="Data profil">
                @foreach (['identitas' => 'Data identitas', 'kontak' => 'Kontak & alamat', 'profesi' => 'Data profesi', 'dokumen' => 'Dokumen', 'akun' => 'Akun'] as $key => $label)
                    <button type="button"
                        class="border-b-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                        :class="tab === @js($key) ? 'border-saffron-600 text-saffron-700' : 'border-transparent text-navy-800/55 hover:text-navy-900'"
                        @click="tab = @js($key)">{{ $label }}</button>
                @endforeach
            </nav>
        </div>

        <form method="POST" action="{{ route('portal.profil.update') }}" enctype="multipart/form-data" class="p-4 sm:p-5" x-show="tab !== 'akun'">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" :value="tab">

            <div x-show="tab === 'identitas'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <p class="text-sm font-medium mb-2">Pas foto</p>
                    <div class="flex items-start gap-4">
                        <div class="h-28 w-24 rounded-xl overflow-hidden bg-cream-100 border border-gold-400/30 shrink-0">
                            @if ($anggota->foto_path)
                                <img src="{{ route('portal.foto') }}" alt="Pas foto {{ $anggota->nama }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <input type="file" name="pas_foto" accept="image/*" class="block w-full text-sm">
                            <p class="text-xs text-navy-800/50 mt-1">Kosongkan jika tidak diganti. Gambar, maksimal 2 MB.</p>
                            @error('pas_foto')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm mb-1">NIK <span class="text-red-600">*</span></label>
                    <input name="nik" value="{{ old('nik', $anggota->nik) }}" maxlength="16" inputmode="numeric" class="{{ $input }}">
                    @error('nik')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Nama <span class="text-red-600">*</span></label>
                    <input name="nama" value="{{ old('nama', $anggota->nama) }}" class="{{ $input }}">
                    @error('nama')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Gelar depan</label>
                    <input name="gelar_depan" value="{{ old('gelar_depan', $anggota->gelar_depan) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Gelar belakang</label>
                    <input name="gelar_belakang" value="{{ old('gelar_belakang', $anggota->gelar_belakang) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Jenis kelamin <span class="text-red-600">*</span></label>
                    <select name="jenis_kelamin" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($jenisKelamin as $value => $label)
                            <option value="{{ $value }}" @selected(old('jenis_kelamin', $anggota->jenis_kelamin) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('jenis_kelamin')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Agama <span class="text-red-600">*</span></label>
                    <select name="agama" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($agama as $value => $label)
                            <option value="{{ $value }}" @selected(old('agama', $anggota->agama) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('agama')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Tempat lahir <span class="text-red-600">*</span></label>
                    <input name="tempat_lahir" value="{{ old('tempat_lahir', $anggota->tempat_lahir) }}" class="{{ $input }}">
                    @error('tempat_lahir')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Tanggal lahir <span class="text-red-600">*</span></label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anggota->tanggal_lahir?->toDateString()) }}" class="{{ $input }}">
                    @error('tanggal_lahir')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm mb-1">Status perkawinan <span class="text-red-600">*</span></label>
                    <select name="status_perkawinan" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($statusPerkawinan as $value => $label)
                            <option value="{{ $value }}" @selected(old('status_perkawinan', $anggota->status_perkawinan) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status_perkawinan')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div x-show="tab === 'kontak'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">HP <span class="text-red-600">*</span></label>
                    <input name="hp" value="{{ old('hp', $anggota->hp) }}" class="{{ $input }}">
                    @error('hp')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">WhatsApp <span class="text-red-600">*</span></label>
                    <input name="whatsapp" value="{{ old('whatsapp', $anggota->whatsapp) }}" class="{{ $input }}">
                    @error('whatsapp')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm mb-1">Email <span class="text-red-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $anggota->email) }}" class="{{ $input }}">
                    @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm mb-1">Alamat lengkap <span class="text-red-600">*</span></label>
                    <textarea name="alamat" rows="3" class="{{ $input }}">{{ old('alamat', $anggota->alamat) }}</textarea>
                    @error('alamat')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Provinsi / PD <span class="text-red-600">*</span></label>
                    <select name="provinsi_kode" x-model="form.provinsi_kode" @change="onProvinsiChange()" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($provinsiOptions as $item)
                            <option value="{{ $item['kode'] }}">{{ $item['nama'] }}</option>
                        @endforeach
                    </select>
                    @error('provinsi_kode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Kabupaten/Kota / PC <span class="text-red-600">*</span></label>
                    <select name="kabupaten_kode" x-model="form.kabupaten_kode" @change="onKabupatenChange()" class="{{ $input }}">
                        <option value="">Pilih</option>
                        <template x-for="item in options.kabupaten" :key="item.kode">
                            <option :value="item.kode" x-text="item.nama"></option>
                        </template>
                    </select>
                    @error('kabupaten_kode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Kecamatan <span class="text-red-600">*</span></label>
                    <select name="kecamatan_kode" x-model="form.kecamatan_kode" @change="onKecamatanChange()" class="{{ $input }}">
                        <option value="">Pilih</option>
                        <template x-for="item in options.kecamatan" :key="item.kode">
                            <option :value="item.kode" x-text="item.nama"></option>
                        </template>
                    </select>
                    @error('kecamatan_kode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Kelurahan <span class="text-red-600">*</span></label>
                    <select name="kelurahan_kode" x-model="form.kelurahan_kode" class="{{ $input }}">
                        <option value="">Pilih</option>
                        <template x-for="item in options.kelurahan" :key="item.kode">
                            <option :value="item.kode" x-text="item.nama"></option>
                        </template>
                    </select>
                    @error('kelurahan_kode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm mb-1">Kode pos <span class="text-red-600">*</span></label>
                    <input name="kode_pos" value="{{ old('kode_pos', $anggota->kode_pos) }}" class="{{ $input }}">
                    @error('kode_pos')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div x-show="tab === 'profesi'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Status guru <span class="text-red-600">*</span></label>
                    <select name="status_guru" x-model="statusGuru" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($statusGuru as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                        @if (filled($anggota->status_guru) && ! array_key_exists($anggota->status_guru, $statusGuru))
                            <option value="{{ $anggota->status_guru }}">{{ $anggota->status_guru }}</option>
                        @endif
                    </select>
                    @error('status_guru')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Jenjang <span class="text-red-600">*</span></label>
                    <select name="jenjang" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($jenjang as $value => $label)
                            <option value="{{ $value }}" @selected(old('jenjang', $anggota->jenjang) === $value)>{{ $label }}</option>
                        @endforeach
                        @php $jenjangSaatIni = old('jenjang', $anggota->jenjang); @endphp
                        @if (filled($jenjangSaatIni) && ! array_key_exists($jenjangSaatIni, $jenjang))
                            <option value="{{ $jenjangSaatIni }}" selected>{{ $jenjangSaatIni }}</option>
                        @endif
                    </select>
                    @error('jenjang')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">NIP / NIPPPK <span x-show="nipRequired()" class="text-red-600">*</span></label>
                    <input name="nip" value="{{ old('nip', $anggota->nip) }}" class="{{ $input }}">
                    @error('nip')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">NUPTK</label>
                    <input name="nuptk" value="{{ old('nuptk', $anggota->nuptk) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Nama sekolah <span class="text-red-600">*</span></label>
                    <input name="nama_sekolah" value="{{ old('nama_sekolah', $anggota->nama_sekolah) }}" class="{{ $input }}">
                    @error('nama_sekolah')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">NPSN <span class="text-navy-800/50 font-normal">(opsional)</span></label>
                    <input name="npsn" value="{{ old('npsn', $anggota->npsn) }}" class="{{ $input }}">
                    @error('npsn')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Status sekolah <span class="text-red-600">*</span></label>
                    <select name="status_sekolah" class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($statusSekolah as $value => $label)
                            <option value="{{ $value }}" @selected(old('status_sekolah', $anggota->status_sekolah) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status_sekolah')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm mb-1">Alamat sekolah <span class="text-red-600">*</span></label>
                    <textarea name="alamat_sekolah" rows="3" class="{{ $input }}">{{ old('alamat_sekolah', $anggota->alamat_sekolah) }}</textarea>
                    @error('alamat_sekolah')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div x-show="tab === 'dokumen'" x-cloak class="space-y-4">
                @php $skMengajar = $anggota->dokumenTerbaru(\App\Models\AnggotaDokumen::SK_MENGAJAR); @endphp
                <div>
                    <p class="text-sm font-medium">SK Mengajar <span class="text-red-600">*</span></p>
                    @if ($skMengajar)
                        <p class="text-xs text-navy-800/55 mt-1">
                            Berkas saat ini:
                            <a href="{{ route('anggota.dokumen', [$anggota, $skMengajar]) }}" class="text-saffron-700 hover:underline" target="_blank" rel="noopener">{{ $skMengajar->nama_asli }}</a>
                        </p>
                    @else
                        <p class="text-xs text-navy-800/55 mt-1">Belum ada berkas.</p>
                    @endif
                    <input type="file" name="sk_mengajar" accept="image/*,.pdf" class="mt-2 block w-full text-sm">
                    <p class="text-xs text-navy-800/50 mt-1">Kosongkan jika tidak diganti. JPG, PNG, atau PDF, maksimal 2 MB.</p>
                    @error('sk_mengajar')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @php $buktiPembayaran = $anggota->dokumenTerbaru(\App\Models\AnggotaDokumen::BUKTI_PEMBAYARAN); @endphp
                <div>
                    <p class="text-sm font-medium">Bukti pembayaran</p>
                    @if ($buktiPembayaran)
                        <p class="text-xs text-navy-800/55 mt-1">
                            Berkas saat ini:
                            <a href="{{ route('anggota.dokumen', [$anggota, $buktiPembayaran]) }}" class="text-saffron-700 hover:underline" target="_blank" rel="noopener">{{ $buktiPembayaran->nama_asli }}</a>
                        </p>
                    @else
                        <p class="text-xs text-navy-800/55 mt-1">Belum diunggah.</p>
                    @endif
                    <input type="file" name="bukti_pembayaran" accept="image/*,.pdf" class="mt-2 block w-full text-sm">
                    <p class="text-xs text-navy-800/50 mt-1">JPG, PNG, atau PDF, maksimal 2 MB.</p>
                    @error('bukti_pembayaran')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <p class="text-xs text-navy-800/50">Pas foto diperbarui di tab Data identitas.</p>
            </div>

            <div class="mt-6 flex justify-end" x-show="tab !== 'akun'">
                <button type="submit" class="rounded-xl bg-saffron-600 text-white px-5 py-2.5 text-sm font-medium">Simpan data</button>
            </div>
        </form>

        <section class="p-4 sm:p-5 space-y-6" x-show="tab === 'akun'" x-cloak>
            <div>
                <h2 class="font-semibold">Data keanggotaan</h2>
                <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-navy-800/55">Nomor anggota</dt>
                        <dd class="font-medium">{{ $anggota->nomor_anggota ?: 'Belum terbit' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-navy-800/55">Status</dt>
                        <dd class="font-medium">{{ $anggota->statusLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-navy-800/55">PD PERGABI</dt>
                        <dd class="font-medium">{{ $anggota->labelPdPergabi() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-navy-800/55">PC / kabupaten-kota</dt>
                        <dd class="font-medium">{{ $anggota->kabupaten?->nama ?? $anggota->kabupaten_kode ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-navy-800/55">Tanggal bergabung</dt>
                        <dd class="font-medium">{{ $anggota->tanggal_bergabung?->translatedFormat('d F Y') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-navy-800/55">Masa berlaku</dt>
                        <dd class="font-medium">{{ $anggota->masaBerlakuLabel() }}</dd>
                    </div>
                </dl>
            </div>
            <h2 class="font-semibold">Ganti password</h2>
            <form method="POST" action="{{ route('portal.password') }}" class="mt-4 space-y-3 max-w-md">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm mb-1">Password saat ini</label>
                    <input type="password" name="current_password" required class="{{ $input }}">
                    @error('current_password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Password baru</label>
                    <input type="password" name="password" required class="{{ $input }}">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Ulangi password baru</label>
                    <input type="password" name="password_confirmation" required class="{{ $input }}">
                </div>
                <button type="submit" class="rounded-xl bg-navy-900 text-cream-100 px-4 py-2.5 text-sm font-medium">Perbarui password</button>
            </form>
        </section>
    </div>

    <script>
        function portalProfil(config) {
            return {
                tab: config.tab || 'identitas',
                statusGuru: config.statusGuru || '',
                form: {
                    provinsi_kode: config.provinsi || '',
                    kabupaten_kode: config.kabupaten || '',
                    kecamatan_kode: config.kecamatan || '',
                    kelurahan_kode: config.kelurahan || '',
                },
                options: {
                    kabupaten: config.kabupatenOptions || [],
                    kecamatan: config.kecamatanOptions || [],
                    kelurahan: config.kelurahanOptions || [],
                },
                nipRequired() {
                    return this.statusGuru === 'ASN';
                },
                async onProvinsiChange() {
                    this.form.kabupaten_kode = '';
                    this.form.kecamatan_kode = '';
                    this.form.kelurahan_kode = '';
                    this.options.kabupaten = [];
                    this.options.kecamatan = [];
                    this.options.kelurahan = [];
                    if (this.form.provinsi_kode) {
                        this.options.kabupaten = await this.loadWilayah(this.form.provinsi_kode);
                    }
                },
                async onKabupatenChange() {
                    this.form.kecamatan_kode = '';
                    this.form.kelurahan_kode = '';
                    this.options.kecamatan = [];
                    this.options.kelurahan = [];
                    if (this.form.kabupaten_kode) {
                        this.options.kecamatan = await this.loadWilayah(this.form.kabupaten_kode);
                    }
                },
                async onKecamatanChange() {
                    this.form.kelurahan_kode = '';
                    this.options.kelurahan = [];
                    if (this.form.kecamatan_kode) {
                        this.options.kelurahan = await this.loadWilayah(this.form.kecamatan_kode);
                    }
                },
                async loadWilayah(parent) {
                    const url = new URL(config.wilayahUrl, window.location.origin);
                    url.searchParams.set('parent', parent);
                    const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
                    return response.json();
                },
            };
        }
    </script>
</x-layouts.portal>
