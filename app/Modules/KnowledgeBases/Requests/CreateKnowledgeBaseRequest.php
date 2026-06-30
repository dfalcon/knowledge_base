<?php

namespace App\Modules\KnowledgeBases\Requests;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (KnowledgeBase::where('slug', Str::slug($value))->exists()) {
                    $fail('A knowledge base with a similar name already exists.');
                }
            }],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
