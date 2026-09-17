<?php

use App\Enums\LancamentoStatus;
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

it('creates a recorrência and generates the lançamentos of the horizon', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->despesa()->create();
    $fornecedor = Contraparte::factory()->for($empresa)->fornecedor()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/recorrencias', [
            'tipo' => 'despesa',
            'descricao' => 'Aluguel da loja',
            'valor' => '1500.00',
            'dia' => 10,
            'data_inicio' => '2026-01-01',
            'forma_pagamento' => 'boleto',
            'categoria_id' => $categoria->getKey(),
            'contraparte_id' => $fornecedor->getKey(),
        ])
        ->assertRedirect(route('recorrencias.index'))
        ->assertSessionHas('success', 'Recorrência criada com sucesso.');

    $recorrencia = Recorrencia::where('descricao', 'Aluguel da loja')->firstOrFail();

    $this->assertDatabaseHas('recorrencias', [
        'id' => $recorrencia->getKey(),
        'empresa_id' => $empresa->getKey(),
        'tipo' => 'despesa',
        'dia' => 10,
    ]);

    // Janeiro/2026 até Janeiro/2028 (now + 24 meses) = 25 lançamentos.
    $lancamentos = $recorrencia->lancamentos()->orderBy('data')->get();

    expect($lancamentos)->toHaveCount(25)
        ->and($lancamentos->first()->data->format('Y-m-d'))->toBe('2026-01-10')
        ->and($lancamentos->last()->data->format('Y-m-d'))->toBe('2028-01-10')
        ->and($recorrencia->lancamentos()->where('status', LancamentoStatus::Pendente)->count())->toBe(25);

    $this->assertDatabaseHas('lancamentos', [
        'recorrencia_id' => $recorrencia->getKey(),
        'empresa_id' => $empresa->getKey(),
        'tipo' => 'despesa',
        'descricao' => 'Aluguel da loja',
        'valor' => '1500.00',
        'status' => 'pendente',
        'categoria_id' => $categoria->getKey(),
        'contraparte_id' => $fornecedor->getKey(),
    ]);

    // Não gera duplicado no mesmo mês.
    expect($lancamentos->pluck('data')->map(fn ($data) => $data->format('Y-m'))->unique())->toHaveCount(25);
});

it('does not duplicate lançamentos when generation runs again', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    $service = app(RecorrenciaService::class);

    $service->manterHorizonte(24);
    $depois = $recorrencia->lancamentos()->count();

    expect($depois)->toBe(25);

    $service->manterHorizonte(24);

    expect($recorrencia->lancamentos()->count())->toBe($depois);
});

it('regenerates only the pendentes when updating, preserving the pagos', function () {
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

    $pago = $recorrencia->lancamentos()->orderBy('data')->first();
    $pago->update(['status' => LancamentoStatus::Pago]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->put("/recorrencias/{$recorrencia->getKey()}", [
            'tipo' => 'despesa',
            'descricao' => 'Aluguel novo',
            'valor' => '2000.00',
            'dia' => 10,
            'data_inicio' => '2026-01-01',
            'ativa' => true,
        ])
        ->assertRedirect(route('recorrencias.index'))
        ->assertSessionHas('success', 'Recorrência atualizada com sucesso.');

    $this->assertDatabaseHas('lancamentos', [
        'id' => $pago->getKey(),
        'status' => 'pago',
        'valor' => '1500.00',
    ]);

    expect($recorrencia->lancamentos()->count())->toBe(25)
        ->and($recorrencia->lancamentos()->pendentes()->count())->toBe(24);

    $pendente = $recorrencia->lancamentos()->pendentes()->orderBy('data')->first();

    expect($pendente->descricao)->toBe('Aluguel novo')
        ->and($pendente->valor)->toBe('2000.00');
});

it('keeps the rolling horizon without recreating months excluded manually', function () {
    $this->travelTo('2026-01-01 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'dia' => 15,
        'data_inicio' => '2026-01-01',
    ]);

    $service = app(RecorrenciaService::class);

    expect($service->manterHorizonte(24))->toBe(25);

    $recorrencia->lancamentos()->whereBetween('data', ['2026-06-01', '2026-06-30'])->delete();

    $this->travelTo('2027-06-01 12:00:00');

    expect($service->manterHorizonte(24))->toBe(17);

    expect($recorrencia->lancamentos()->count())->toBe(41)
        ->and($recorrencia->lancamentos()->whereBetween('data', ['2026-06-01', '2026-06-30'])->count())->toBe(0)
        ->and($recorrencia->lancamentos()->orderByDesc('data')->first()->data->format('Y-m-d'))->toBe('2029-06-15');
});

it('regenerates the pendentes through the endpoint', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    app(RecorrenciaService::class)->manterHorizonte(24);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->from('/recorrencias')
        ->post("/recorrencias/{$recorrencia->getKey()}/regenerar")
        ->assertRedirect('/recorrencias')
        ->assertSessionHas('success', '25 lançamentos regenerados.');

    expect($recorrencia->lancamentos()->count())->toBe(25);
});

it('keeps the generated lançamentos when the recorrência is excluded', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'descricao' => 'Aluguel',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    app(RecorrenciaService::class)->manterHorizonte(24);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->delete("/recorrencias/{$recorrencia->getKey()}")
        ->assertRedirect(route('recorrencias.index'))
        ->assertSessionHas('success', 'Recorrência excluída com sucesso.');

    $this->assertDatabaseMissing('recorrencias', ['id' => $recorrencia->getKey()]);

    expect(Lancamento::where('recorrencia_id', $recorrencia->getKey())->count())->toBe(0)
        ->and(Lancamento::where('descricao', 'Aluguel')->count())->toBe(25);
});

it('rejects an invalid recorrência', function (array $payload, string $campo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/recorrencias', [
            'tipo' => 'despesa',
            'descricao' => 'Aluguel',
            'valor' => '1500.00',
            'dia' => 10,
            'data_inicio' => '2026-01-01',
            'data_fim' => '2026-12-01',
            ...$payload,
        ])
        ->assertSessionHasErrors($campo);
})->with([
    'dia fora do intervalo' => [['dia' => 29], 'dia'],
    'data final anterior à inicial' => [['data_fim' => '2025-12-01'], 'data_fim'],
    'descrição curta' => [['descricao' => 'A'], 'descricao'],
]);

it('rejects a recorrência with a categoria of another tipo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoriaReceita = Categoria::factory()->for($empresa)->receita()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/recorrencias', [
            'tipo' => 'despesa',
            'descricao' => 'Aluguel',
            'valor' => '1500.00',
            'dia' => 10,
            'data_inicio' => '2026-01-01',
            'categoria_id' => $categoriaReceita->getKey(),
        ])
        ->assertSessionHasErrors('categoria_id');
});

it('returns 404 when updating or deleting a recorrência of another empresa', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($outraEmpresa)->despesa()->create();

    $payload = $verbo === 'put'
        ? [
            'tipo' => 'despesa',
            'descricao' => 'Invasão',
            'valor' => '10',
            'dia' => 5,
            'data_inicio' => '2026-01-01',
        ]
        : [];

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/recorrencias/{$recorrencia->getKey()}", $payload)
        ->assertNotFound();
})->with([
    'update' => ['put'],
    'delete' => ['delete'],
]);
