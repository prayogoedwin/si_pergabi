<?php

namespace App\Http\Requests;

class UpdateLinkInformasiRequest extends StoreLinkInformasiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-link-informasi') === true;
    }
}
