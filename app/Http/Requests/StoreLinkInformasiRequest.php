<?php

namespace App\Http\Requests;

use App\Models\LinkInformasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLinkInformasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-link-informasi') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'url' => ['required', 'string', 'url', 'max:500'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'integer', Rule::in([LinkInformasi::STATUS_NONAKTIF, LinkInformasi::STATUS_AKTIF])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'nama',
            'url' => 'URL',
            'keterangan' => 'keterangan',
            'status' => 'status',
        ];
    }

    protected function prepareForValidation(): void
    {
        $url = trim((string) $this->input('url'));

        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $this->merge([
            'url' => $url,
            'keterangan' => $this->filled('keterangan') ? trim((string) $this->input('keterangan')) : null,
        ]);
    }
}
