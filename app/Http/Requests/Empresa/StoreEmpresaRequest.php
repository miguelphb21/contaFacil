<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => preg_replace('/\D/', '', $this->cnpj ?? ''),
        ]);
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
                'size:14',
                'regex:/^\d{14}$/',
                'unique:empresas,cnpj',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidCnpj($value)) {
                        $fail('Informe um CNPJ válido.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'razao_social.required' => 'Informe a razão social.',
            'razao_social.string' => 'A razão social deve ser um texto válido.',
            'razao_social.max' => 'A razão social deve ter no máximo 255 caracteres.',

            'nome_fantasia.string' => 'O nome fantasia deve ser um texto válido.',
            'nome_fantasia.max' => 'O nome fantasia deve ter no máximo 255 caracteres.',

            'cnpj.required' => 'Informe o CNPJ.',
            'cnpj.string' => 'O CNPJ informado não é válido.',
            'cnpj.size' => 'O CNPJ deve possuir 14 números.',
            'cnpj.regex' => 'O CNPJ deve conter somente números.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
        ];
    }

    private function isValidCnpj(string $cnpj): bool
    {
        if (! preg_match('/^\d{14}$/', $cnpj)) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weightsFirst = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weightsFirst[$i];
        }

        $remainder = $sum % 11;
        $digitFirst = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $digitFirst) {
            return false;
        }

        $weightsSecond = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weightsSecond[$i];
        }

        $remainder = $sum % 11;
        $digitSecond = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $digitSecond;
    }
}
