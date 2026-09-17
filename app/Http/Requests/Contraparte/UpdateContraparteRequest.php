<?php

namespace App\Http\Requests\Contraparte;

use App\Enums\ContraparteTipo;
use App\Http\Requests\Concerns\AplicaEmpresaAtiva;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContraparteRequest extends FormRequest
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
                Rule::enum(ContraparteTipo::class),
            ],

            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'cpf_cnpj' => [
                'nullable',
                'string',
                'max:20',
            ],

            'telefone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'endereco' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa_id.required' => 'Selecione uma empresa.',
            'empresa_id.exists' => 'A empresa selecionada não existe.',

            'tipo.required' => 'Informe o tipo da contraparte.',

            'nome.required' => 'Informe o nome da contraparte.',
            'nome.min' => 'O nome deve possuir pelo menos 2 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 255 caracteres.',
        ];
    }
}
