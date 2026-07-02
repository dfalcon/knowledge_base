<?php

namespace App\Modules\Documents\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncDocumentTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tags'   => ['present', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }
}
