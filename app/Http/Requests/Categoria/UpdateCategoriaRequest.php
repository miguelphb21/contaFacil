<?php

namespace App\Http\Requests\Categoria;

use App\Enums\LancamentoTipo;
use App\Http\Requests\Concerns\AplicaEmpresaAtiva;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
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

            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'descricao' => [
                'nullable',
                'string',
                'max:500',
            ],

            'tipo' => [
                'required',
                Rule::enum(LancamentoTipo::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa_id.required' => 'Selecione uma empresa.',
            'empresa_id.integer' => 'A empresa selecionada é inválida.',
            'empresa_id.exists' => 'A empresa selecionada não existe.',

            'nome.required' => 'Informe o nome da categoria.',
            'nome.string' => 'O nome da categoria deve ser um texto.',
            'nome.min' => 'O nome da categoria deve possuir pelo menos 2 caracteres.',
            'nome.max' => 'O nome da categoria deve ter no máximo 255 caracteres.',

            'descricao.string' => 'A descrição deve ser um texto válido.',
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.',

            'tipo.required' => 'Informe o tipo da categoria.',
        ];
    }
}
