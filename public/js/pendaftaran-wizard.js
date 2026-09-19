window.PergabiPendaftaran = function (config) {
    const STORAGE_KEY = 'pergabi.pendaftaran.v1';

    return {
        step: 1,
        saving: false,
        submitting: false,
        savedAt: null,
        errors: {},
        errorMessage: '',
        form: {
            nik: '',
            gelar_depan: '',
            nama: '',
            gelar_belakang: '',
            jenis_kelamin: '',
            tempat_lahir: '',
            tanggal_lahir: '',
            agama: '',
            status_perkawinan: '',
            hp: '',
            whatsapp: '',
            email: '',
            alamat: '',
            provinsi_kode: '',
            kabupaten_kode: '',
            kecamatan_kode: '',
            kelurahan_kode: '',
            kode_pos: '',
            status_guru: '',
            nip: '',
            nuptk: '',
            nomor_gtk: '',
            mapel: '',
            jenjang: '',
            nama_sekolah: '',
            npsn: '',
            status_sekolah: '',
            alamat_sekolah: '',
            password: '',
            password_confirmation: '',
            kanal_verifikasi: '',
        },
        files: {
            pas_foto: null,
            ktp: null,
            sk_mengajar: null,
            ijazah: null,
            sertifikat_pendidik: null,
        },
        options: {
            provinsi: [],
            kabupaten: [],
            kecamatan: [],
            kelurahan: [],
        },
        verifikasi: config.verifikasi || { langsung: false, pilihKanal: false, defaultKanal: 'email' },

        async init() {
            this.restore();
            if (config.email) {
                this.form.email = config.email;
            }
            if (!this.form.kanal_verifikasi) {
                this.form.kanal_verifikasi = this.verifikasi.defaultKanal || '';
            }
            await this.loadWilayah('provinsi', null, this.form.provinsi_kode);
            if (this.form.provinsi_kode) {
                await this.loadWilayah('kabupaten', this.form.provinsi_kode, this.form.kabupaten_kode);
            }
            if (this.form.kabupaten_kode) {
                await this.loadWilayah('kecamatan', this.form.kabupaten_kode, this.form.kecamatan_kode);
            }
            if (this.form.kecamatan_kode) {
                await this.loadWilayah('kelurahan', this.form.kecamatan_kode, this.form.kelurahan_kode);
            }
        },

        persist() {
            const payload = {
                step: this.step,
                form: { ...this.form, password: '', password_confirmation: '' },
                files: this.files,
                savedAt: new Date().toISOString(),
            };

            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                this.savedAt = payload.savedAt;
            } catch (error) {
                const withoutFiles = { ...payload, files: {} };
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(withoutFiles));
                    this.savedAt = payload.savedAt;
                    this.errorMessage = 'Draf tersimpan, tetapi dokumen terlalu besar untuk disimpan di perangkat. Unggah ulang dokumen jika Anda menutup halaman.';
                } catch (inner) {
                    this.errorMessage = 'Tidak cukup ruang untuk menyimpan draf di perangkat ini.';
                }
            }
        },

        restore() {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return;
            }

            try {
                const payload = JSON.parse(raw);
                this.step = payload.step || 1;
                Object.assign(this.form, payload.form || {});
                this.form.password = '';
                this.form.password_confirmation = '';
                this.files = { ...this.files, ...(payload.files || {}) };
                this.savedAt = payload.savedAt || null;
            } catch (error) {
                localStorage.removeItem(STORAGE_KEY);
            }
        },

        clearDraft() {
            localStorage.removeItem(STORAGE_KEY);
        },

        nipRequired() {
            return this.form.status_guru === 'ASN' || this.form.status_guru === 'PPPK';
        },

        async onProvinsiChange() {
            this.form.kabupaten_kode = '';
            this.form.kecamatan_kode = '';
            this.form.kelurahan_kode = '';
            this.options.kabupaten = [];
            this.options.kecamatan = [];
            this.options.kelurahan = [];
            this.persist();
            if (this.form.provinsi_kode) {
                await this.loadWilayah('kabupaten', this.form.provinsi_kode);
            }
        },

        async onKabupatenChange() {
            this.form.kecamatan_kode = '';
            this.form.kelurahan_kode = '';
            this.options.kecamatan = [];
            this.options.kelurahan = [];
            this.persist();
            if (this.form.kabupaten_kode) {
                await this.loadWilayah('kecamatan', this.form.kabupaten_kode);
            }
        },

        async onKecamatanChange() {
            this.form.kelurahan_kode = '';
            this.options.kelurahan = [];
            this.persist();
            if (this.form.kecamatan_kode) {
                await this.loadWilayah('kelurahan', this.form.kecamatan_kode);
            }
        },

        async loadWilayah(target, parent, selected) {
            const url = new URL(config.wilayahUrl, window.location.origin);
            if (parent) {
                url.searchParams.set('parent', parent);
            }

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });
            this.options[target] = await response.json();

            if (selected && !this.options[target].some((item) => item.kode === selected)) {
                this.form[target === 'provinsi' ? 'provinsi_kode' : target === 'kabupaten' ? 'kabupaten_kode' : target === 'kecamatan' ? 'kecamatan_kode' : 'kelurahan_kode'] = '';
            }
        },

        async onFile(jenis, event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                this.errors[jenis] = 'Ukuran berkas maksimal 2 MB.';
                event.target.value = '';
                return;
            }

            const stored = await this.fileToStored(file);
            this.files[jenis] = stored;
            delete this.errors[jenis];
            this.persist();
        },

        async fileToStored(file) {
            const isImage = file.type.startsWith('image/');
            const dataUrl = isImage ? await this.compressImage(file) : await this.readFile(file);

            return {
                name: file.name,
                type: file.type || 'application/octet-stream',
                data: dataUrl,
            };
        },

        readFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        },

        compressImage(file) {
            return new Promise((resolve, reject) => {
                const image = new Image();
                const url = URL.createObjectURL(file);
                image.onload = () => {
                    const max = 1200;
                    const scale = Math.min(1, max / Math.max(image.width, image.height));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(image.width * scale);
                    canvas.height = Math.round(image.height * scale);
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
                    URL.revokeObjectURL(url);
                    resolve(canvas.toDataURL('image/jpeg', 0.78));
                };
                image.onerror = reject;
                image.src = url;
            });
        },

        dataUrlToFile(stored, field) {
            if (!stored?.data) {
                return null;
            }
            const [meta, content] = stored.data.split(',');
            const mime = (meta.match(/:(.*?);/) || [])[1] || stored.type;
            const binary = atob(content);
            const bytes = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i += 1) {
                bytes[i] = binary.charCodeAt(i);
            }
            return new File([bytes], stored.name || `${field}.jpg`, { type: mime });
        },

        isImageFile(stored) {
            if (!stored) {
                return false;
            }

            return String(stored.type || '').startsWith('image/') || String(stored.data || '').startsWith('data:image/');
        },

        isPdfFile(stored) {
            if (!stored || this.isImageFile(stored)) {
                return false;
            }

            return String(stored.type || '') === 'application/pdf'
                || String(stored.name || '').toLowerCase().endsWith('.pdf')
                || String(stored.data || '').startsWith('data:application/pdf');
        },

        fieldError(name) {
            const messages = this.errors[name];
            if (!messages) {
                return '';
            }
            return Array.isArray(messages) ? messages[0] : messages;
        },

        validateStep() {
            this.errors = {};
            const required = {
                1: ['nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status_perkawinan'],
                2: ['hp', 'whatsapp', 'email', 'alamat', 'provinsi_kode', 'kabupaten_kode', 'kecamatan_kode', 'kelurahan_kode', 'kode_pos'],
                3: ['status_guru', 'jenjang', 'nama_sekolah', 'status_sekolah', 'alamat_sekolah'],
                4: [],
                5: ['password', 'password_confirmation'],
            }[this.step];

            required.forEach((name) => {
                if (!String(this.form[name] || '').trim()) {
                    this.errors[name] = 'Wajib diisi.';
                }
            });

            if (this.step === 1) {
                if (!/^\d{16}$/.test(this.form.nik)) {
                    this.errors.nik = 'NIK harus 16 digit.';
                }
            }

            if (this.step === 2 && this.form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.errors.email = 'Format email tidak valid.';
            }

            if (this.step === 3 && this.nipRequired() && !this.form.nip) {
                this.errors.nip = 'NIP wajib untuk ASN/PPPK.';
            }

            if (this.step === 4) {
                ['pas_foto', 'ktp', 'sk_mengajar', 'ijazah'].forEach((jenis) => {
                    if (!this.files[jenis]) {
                        this.errors[jenis] = 'Berkas wajib diunggah.';
                    }
                });
            }

            if (this.step === 5) {
                if (this.form.password.length < 8) {
                    this.errors.password = 'Kata sandi minimal 8 karakter.';
                }
                if (this.form.password !== this.form.password_confirmation) {
                    this.errors.password_confirmation = 'Konfirmasi kata sandi tidak sama.';
                }
                if (!this.verifikasi.langsung && this.verifikasi.pilihKanal && !this.form.kanal_verifikasi) {
                    this.errors.kanal_verifikasi = 'Pilih verifikasi email atau WhatsApp.';
                }
            }

            return Object.keys(this.errors).length === 0;
        },

        next() {
            if (!this.validateStep()) {
                return;
            }
            this.step = Math.min(5, this.step + 1);
            this.persist();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        prev() {
            this.step = Math.max(1, this.step - 1);
            this.persist();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        go(step) {
            if (step < this.step) {
                this.step = step;
                this.persist();
            }
        },

        wilayahName(list, kode) {
            return (this.options[list] || []).find((item) => item.kode === kode)?.nama || kode;
        },

        stepHasError(step) {
            const map = {
                1: ['nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status_perkawinan'],
                2: ['hp', 'whatsapp', 'email', 'alamat', 'provinsi_kode', 'kabupaten_kode', 'kecamatan_kode', 'kelurahan_kode', 'kode_pos'],
                3: ['status_guru', 'nip', 'jenjang', 'nama_sekolah', 'status_sekolah', 'alamat_sekolah'],
                4: ['pas_foto', 'ktp', 'sk_mengajar', 'ijazah', 'sertifikat_pendidik'],
                5: ['password', 'password_confirmation', 'kanal_verifikasi'],
            };

            return (map[step] || []).some((name) => this.errors[name]);
        },

        async submit() {
            if (!this.validateStep()) {
                return;
            }

            this.submitting = true;
            this.errorMessage = '';
            this.errors = {};

            if (this.verifikasi.langsung) {
                this.form.kanal_verifikasi = '';
            } else if (!this.verifikasi.pilihKanal) {
                this.form.kanal_verifikasi = this.verifikasi.defaultKanal || '';
            }

            const body = new FormData();
            Object.entries(this.form).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    body.append(key, value);
                }
            });

            Object.entries(this.files).forEach(([key, stored]) => {
                const file = this.dataUrlToFile(stored, key);
                if (file) {
                    body.append(key, file);
                }
            });

            try {
                const response = await fetch(config.storeUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body,
                });

                const payload = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    this.errors = payload.errors || {};
                    this.errorMessage = payload.message || 'Periksa kembali isian Anda.';
                    this.jumpToFirstError();
                    return;
                }

                if (!response.ok) {
                    this.errorMessage = payload.message || 'Pendaftaran gagal. Coba lagi.';
                    return;
                }

                this.clearDraft();
                window.location.href = payload.redirect || '/verify-email';
            } catch (error) {
                this.errorMessage = 'Tidak dapat terhubung ke server. Draf Anda tetap tersimpan di perangkat ini.';
            } finally {
                this.submitting = false;
            }
        },

        jumpToFirstError() {
            for (let step = 1; step <= 5; step += 1) {
                if (this.stepHasError(step)) {
                    this.step = step;
                    break;
                }
            }
        },
    };
};
