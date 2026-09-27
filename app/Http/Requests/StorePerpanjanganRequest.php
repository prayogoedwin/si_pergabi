<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;

class StorePerpanjanganRequest extends StorePendaftaranRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $anggota = $user?->anggota;

        return $user !== null
            && ! $user->isPengurus()
            && $anggota !== null
            && $anggota->canRenew();
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

        $rules['nik'] = ['required', 'digits:16', Rule::unique('anggota', 'nik')->ignore($anggotaId)];
        $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)];

        return $rules;
    }
}
