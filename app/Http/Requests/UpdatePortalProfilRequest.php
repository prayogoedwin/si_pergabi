<?php

namespace App\Http\Requests;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdatePortalProfilRequest extends StorePendaftaranRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ! $user->isPengurus()
            && $user->anggota !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['password'], $rules['kanal_verifikasi']);

        $anggotaId = $this->user()?->anggota?->id;
        $userId = $this->user()?->id;

        $jenjang = array_keys(Anggota::jenjangOptions());
        $currentJenjang = $this->user()?->anggota?->jenjang;

        if (filled($currentJenjang) && ! in_array($currentJenjang, $jenjang, true)) {
            $jenjang[] = $currentJenjang;
        }

        $statusGuru = array_keys(Anggota::statusGuruOptions());
        $currentStatusGuru = $this->user()?->anggota?->status_guru;

        if (filled($currentStatusGuru) && ! in_array($currentStatusGuru, $statusGuru, true)) {
            $statusGuru[] = $currentStatusGuru;
        }

        $rules['nik'] = ['required', 'digits:16', Rule::unique('anggota', 'nik')->ignore($anggotaId)];
        $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)];
        $rules['jenjang'] = ['required', Rule::in($jenjang)];
        $rules['status_guru'] = ['required', Rule::in($statusGuru)];
        $rules['pas_foto'] = ['nullable', 'file', 'image', 'max:2048'];
        $rules['sk_mengajar'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        $rules['bukti_pembayaran'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];

        return $rules;
    }
}
