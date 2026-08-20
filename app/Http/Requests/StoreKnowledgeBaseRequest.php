<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\KnowledgeBase::class)
            ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'embedding_provider' => [
                'nullable',
                'string',
                'max:100',
            ],

            'embedding_model' => [
                'nullable',
                'string',
                'max:150',
            ],

            'chunk_size' => [
                'nullable',
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
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Knowledge base name is required.',

            'chunk_size.min' =>
                'Chunk size must be at least 100.',

            'chunk_overlap.min' =>
                'Chunk overlap cannot be negative.',
        ];
    }
}