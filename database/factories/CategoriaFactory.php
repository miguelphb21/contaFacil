<?php

namespace Database\Factories;

use App\Enums\LancamentoTipo;
use App\Models\Categoria;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
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
            'nome' => fake()->word(),
            'descricao' => fake()->optional()->sentence(),
            'tipo' => fake()->randomElement(LancamentoTipo::cases()),
        ];
    }

    public function receita(): static
    {
        return $this->state(['tipo' => LancamentoTipo::Receita]);
    }

    public function despesa(): static
    {
        return $this->state(['tipo' => LancamentoTipo::Despesa]);
    }
}
