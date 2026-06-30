<?php

namespace App\Modules\KnowledgeBases\Requests;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $knowledgeBase = $this->route('knowledgeBase');

        return [
            'name' => ['sometimes', 'string', 'max:255', function ($attribute, $value, $fail) use ($knowledgeBase) {
                if (KnowledgeBase::where('slug', Str::slug($value))->where('id', '!=', $knowledgeBase->id)->exists()) {
                    $fail('A knowledge base with a similar slug already exists.');
                }
            }],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
