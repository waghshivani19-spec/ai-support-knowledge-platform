<?php

namespace App\Http\Requests;

use App\Models\KnowledgeBase;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $knowledgeBase = $this->route('knowledge_base');

        return $this->user()?->can(
            'update',
            $knowledgeBase
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],

            'embedding_provider' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'embedding_model' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'chunk_size' => [
                'sometimes',
                'integer',
                'min:100',
                'max:10000',
            ],

            'chunk_overlap' => [
                'sometimes',
                'integer',
                'min:0',
                'max:5000',
                'lt:chunk_size',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}