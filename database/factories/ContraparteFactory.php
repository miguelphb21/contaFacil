<?php

namespace Database\Factories;

use App\Enums\ContraparteTipo;
use App\Models\Contraparte;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contraparte>
 */
class ContraparteFactory extends Factory
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
            'tipo' => fake()->randomElement(ContraparteTipo::cases()),
            'nome' => fake()->name(),
            'cpf_cnpj' => fake()->optional()->numerify('##############'),
            'telefone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'endereco' => fake()->optional()->address(),
        ];
    }

    public function fornecedor(): static
    {
        return $this->state(['tipo' => ContraparteTipo::Fornecedor]);
    }

    public function cliente(): static
    {
        return $this->state(['tipo' => ContraparteTipo::Cliente]);
    }
}
