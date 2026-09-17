<?php

namespace Database\Factories;

use App\Enums\LancamentoTipo;
use App\Models\Empresa;
use App\Models\Recorrencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recorrencia>
 */
class RecorrenciaFactory extends Factory
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
            'dia' => fake()->numberBetween(1, 28),
            'data_inicio' => fake()->dateTimeThisYear()->format('Y-m-d'),
            'data_fim' => null,
            'forma_pagamento' => fake()->optional()->randomElement(['pix', 'cartao_credito', 'boleto', 'dinheiro', 'transferencia']),
            'ativa' => true,
            'categoria_id' => null,
            'contraparte_id' => null,
        ];
    }

    public function despesa(): static
    {
        return $this->state(['tipo' => LancamentoTipo::Despesa]);
    }

    public function receita(): static
    {
        return $this->state(['tipo' => LancamentoTipo::Receita]);
    }
}
