<?php

namespace App\Http\Requests\Recorrencia;

use App\Enums\LancamentoTipo;
use App\Http\Requests\Concerns\AplicaEmpresaAtiva;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecorrenciaRequest extends FormRequest
{
    use AplicaEmpresaAtiva;

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

            'dia' => [
                'required',
                'integer',
                'between:1,28',
            ],

            'data_inicio' => [
                'required',
                'date',
            ],

            'data_fim' => [
                'nullable',
                'date',
                'after_or_equal:data_inicio',
            ],

            'forma_pagamento' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ativa' => [
                'boolean',
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

            'tipo.required' => 'Informe o tipo da recorrência.',

            'descricao.required' => 'Informe a descrição da recorrência.',
            'descricao.min' => 'A descrição deve possuir pelo menos 2 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',

            'valor.required' => 'Informe o valor da recorrência.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor deve ser maior que zero.',

            'dia.required' => 'Informe o dia da recorrência.',
            'dia.integer' => 'O dia deve ser um número.',
            'dia.between' => 'O dia deve estar entre 1 e 28.',

            'data_inicio.required' => 'Informe a data de início.',
            'data_inicio.date' => 'A data de início é inválida.',
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.',

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
