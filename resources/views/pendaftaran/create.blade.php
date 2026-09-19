<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Anggota — PERGABI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pergabi.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700|cormorant-garamond:600,700" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Be Vietnam Pro', 'sans-serif'],
                        display: ['Cormorant Garamond', 'serif'],
                    },
                    colors: {
                        saffron: { 500: '#ee6b24', 600: '#e25a12', 700: '#c94b10' },
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: "Be Vietnam Pro", sans-serif;
            background:
                radial-gradient(ellipse 80% 40% at 50% 0%, rgba(238, 107, 36, 0.22), transparent 55%),
                linear-gradient(165deg, #06101c 0%, #0c2244 48%, #1a1230 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8"
        x-data="PergabiPendaftaran({
            storeUrl: @js(route('daftar.store')),
            wilayahUrl: @js(route('daftar.wilayah')),
            csrf: @js(csrf_token()),
            verifikasi: @js($wizardConfig),
            email: @js(session('google_email')),
        })"
        @change="persist()">
        <script type="application/json" id="wizard-config">@json($wizardConfig)</script>
        <header class="max-w-4xl mx-auto mb-6 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-[#fff6ea]">
                <img src="{{ asset('images/logo-pergabi.png') }}" alt="PERGABI" class="w-12 h-12 object-contain">
                <span>
                    <span class="block font-display text-2xl tracking-[0.14em] leading-none">PERGABI</span>
                    <span class="block text-xs text-[#fff6ea]/70 mt-1">Pendaftaran anggota</span>
                </span>
            </a>
            <a href="{{ route('login') }}" class="text-sm text-[#f0c14b] hover:underline">Sudah punya akun? Masuk</a>
        </header>

        <main class="max-w-4xl mx-auto bg-[#fff8f1] rounded-3xl shadow-2xl overflow-hidden">
            <div class="px-5 sm:px-8 pt-6 pb-4 border-b border-orange-100">
                <h1 class="font-display text-3xl sm:text-4xl text-slate-900">Formulir pendaftaran</h1>
                <p class="text-sm text-slate-600 mt-1">Isian tersimpan otomatis di perangkat Anda. Jika koneksi terputus, Anda bisa lanjut tanpa mulai dari awal.</p>
                @if (session('status'))
                    <div class="mt-3 rounded-xl border border-saffron-600/30 bg-orange-50 px-4 py-3 text-sm text-saffron-700">
                        {{ session('status') }}
                    </div>
                @endif
                <p class="text-xs text-saffron-700 mt-2" x-show="savedAt">
                    Draf tersimpan <span x-text="savedAt ? new Date(savedAt).toLocaleString('id-ID') : ''"></span>
                </p>
            </div>

            <ol class="hidden md:grid grid-cols-5 gap-2 px-8 py-4 bg-white/60">
                @foreach ([1 => 'Pribadi', 2 => 'Kontak', 3 => 'Profesi', 4 => 'Dokumen', 5 => 'Akun'] as $number => $label)
                    <li>
                        <button type="button" class="w-full text-left" @click="go({{ $number }})">
                            <span class="flex items-center gap-2 text-sm font-semibold"
                                :class="step === {{ $number }} ? 'text-saffron-700' : (step > {{ $number }} ? 'text-emerald-700' : 'text-slate-400')">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs"
                                    :class="step === {{ $number }} ? 'bg-saffron-600 text-white' : (step > {{ $number }} ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600')">{{ $number }}</span>
                                {{ $label }}
                            </span>
                        </button>
                    </li>
                @endforeach
            </ol>

            <div class="md:hidden px-5 py-3 bg-white/60">
                <div class="flex items-center justify-between text-sm font-semibold text-saffron-700">
                    <span>Langkah <span x-text="step"></span> dari 5</span>
                    <span x-text="['', 'Data pribadi', 'Kontak & alamat', 'Data profesi', 'Dokumen', 'Akun'][step]"></span>
                </div>
                <div class="mt-2 h-2 rounded-full bg-orange-100">
                    <div class="h-2 rounded-full bg-saffron-600 transition-all" :style="`width: ${step * 20}%`"></div>
                </div>
            </div>

            <div class="px-5 sm:px-8 py-6 space-y-5">
                <div x-show="errorMessage" class="rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3" x-text="errorMessage"></div>

                <section x-show="step === 1" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">NIK <span class="text-red-600">*</span></label>
                        <input x-model="form.nik" maxlength="16" inputmode="numeric" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white" placeholder="16 digit">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('nik')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama <span class="text-red-600">*</span></label>
                        <input x-model="form.nama" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('nama')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Gelar depan</label>
                        <input x-model="form.gelar_depan" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white" placeholder="Dr., Drs., ...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Gelar belakang</label>
                        <input x-model="form.gelar_belakang" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white" placeholder="S.Pd., M.Pd., ...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Jenis kelamin <span class="text-red-600">*</span></label>
                        <select x-model="form.jenis_kelamin" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih</option>
                            @foreach ($jenisKelamin as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('jenis_kelamin')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Agama <span class="text-red-600">*</span></label>
                        <select x-model="form.agama" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih</option>
                            @foreach ($agama as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('agama')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tempat lahir <span class="text-red-600">*</span></label>
                        <input x-model="form.tempat_lahir" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('tempat_lahir')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tanggal lahir <span class="text-red-600">*</span></label>
                        <input type="date" x-model="form.tanggal_lahir" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('tanggal_lahir')"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Status perkawinan <span class="text-red-600">*</span></label>
                        <select x-model="form.status_perkawinan" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih</option>
                            @foreach ($statusPerkawinan as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('status_perkawinan')"></p>
                    </div>
                </section>

                <section x-show="step === 2" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">HP <span class="text-red-600">*</span></label>
                        <input x-model="form.hp" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('hp')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">WhatsApp <span class="text-red-600">*</span></label>
                        <input x-model="form.whatsapp" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('whatsapp')"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
                        <input type="email" x-model="form.email" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-slate-500 mt-1">Email ini dipakai untuk login dan verifikasi.</p>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('email')"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Alamat lengkap <span class="text-red-600">*</span></label>
                        <textarea x-model="form.alamat" rows="3" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white"></textarea>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('alamat')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Provinsi / PD <span class="text-red-600">*</span></label>
                        <select x-model="form.provinsi_kode" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white" @change="onProvinsiChange()">
                            <option value="">Pilih provinsi</option>
                            <template x-for="item in options.provinsi" :key="item.kode">
                                <option :value="item.kode" x-text="item.nama"></option>
                            </template>
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('provinsi_kode')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kabupaten/Kota / PC <span class="text-red-600">*</span></label>
                        <select x-model="form.kabupaten_kode" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white" @change="onKabupatenChange()">
                            <option value="">Pilih kabupaten/kota</option>
                            <template x-for="item in options.kabupaten" :key="item.kode">
                                <option :value="item.kode" x-text="item.nama"></option>
                            </template>
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('kabupaten_kode')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kecamatan <span class="text-red-600">*</span></label>
                        <select x-model="form.kecamatan_kode" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white" @change="onKecamatanChange()">
                            <option value="">Pilih kecamatan</option>
                            <template x-for="item in options.kecamatan" :key="item.kode">
                                <option :value="item.kode" x-text="item.nama"></option>
                            </template>
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('kecamatan_kode')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kelurahan <span class="text-red-600">*</span></label>
                        <select x-model="form.kelurahan_kode" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih kelurahan</option>
                            <template x-for="item in options.kelurahan" :key="item.kode">
                                <option :value="item.kode" x-text="item.nama"></option>
                            </template>
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('kelurahan_kode')"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Kode pos <span class="text-red-600">*</span></label>
                        <input x-model="form.kode_pos" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('kode_pos')"></p>
                    </div>
                </section>

                <section x-show="step === 3" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Status guru <span class="text-red-600">*</span></label>
                        <select x-model="form.status_guru" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih</option>
                            @foreach ($statusGuru as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('status_guru')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Jenjang <span class="text-red-600">*</span></label>
                        <select x-model="form.jenjang" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih</option>
                            @foreach ($jenjang as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('jenjang')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">NIP / NIPPPK <span x-show="nipRequired()" class="text-red-600">*</span></label>
                        <input x-model="form.nip" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('nip')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">NUPTK</label>
                        <input x-model="form.nuptk" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor GTK</label>
                        <input x-model="form.nomor_gtk" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Mata pelajaran</label>
                        <input x-model="form.mapel" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama sekolah <span class="text-red-600">*</span></label>
                        <input x-model="form.nama_sekolah" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('nama_sekolah')"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">NPSN</label>
                        <input x-model="form.npsn" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status sekolah <span class="text-red-600">*</span></label>
                        <select x-model="form.status_sekolah" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <option value="">Pilih</option>
                            @foreach ($statusSekolah as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('status_sekolah')"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Alamat sekolah <span class="text-red-600">*</span></label>
                        <textarea x-model="form.alamat_sekolah" rows="3" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white"></textarea>
                        <p class="text-xs text-red-600 mt-1" x-text="fieldError('alamat_sekolah')"></p>
                    </div>
                </section>

                <section x-show="step === 4" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $dokumenFields = [
                            'pas_foto' => ['label' => 'Pas foto', 'required' => true, 'image' => true],
                            'ktp' => ['label' => 'KTP', 'required' => true, 'image' => false],
                            'sk_mengajar' => ['label' => 'SK Mengajar', 'required' => true, 'image' => false],
                            'ijazah' => ['label' => 'Ijazah', 'required' => true, 'image' => false],
                            'sertifikat_pendidik' => ['label' => 'Sertifikat Pendidik', 'required' => false, 'image' => false],
                        ];
                    @endphp
                    @foreach ($dokumenFields as $field => $dokumen)
                        <div class="{{ $field === 'sertifikat_pendidik' ? 'sm:col-span-2' : '' }}">
                            <label class="block text-sm font-medium mb-1">
                                {{ $dokumen['label'] }}
                                @if ($dokumen['required'])
                                    <span class="text-red-600">*</span>
                                @else
                                    <span class="text-slate-500 font-normal">(opsional)</span>
                                @endif
                            </label>
                            <input type="file" accept="{{ $dokumen['image'] ? 'image/*' : 'image/*,.pdf' }}" class="block w-full text-sm" @change="onFile('{{ $field }}', $event)">
                            <p class="text-xs text-slate-500 mt-1" x-show="files.{{ $field }}" x-text="files.{{ $field }}?.name"></p>
                            <img x-show="isImageFile(files.{{ $field }})" :src="files.{{ $field }}?.data" alt="Pratinjau {{ strtolower($dokumen['label']) }}" class="mt-3 w-28 h-36 object-cover rounded-xl border bg-white">
                            <div x-show="isPdfFile(files.{{ $field }})" class="mt-3 w-28 h-36 rounded-xl border bg-white flex flex-col items-center justify-center text-slate-500 text-xs px-2 text-center">
                                <span class="text-2xl mb-1">PDF</span>
                                <span class="truncate w-full" x-text="files.{{ $field }}?.name"></span>
                            </div>
                            <p class="text-xs text-red-600 mt-1" x-text="fieldError('{{ $field }}')"></p>
                        </div>
                    @endforeach
                    <p class="sm:col-span-2 text-xs text-slate-500">Pas foto wajib gambar. KTP, SK, dan ijazah boleh JPG, PNG, atau PDF. Maksimal 2 MB per berkas.</p>
                </section>

                <section x-show="step === 5" x-cloak class="space-y-4">
                    <div class="rounded-2xl bg-white border border-orange-100 p-4 text-sm space-y-1">
                        <p class="font-semibold text-slate-900">Ringkasan</p>
                        <p x-text="`${form.nama} · NIK ${form.nik}`"></p>
                        <p x-text="form.email"></p>
                        <p>PD/PC mengikuti provinsi dan kabupaten/kota yang dipilih.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Kata sandi <span class="text-red-600">*</span></label>
                            <input type="password" x-model="form.password" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <p class="text-xs text-slate-500 mt-1">Tidak disimpan di perangkat. Minimal 8 karakter.</p>
                            <p class="text-xs text-red-600 mt-1" x-text="fieldError('password')"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Konfirmasi kata sandi <span class="text-red-600">*</span></label>
                            <input type="password" x-model="form.password_confirmation" class="w-full rounded-xl border-slate-300 px-4 py-2.5 bg-white">
                            <p class="text-xs text-red-600 mt-1" x-text="fieldError('password_confirmation')"></p>
                        </div>
                    </div>
                    <div x-show="verifikasi.pilihKanal" class="rounded-2xl bg-white border border-orange-100 p-4">
                        <p class="text-sm font-semibold mb-3">Verifikasi akun <span class="text-red-600">*</span></p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <label class="flex-1 flex items-center gap-3 rounded-xl border px-4 py-3 cursor-pointer" :class="form.kanal_verifikasi === 'email' ? 'border-saffron-600 bg-orange-50' : 'border-slate-200'">
                                <input type="radio" x-model="form.kanal_verifikasi" value="email">
                                <span>Email</span>
                            </label>
                            <label class="flex-1 flex items-center gap-3 rounded-xl border px-4 py-3 cursor-pointer" :class="form.kanal_verifikasi === 'whatsapp' ? 'border-saffron-600 bg-orange-50' : 'border-slate-200'">
                                <input type="radio" x-model="form.kanal_verifikasi" value="whatsapp">
                                <span>WhatsApp</span>
                            </label>
                        </div>
                        <p class="text-xs text-red-600 mt-2" x-text="fieldError('kanal_verifikasi')"></p>
                    </div>
                    <p class="text-sm text-slate-600" x-show="!verifikasi.langsung && !verifikasi.pilihKanal && verifikasi.defaultKanal === 'email'">Setelah daftar, Anda akan memverifikasi akun lewat email.</p>
                    <p class="text-sm text-slate-600" x-show="!verifikasi.langsung && !verifikasi.pilihKanal && verifikasi.defaultKanal === 'whatsapp'">Setelah daftar, kode verifikasi dikirim ke WhatsApp.</p>
                    <p class="text-sm text-slate-600" x-show="verifikasi.langsung">Akun langsung terdaftar dan menunggu verifikasi PC.</p>
                </section>
            </div>

            <div class="sticky bottom-0 bg-[#fff8f1]/95 backdrop-blur border-t border-orange-100 px-5 sm:px-8 py-4 flex flex-col sm:flex-row gap-3 sm:justify-between">
                <button type="button" class="w-full sm:w-auto px-6 py-3 rounded-full border border-slate-300 font-semibold" @click="prev()" x-show="step > 1">Kembali</button>
                <button type="button" class="w-full sm:w-auto px-8 py-3 rounded-full bg-saffron-600 hover:bg-saffron-700 text-white font-semibold" @click="next()" x-show="step < 5">Lanjut</button>
                <button type="button" class="w-full sm:w-auto px-8 py-3 rounded-full bg-saffron-600 hover:bg-saffron-700 text-white font-semibold disabled:opacity-60" @click="submit()" x-show="step === 5" :disabled="submitting" x-text="submitting ? 'Mengirim...' : 'Kirim pendaftaran'"></button>
            </div>
        </main>
    </div>
    <script src="{{ asset('js/pendaftaran-wizard.js') }}"></script>
</body>
</html>
