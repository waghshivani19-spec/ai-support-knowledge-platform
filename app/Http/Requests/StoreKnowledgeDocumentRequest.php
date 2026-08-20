<?php

namespace App\Http\Requests;

use App\Models\KnowledgeBase;
use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $knowledgeBase = $this->route('knowledge_base');

        return $this->user()?->can(
            'view',
            $knowledgeBase
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,docx,txt,csv',
                'max:20480',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' =>
                'Please select a document.',

            'file.mimes' =>
                'Only PDF, DOCX, TXT and CSV files are supported.',

            'file.max' =>
                'The document cannot be larger than 20 MB.',
        ];
    }
}