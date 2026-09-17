<?php

namespace Database\Factories;

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\Lancamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lancamento>
 */
class LancamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'tipo' => fake()->randomElement(LancamentoTipo::cases()),
            'descricao' => fake()->sentence(3),
            'valor' => fake()->randomFloat(2, 10, 5000),
            'data' => fake()->date(),
            'status' => fake()->randomElement(LancamentoStatus::cases()),
            'forma_pagamento' => fake()->optional()->randomElement(['pix', 'cartao_credito', 'boleto', 'dinheiro', 'transferencia']),
            'categoria_id' => null,
            'contraparte_id' => null,
            'recorrencia_id' => null,
        ];
    }

    public function receita(): static
    {
        return $this->state([
            'tipo' => LancamentoTipo::Receita,
            'contraparte_id' => Contraparte::factory()->cliente(),
        ]);
    }

    public function despesa(): static
    {
        return $this->state([
            'tipo' => LancamentoTipo::Despesa,
            'contraparte_id' => Contraparte::factory()->fornecedor(),
        ]);
    }

    public function pendente(): static
    {
        return $this->state(['status' => LancamentoStatus::Pendente]);
    }

    public function pago(): static
    {
        return $this->state(['status' => LancamentoStatus::Pago]);
    }
}
