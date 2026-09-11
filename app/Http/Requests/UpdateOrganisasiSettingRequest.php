<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisasiSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'singkatan' => ['required', 'string', 'max:50'],
            'alamat' => ['required', 'string', 'max:500'],
            'nama_ketua_umum' => ['required', 'string', 'max:255'],
            'nama_sekretaris_jenderal' => ['required', 'string', 'max:255'],
            'visi' => ['required', 'string', 'max:1000'],
            'misi' => ['required', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'stempel' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'ttd_ketua_umum' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'ttd_sekretaris_jenderal' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'logo_hapus' => ['nullable', 'boolean'],
            'stempel_hapus' => ['nullable', 'boolean'],
            'ttd_ketua_umum_hapus' => ['nullable', 'boolean'],
            'ttd_sekretaris_jenderal_hapus' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'singkatan' => 'singkatan',
            'alamat' => 'alamat',
            'nama_ketua_umum' => 'nama Ketua Umum',
            'nama_sekretaris_jenderal' => 'nama Sekretaris Jenderal',
            'visi' => 'visi',
            'misi' => 'misi',
            'logo' => 'logo',
            'stempel' => 'stempel',
            'ttd_ketua_umum' => 'tanda tangan Ketua Umum',
            'ttd_sekretaris_jenderal' => 'tanda tangan Sekretaris Jenderal',
        ];
    }
}
