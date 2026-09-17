<?php

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\Recorrencia;
use App\Models\User;
use App\Services\RecorrenciaService;

afterEach(function () {
    $this->travelBack();
});

it('cria uma receita com o payload enviado pelo formulário, incluindo status e forma de pagamento', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'descricao' => 'Venda à vista',
            'valor' => '320.75',
            'data' => '2026-01-10',
            'status' => 'pendente',
            'forma_pagamento' => null,
            'categoria_id' => null,
            'contraparte_id' => null,
        ])
        ->assertRedirect(route('receitas.index'))
        ->assertSessionHas('success', 'Receita registrada com sucesso.');

    $this->assertDatabaseHas('lancamentos', [
        'empresa_id' => $empresa->getKey(),
        'tipo' => 'receita',
        'descricao' => 'Venda à vista',
        'valor' => '320.75',
        'status' => 'pendente',
        'forma_pagamento' => null,
        'categoria_id' => null,
        'contraparte_id' => null,
    ]);
});

it('atualiza uma receita via {verbo} preservando empresa, tipo, categoria e contraparte', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->receita()->create();
    $cliente = Contraparte::factory()->for($empresa)->cliente()->create();

    $receita = Lancamento::factory()->for($empresa)->receita()->create([
        'data' => '2026-01-05',
        'categoria_id' => $categoria->getKey(),
        'contraparte_id' => $cliente->getKey(),
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/receitas/{$receita->getKey()}", [
            'empresa_id' => $outraEmpresa->getKey(),
            'descricao' => 'Venda alterada',
            'valor' => '99.90',
            'data' => '2026-01-20',
            'status' => 'pago',
            'forma_pagamento' => 'pix',
            'categoria_id' => $categoria->getKey(),
            'contraparte_id' => $cliente->getKey(),
        ])
        ->assertRedirect(route('receitas.index'))
        ->assertSessionHas('success', 'Lançamento atualizado com sucesso.');

    $receita->refresh();

    expect($receita->empresa_id)->toBe($empresa->getKey())
        ->and($receita->tipo)->toBe(LancamentoTipo::Receita)
        ->and($receita->descricao)->toBe('Venda alterada')
        ->and($receita->valor)->toBe('99.90')
        ->and($receita->data->format('Y-m-d'))->toBe('2026-01-20')
        ->and($receita->status)->toBe(LancamentoStatus::Pago)
        ->and($receita->forma_pagamento)->toBe('pix')
        ->and($receita->categoria_id)->toBe($categoria->getKey())
        ->and($receita->contraparte_id)->toBe($cliente->getKey());
})->with([
    'patch' => ['patch'],
    'put' => ['put'],
]);

it('atualiza uma despesa via {verbo} preservando categoria e contraparte', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->despesa()->create();
    $fornecedor = Contraparte::factory()->for($empresa)->fornecedor()->create();

    $despesa = Lancamento::factory()->for($empresa)->despesa()->create([
        'categoria_id' => $categoria->getKey(),
        'contraparte_id' => $fornecedor->getKey(),
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/despesas/{$despesa->getKey()}", [
            'descricao' => 'Aluguel novo',
            'valor' => '1200.50',
            'data' => '2026-02-10',
            'status' => 'pendente',
            'forma_pagamento' => 'boleto',
            'categoria_id' => $categoria->getKey(),
            'contraparte_id' => $fornecedor->getKey(),
        ])
        ->assertRedirect(route('despesas.index'))
        ->assertSessionHas('success', 'Lançamento atualizado com sucesso.');

    $despesa->refresh();

    expect($despesa->tipo)->toBe(LancamentoTipo::Despesa)
        ->and($despesa->descricao)->toBe('Aluguel novo')
        ->and($despesa->valor)->toBe('1200.50')
        ->and($despesa->status)->toBe(LancamentoStatus::Pendente)
        ->and($despesa->forma_pagamento)->toBe('boleto')
        ->and($despesa->categoria_id)->toBe($categoria->getKey())
        ->and($despesa->contraparte_id)->toBe($fornecedor->getKey());
})->with([
    'patch' => ['patch'],
    'put' => ['put'],
]);

it('atualiza uma recorrência via {verbo} regenerando os lançamentos pendentes', function (string $verbo) {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'descricao' => 'Aluguel',
        'valor' => '1500.00',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    app(RecorrenciaService::class)->manterHorizonte(24);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/recorrencias/{$recorrencia->getKey()}", [
            'tipo' => 'despesa',
            'descricao' => 'Aluguel novo',
            'valor' => '2000.00',
            'dia' => 10,
            'data_inicio' => '2026-01-01',
            'data_fim' => null,
            'forma_pagamento' => null,
            'ativa' => true,
        ])
        ->assertRedirect(route('recorrencias.index'))
        ->assertSessionHas('success', 'Recorrência atualizada com sucesso.');

    $recorrencia->refresh();

    expect($recorrencia->descricao)->toBe('Aluguel novo')
        ->and($recorrencia->valor)->toBe('2000.00');

    expect($recorrencia->lancamentos()->pendentes()->count())->toBe(25)
        ->and($recorrencia->lancamentos()->pendentes()->first()->descricao)->toBe('Aluguel novo')
        ->and($recorrencia->lancamentos()->pendentes()->first()->valor)->toBe('2000.00');
})->with([
    'patch' => ['patch'],
    'put' => ['put'],
]);

it('atualiza uma contraparte via {verbo}', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $contraparte = Contraparte::factory()->for($empresa)->fornecedor()->create(['nome' => 'Antiga']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/contrapartes/{$contraparte->getKey()}", [
            'tipo' => 'fornecedor',
            'nome' => 'Nova razão',
            'email' => 'contato@nova.com',
        ])
        ->assertRedirect(route('contrapartes.index'))
        ->assertSessionHas('success', 'Contraparte atualizada com sucesso.');

    $this->assertDatabaseHas('contrapartes', [
        'id' => $contraparte->getKey(),
        'tipo' => 'fornecedor',
        'nome' => 'Nova razão',
        'email' => 'contato@nova.com',
    ]);
})->with([
    'patch' => ['patch'],
    'put' => ['put'],
]);

it('atualiza uma categoria via {verbo}', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->despesa()->create(['nome' => 'Antiga']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/categorias/{$categoria->getKey()}", [
            'nome' => 'Nova',
            'tipo' => LancamentoTipo::Despesa->value,
        ])
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('success', 'Categoria atualizada com sucesso.');

    $this->assertDatabaseHas('categorias', [
        'id' => $categoria->getKey(),
        'nome' => 'Nova',
        'tipo' => 'despesa',
    ]);
})->with([
    'patch' => ['patch'],
    'put' => ['put'],
]);

it('atualiza uma empresa do usuário via {verbo}', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->{$verbo}("/empresas/{$empresa->getKey()}", [
            'razao_social' => 'Nova Razão Social Ltda',
            'nome_fantasia' => 'Novo Nome',
            'cnpj' => '12345678000199',
        ])
        ->assertRedirect(route('empresas.index'))
        ->assertSessionHas('success', 'Empresa atualizada com sucesso.');

    $this->assertDatabaseHas('empresas', [
        'id' => $empresa->getKey(),
        'razao_social' => 'Nova Razão Social Ltda',
        'nome_fantasia' => 'Novo Nome',
        'cnpj' => '12345678000199',
    ]);
})->with([
    'patch' => ['patch'],
    'put' => ['put'],
]);
