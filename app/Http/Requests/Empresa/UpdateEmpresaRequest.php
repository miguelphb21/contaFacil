<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
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
            'razao_social' => [
                'required',
                'string',
                'max:255',
            ],

            'nome_fantasia' => [
                'nullable',
                'string',
                'max:255',
            ],

            'cnpj' => [
                'required',
                'string',
                'max:14',
                Rule::unique('empresas', 'cnpj')->ignore($this->route('empresa')),
            ],
        ];
    }
}
