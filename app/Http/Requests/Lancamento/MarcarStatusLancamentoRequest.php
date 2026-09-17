<?php

namespace App\Http\Requests\Lancamento;

use App\Enums\LancamentoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarcarStatusLancamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(LancamentoStatus::class),
            ],

            'forma_pagamento' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Informe o novo status do lançamento.',
        ];
    }
}
