<?php

namespace App\Modules\Documents\Requests;

use App\Modules\Documents\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'knowledge_base_id' => ['sometimes', 'uuid'],
            'status'   => ['sometimes', Rule::in([DocumentStatus::Indexed->value])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
