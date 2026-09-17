<?php

namespace App\Http\Requests\Lancamento;

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLancamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'empresa_id' => (int) session('empresa_ativa_id'),
            'tipo' => str_starts_with($this->route()->getName(), 'receitas')
                ? LancamentoTipo::Receita->value
                : LancamentoTipo::Despesa->value,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'empresa_id' => [
                'required',
                'integer',
                'exists:empresas,id',
            ],

            'tipo' => [
                'required',
                Rule::enum(LancamentoTipo::class),
            ],

            'descricao' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'valor' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999999.99',
            ],

            'data' => [
                'required',
                'date',
            ],

            'status' => [
                'sometimes',
                Rule::enum(LancamentoStatus::class),
            ],

            'forma_pagamento' => [
                'nullable',
                'string',
                'max:50',
            ],

            'categoria_id' => [
                'nullable',
                'integer',
                Rule::exists('categorias', 'id')
                    ->where(fn ($query) => $query
                        ->where('empresa_id', $this->empresa_id)
                        ->where('tipo', $this->tipo)),
            ],

            'contraparte_id' => [
                'nullable',
                'integer',
                Rule::exists('contrapartes', 'id')
                    ->where(fn ($query) => $query
                        ->where('empresa_id', $this->empresa_id)
                        ->where('tipo', $this->contraparteTipoEsperado())),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa_id.required' => 'Selecione uma empresa.',
            'empresa_id.exists' => 'A empresa selecionada não existe.',

            'tipo.required' => 'Informe o tipo do lançamento.',

            'descricao.required' => 'Informe a descrição do lançamento.',
            'descricao.min' => 'A descrição deve possuir pelo menos 2 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',

            'valor.required' => 'Informe o valor do lançamento.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor deve ser maior que zero.',

            'data.required' => 'Informe a data do lançamento.',
            'data.date' => 'A data informada é inválida.',

            'categoria_id.exists' => 'A categoria selecionada é inválida.',
            'contraparte_id.exists' => 'A contraparte selecionada é inválida.',
        ];
    }

    private function contraparteTipoEsperado(): string
    {
        return $this->tipo === LancamentoTipo::Despesa->value
            ? 'fornecedor'
            : 'cliente';
    }
}
