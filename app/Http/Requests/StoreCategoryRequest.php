<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->where('user_id', $this->user()->id),
            ],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon'  => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Você já tem uma categoria com esse nome.',
            'color.regex' => 'A cor deve ser um código hexadecimal válido (ex: #3b82f6).',
        ];
    }
}
