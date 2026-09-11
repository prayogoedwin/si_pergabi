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
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'mail_mailer' => ['required', Rule::in(['smtp', 'log', 'sendmail'])],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_scheme' => ['nullable', 'in:tls,smtps'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
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

            if ($whatsapp && blank($this->input('fonnte_token')) && ! app(SettingService::class)->fonnteToken()) {
                $validator->errors()->add('fonnte_token', 'Token Fonnte wajib diisi jika WhatsApp aktif.');
            }

            if ($email && blank($this->input('mail_from_address'))) {
                $validator->errors()->add('mail_from_address', 'Alamat pengirim email wajib diisi jika verifikasi email aktif.');
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
            'fonnte_token' => 'token Fonnte',
            'mail_from_address' => 'email pengirim',
        ];
    }
}
