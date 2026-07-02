<?php

namespace App\Modules\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'knowledge_base_id' => ['nullable', 'uuid', 'exists:knowledge_bases,id'],
            'title'             => ['nullable', 'string', 'max:500'],
        ];
    }
}
