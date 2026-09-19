<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestIntegrasiSmtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-integrasi') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email tujuan uji',
        ];
    }
}
