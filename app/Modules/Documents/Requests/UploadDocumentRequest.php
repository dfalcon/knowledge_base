<?php

namespace App\Modules\Documents\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'  => ['required', 'file', 'mimes:'.self::ALLOWED, 'max:512000'], // 500 MB
            'title' => ['sometimes', 'string', 'max:500'],
        ];
    }

    private const ALLOWED = 'pdf,doc,docx,txt,rtf,odt,md'
        .',xls,xlsx,csv,ods'
        .',ppt,pptx,odp'
        .',jpg,jpeg,png,gif,webp,svg,bmp,tiff'
        .',mp4,mov,avi,mkv,webm,wmv'
        .',mp3,wav,ogg,m4a,aac'
        .',zip';
}
