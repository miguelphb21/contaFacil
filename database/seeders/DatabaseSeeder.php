<?php

namespace Database\Seeders;

use App\Enums\LancamentoTipo;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\Recorrencia;
use App\Models\User;
use App\Services\RecorrenciaService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Usuário Demonstração',
            'email' => 'test@example.com',
        ]);

        $empresa = Empresa::factory()->create([
            'razao_social' => 'Comércio de Exemplo LTDA',
            'nome_fantasia' => 'Conta Fácil Demo',
        ]);

        $empresa->usuarios()->attach($user);

        /** @var array<string, array<string, Categoria>> $categorias */
        $categorias = [
            'despesa' => [],
            'receita' => [],
        ];

        foreach (['Aluguel', 'Salários', 'Impostos', 'Mercado', 'Transporte', 'Energia'] as $nome) {
            $categorias['despesa'][$nome] = Categoria::factory()->despesa()->create([
                'empresa_id' => $empresa->getKey(),
                'nome' => $nome,
            ]);
        }

        foreach (['Vendas', 'Serviços', 'Investimentos'] as $nome) {
            $categorias['receita'][$nome] = Categoria::factory()->receita()->create([
                'empresa_id' => $empresa->getKey(),
                'nome' => $nome,
            ]);
        }

        $fornecedores = collect(['Comercial Energia SA', 'Mercado Bom Preço', 'Imobiliária Vista', 'Transportadora Rápida'])
            ->map(fn (string $nome) => Contraparte::factory()->fornecedor()->create([
                'empresa_id' => $empresa->getKey(),
                'nome' => $nome,
            ]));

        $clientes = collect(['Loja do João', 'Padaria Central', 'Restaurante Sabor'])
            ->map(fn (string $nome) => Contraparte::factory()->cliente()->create([
                'empresa_id' => $empresa->getKey(),
                'nome' => $nome,
            ]));

        $recorrencias = [
            [
                'tipo' => LancamentoTipo::Despesa,
                'descricao' => 'Aluguel do escritório',
                'valor' => 2500.00,
                'dia' => 5,
                'categoria' => 'Aluguel',
                'contraparte' => 'Imobiliária Vista',
                'forma_pagamento' => 'transferencia',
            ],
            [
                'tipo' => LancamentoTipo::Despesa,
                'descricao' => 'Folha de pagamento',
                'valor' => 9800.00,
                'dia' => 28,
                'categoria' => 'Salários',
                'contraparte' => null,
                'forma_pagamento' => 'transferencia',
            ],
            [
                'tipo' => LancamentoTipo::Despesa,
                'descricao' => 'Energia elétrica',
                'valor' => 380.00,
                'dia' => 12,
                'categoria' => 'Energia',
                'contraparte' => 'Comercial Energia SA',
                'forma_pagamento' => 'boleto',
            ],
            [
                'tipo' => LancamentoTipo::Despesa,
                'descricao' => 'Compra de suprimentos',
                'valor' => 1200.00,
                'dia' => 15,
                'categoria' => 'Mercado',
                'contraparte' => 'Mercado Bom Preço',
                'forma_pagamento' => 'pix',
            ],
            [
                'tipo' => LancamentoTipo::Receita,
                'descricao' => 'Vendas via PIX',
                'valor' => 12000.00,
                'dia' => 10,
                'categoria' => 'Vendas',
                'contraparte' => null,
                'forma_pagamento' => 'pix',
            ],
            [
                'tipo' => LancamentoTipo::Receita,
                'descricao' => 'Serviços prestados',
                'valor' => 3500.00,
                'dia' => 20,
                'categoria' => 'Serviços',
                'contraparte' => null,
                'forma_pagamento' => 'transferencia',
            ],
        ];

        foreach ($recorrencias as $dados) {
            $contraparte = $dados['contraparte'] !== null
                ? $fornecedores->firstWhere('nome', $dados['contraparte'])->getKey()
                : null;

            $recorrencia = Recorrencia::factory()->create([
                'empresa_id' => $empresa->getKey(),
                'tipo' => $dados['tipo'],
                'descricao' => $dados['descricao'],
                'valor' => $dados['valor'],
                'dia' => $dados['dia'],
                'data_inicio' => now()->subMonths(5)->startOfMonth()->format('Y-m-d'),
                'categoria_id' => $categorias[$dados['tipo']->value][$dados['categoria']]->getKey(),
                'contraparte_id' => $contraparte,
                'forma_pagamento' => $dados['forma_pagamento'],
            ]);

            app(RecorrenciaService::class)->gerar(
                $recorrencia,
                CarbonImmutable::now()->endOfMonth()
            );
        }

        Lancamento::factory()->despesa()->pendente()->create([
            'empresa_id' => $empresa->getKey(),
            'descricao' => 'Manutenção do veículo de entrega',
            'valor' => 850.00,
            'data' => now()->addDays(6)->startOfDay()->format('Y-m-d'),
            'categoria_id' => $categorias['despesa']['Transporte']->getKey(),
            'contraparte_id' => $fornecedores->firstWhere('nome', 'Transportadora Rápida')->getKey(),
            'forma_pagamento' => null,
        ]);

        Lancamento::factory()->receita()->pago()->create([
            'empresa_id' => $empresa->getKey(),
            'descricao' => 'Venda especial para Loja do João',
            'valor' => 2800.00,
            'data' => now()->subDays(4)->startOfDay()->format('Y-m-d'),
            'categoria_id' => $categorias['receita']['Vendas']->getKey(),
            'contraparte_id' => $clientes->firstWhere('nome', 'Loja do João')->getKey(),
            'forma_pagamento' => 'pix',
        ]);
    }
}
