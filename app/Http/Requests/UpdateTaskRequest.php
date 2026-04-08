<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority'    => ['required', 'in:low,medium,high'],
            'status'      => ['required', 'in:pending,completed'],
            'due_date'    => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->input('category_id');
            if ($categoryId) {
                $belongs = $this->user()->categories()
                    ->where('id', $categoryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add('category_id', 'Categoria inválida.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'O título é obrigatório.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists'   => 'Categoria inválida.',
            'priority.in'          => 'Prioridade inválida.',
            'status.in'            => 'Status inválido.',
        ];
    }
}
