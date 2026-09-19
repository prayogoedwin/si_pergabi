<?php

namespace App\Http\Requests;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePendaftaranSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-pendaftaran') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pendaftaran_tipe' => ['required', Rule::in([SettingService::TIPE_LANGSUNG, SettingService::TIPE_VERIFIKASI])],
            'email_aktif' => ['nullable', 'boolean'],
            'whatsapp_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->string('pendaftaran_tipe')->toString() !== SettingService::TIPE_VERIFIKASI) {
                return;
            }

            $email = $this->boolean('email_aktif');
            $whatsapp = $this->boolean('whatsapp_aktif');

            if (! $email && ! $whatsapp) {
                $validator->errors()->add('email_aktif', 'Aktifkan email atau WhatsApp. Minimal satu kanal verifikasi.');
            }

            if ($whatsapp && ! app(SettingService::class)->fonnteToken()) {
                $validator->errors()->add('whatsapp_aktif', 'Atur token Fonnte di menu Integrasi sebelum mengaktifkan WhatsApp.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pendaftaran_tipe' => 'tipe pendaftaran',
            'email_aktif' => 'verifikasi email',
            'whatsapp_aktif' => 'verifikasi WhatsApp',
        ];
    }
}
