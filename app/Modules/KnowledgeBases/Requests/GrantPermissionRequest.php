<?php

namespace App\Modules\KnowledgeBases\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GrantPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'   => ['required', 'uuid', 'exists:users,id'],
            'can_read'  => ['sometimes', 'boolean'],
            'can_write' => ['sometimes', 'boolean'],
        ];
    }
}
