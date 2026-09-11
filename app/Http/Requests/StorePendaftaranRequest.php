<?php

namespace App\Http\Requests;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StorePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $nipRequired = in_array($this->input('status_guru'), ['ASN', 'PPPK'], true);

        return [
            'nik' => ['required', 'digits:16', 'unique:anggota,nik'],
            'gelar_depan' => ['nullable', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:150'],
            'gelar_belakang' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', Rule::in(array_keys(Anggota::jenisKelaminOptions()))],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'agama' => ['required', Rule::in(array_keys(Anggota::agamaOptions()))],
            'status_perkawinan' => ['required', Rule::in(array_keys(Anggota::statusPerkawinanOptions()))],
            'hp' => ['required', 'string', 'max:20'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'alamat' => ['required', 'string', 'max:500'],
            'provinsi_kode' => ['required', 'exists:wilayah,kode'],
            'kabupaten_kode' => ['required', 'exists:wilayah,kode'],
            'kecamatan_kode' => ['required', 'exists:wilayah,kode'],
            'kelurahan_kode' => ['required', 'exists:wilayah,kode'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'status_guru' => ['required', Rule::in(array_keys(Anggota::statusGuruOptions()))],
            'nip' => [$nipRequired ? 'required' : 'nullable', 'string', 'max:30'],
            'nuptk' => ['nullable', 'string', 'max:30'],
            'nomor_gtk' => ['nullable', 'string', 'max:30'],
            'mapel' => ['nullable', 'string', 'max:100'],
            'jenjang' => ['required', Rule::in(array_keys(Anggota::jenjangOptions()))],
            'nama_sekolah' => ['required', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'status_sekolah' => ['required', Rule::in(array_keys(Anggota::statusSekolahOptions()))],
            'alamat_sekolah' => ['required', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'pas_foto' => ['required', 'file', 'image', 'max:2048'],
            'ktp' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'sk_mengajar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'ijazah' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'sertifikat_pendidik' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'kanal_verifikasi' => ['nullable', 'in:email,whatsapp'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->assertWilayahHierarchy($validator, 'kabupaten_kode', $this->string('provinsi_kode')->toString(), Wilayah::TINGKAT_KABUPATEN);
            $this->assertWilayahHierarchy($validator, 'kecamatan_kode', $this->string('kabupaten_kode')->toString(), Wilayah::TINGKAT_KECAMATAN);
            $this->assertWilayahHierarchy($validator, 'kelurahan_kode', $this->string('kecamatan_kode')->toString(), Wilayah::TINGKAT_KELURAHAN);
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'nama' => 'nama',
            'jenis_kelamin' => 'jenis kelamin',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'status_perkawinan' => 'status perkawinan',
            'provinsi_kode' => 'provinsi',
            'kabupaten_kode' => 'kabupaten/kota',
            'kecamatan_kode' => 'kecamatan',
            'kelurahan_kode' => 'kelurahan',
            'kode_pos' => 'kode pos',
            'status_guru' => 'status guru',
            'nama_sekolah' => 'nama sekolah',
            'status_sekolah' => 'status sekolah',
            'alamat_sekolah' => 'alamat sekolah',
            'pas_foto' => 'pas foto',
            'ktp' => 'KTP',
            'sk_mengajar' => 'SK Mengajar',
            'ijazah' => 'ijazah',
            'sertifikat_pendidik' => 'sertifikat pendidik',
            'kanal_verifikasi' => 'kanal verifikasi',
        ];
    }

    /**
     * @return array<string, UploadedFile>
     */
    public function dokumenUploads(): array
    {
        $files = [];

        foreach ([
            AnggotaDokumen::PAS_FOTO => 'pas_foto',
            AnggotaDokumen::KTP => 'ktp',
            AnggotaDokumen::SK_MENGAJAR => 'sk_mengajar',
            AnggotaDokumen::IJAZAH => 'ijazah',
            AnggotaDokumen::SERTIFIKAT_PENDIDIK => 'sertifikat_pendidik',
        ] as $jenis => $field) {
            $file = $this->file($field);

            if ($file) {
                $files[$jenis] = $file;
            }
        }

        return $files;
    }

    private function assertWilayahHierarchy(Validator $validator, string $field, string $parent, int $tingkat): void
    {
        $kode = $this->string($field)->toString();

        if ($kode === '' || $parent === '') {
            return;
        }

        $wilayah = Wilayah::query()->find($kode);

        if ($wilayah === null) {
            return;
        }

        if ($wilayah->tingkat() !== $tingkat || ! str_starts_with($kode, $parent.'.')) {
            $validator->errors()->add($field, 'Wilayah tidak sesuai hierarki.');
        }
    }
}
