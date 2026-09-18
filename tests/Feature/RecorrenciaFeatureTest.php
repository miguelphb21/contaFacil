<?php

use App\Enums\LancamentoStatus;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\Recorrencia;
use App\Models\User;
use App\Services\RecorrenciaService;
use Inertia\Testing\AssertableInertia;

afterEach(function () {
    $this->travelBack();
});

it('creates a recurring despesa with a period and generates the occurrences', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->despesa()->create();
    $fornecedor = Contraparte::factory()->for($empresa)->fornecedor()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
            'categoria_id' => $categoria->getKey(),
            'contraparte_id' => $fornecedor->getKey(),
        ])
        ->assertRedirect(route('despesas.index'))
        ->assertSessionHas('success', 'Despesa registrada com sucesso.');

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();

    expect($recorrencia->empresa_id)->toBe($empresa->getKey())
        ->and($recorrencia->dia)->toBe(10)
        ->and($recorrencia->data_inicio->format('Y-m-d'))->toBe('2026-01-10')
        ->and($recorrencia->data_fim->format('Y-m-d'))->toBe('2026-03-31');

    $lancamentos = $recorrencia->lancamentos()->orderBy('data')->get();

    expect($lancamentos)->toHaveCount(3)
        ->and($lancamentos->first()->data->format('Y-m-d'))->toBe('2026-01-10')
        ->and($lancamentos->last()->data->format('Y-m-d'))->toBe('2026-03-10')
        ->and($lancamentos->every(fn ($item) => $item->status === LancamentoStatus::Pendente))->toBeTrue();

    $this->assertDatabaseHas('lancamentos', [
        'recorrencia_id' => $recorrencia->getKey(),
        'empresa_id' => $empresa->getKey(),
        'tipo' => 'despesa',
        'descricao' => 'Internet',
        'categoria_id' => $categoria->getKey(),
        'contraparte_id' => $fornecedor->getKey(),
    ]);
});

it('creates a continuous recurring receita when no end date is given', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'descricao' => 'Mensalidade',
            'valor' => '99.90',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => null,
        ])
        ->assertRedirect(route('receitas.index'));

    $recorrencia = Recorrencia::where('descricao', 'Mensalidade')->sole();

    expect($recorrencia->data_fim)->toBeNull();

    // Janeiro/2026 até Janeiro/2028 (now + 24 meses) = 25 ocorrências.
    $lancamentos = $recorrencia->lancamentos()->orderBy('data')->get();

    expect($lancamentos)->toHaveCount(25)
        ->and($lancamentos->last()->data->format('Y-m-d'))->toBe('2028-01-10');
});

it('does not create a recorrência when the movement is not recurring', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Compra única',
            'valor' => '30.00',
            'data' => '2026-01-10',
            'recorrente' => false,
            'data_fim' => null,
        ])
        ->assertRedirect(route('despesas.index'));

    $this->assertDatabaseCount('recorrencias', 0);

    $lancamento = Lancamento::where('descricao', 'Compra única')->sole();

    expect($lancamento->recorrencia_id)->toBeNull();
});

it('rejects a recorrência with an end date before the start date', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => '2026-01-09',
        ])
        ->assertSessionHasErrors('data_fim');
});

it('applies the chosen status only to the first occurrence', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'status' => 'pago',
            'forma_pagamento' => 'pix',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
        ])
        ->assertRedirect(route('despesas.index'));

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();
    $lancamentos = $recorrencia->lancamentos()->orderBy('data')->get();

    expect($lancamentos->first()->status)->toBe(LancamentoStatus::Pago)
        ->and($lancamentos->first()->forma_pagamento)->toBe('pix')
        ->and($lancamentos->slice(1)->every(fn ($item) => $item->status === LancamentoStatus::Pendente))->toBeTrue();
});

it('updates the series when editing a recurring movement', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
        ]);

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();
    $ocorrencia = $recorrencia->lancamentos()->orderBy('data')->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->put("/despesas/{$ocorrencia->getKey()}", [
            'descricao' => 'Internet fibra',
            'valor' => '200.00',
            'data' => '2026-01-10',
            'status' => 'pendente',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
        ])
        ->assertRedirect(route('despesas.index'))
        ->assertSessionHas('success', 'Lançamento atualizado com sucesso.');

    $recorrencia->refresh();

    expect($recorrencia->descricao)->toBe('Internet fibra')
        ->and($recorrencia->valor)->toBe('200.00');

    $pendentes = $recorrencia->lancamentos()->orderBy('data')->get();

    expect($pendentes)->toHaveCount(3)
        ->and($pendentes->every(fn ($item) => $item->descricao === 'Internet fibra'))->toBeTrue()
        ->and($pendentes->every(fn ($item) => $item->valor === '200.00'))->toBeTrue();
});

it('keeps the generated lançamentos when a recurring movement is deleted', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
        ]);

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();
    $ocorrencia = $recorrencia->lancamentos()->orderBy('data')->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->delete("/despesas/{$ocorrencia->getKey()}")
        ->assertRedirect(route('despesas.index'));

    expect(Lancamento::where('recorrencia_id', $recorrencia->getKey())->count())->toBe(2)
        ->and(Recorrencia::whereKey($recorrencia->getKey())->exists())->toBeTrue();
});

it('closes the recorrência and stops generating future months', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => null,
        ]);

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();
    $ocorrencia = $recorrencia->lancamentos()->orderBy('data')->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->from('/despesas')
        ->post("/despesas/{$ocorrencia->getKey()}/encerrar-recorrencia")
        ->assertRedirect('/despesas')
        ->assertSessionHas('success', 'Recorrência encerrada com sucesso.');

    $recorrencia->refresh();

    expect($recorrencia->ativa)->toBeFalse()
        ->and($recorrencia->data_fim->format('Y-m-d'))->toBe('2026-01-10')
        ->and($recorrencia->lancamentos()->count())->toBe(1);

    $this->travelTo('2026-06-15 12:00:00');

    expect(app(RecorrenciaService::class)->manterHorizonte(24))->toBe(0);
});

it('removes all future pending occurrences when encerrar is triggered from a future occurrence', function (string $tipo, string $rota) {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->{$tipo}()->create([
        'descricao' => "Recorrente {$tipo}",
        'valor' => '150.00',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    app(RecorrenciaService::class)->manterHorizonte(24);

    expect($recorrencia->lancamentos()->count())->toBe(25);

    $futura = $recorrencia->lancamentos()
        ->whereBetween('data', ['2026-12-01', '2026-12-31'])
        ->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->from($rota)
        ->post("{$rota}/{$futura->getKey()}/encerrar-recorrencia")
        ->assertRedirect($rota)
        ->assertSessionHas('success', 'Recorrência encerrada com sucesso.');

    $recorrencia->refresh();

    expect($recorrencia->ativa)->toBeFalse()
        ->and($recorrencia->lancamentos()
            ->whereDate('data', '>', '2026-01-15')
            ->count())->toBe(0);
})->with([
    'despesa' => ['despesa', '/despesas'],
    'receita' => ['receita', '/receitas'],
]);

it('returns 404 when closing a recorrência of another empresa', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($outraEmpresa)->despesa()->create();
    $lancamento = Lancamento::factory()->for($outraEmpresa)->despesa()->create([
        'recorrencia_id' => $recorrencia->getKey(),
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post("/despesas/{$lancamento->getKey()}/encerrar-recorrencia")
        ->assertNotFound();
});

it('does not duplicate lançamentos when generation runs again', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $empresa = Empresa::factory()->create();

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

it('preserves the pagos while regenerating the pendentes', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $empresa = Empresa::factory()->create();

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'descricao' => 'Aluguel',
        'valor' => '1500.00',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    app(RecorrenciaService::class)->manterHorizonte(24);

    $pago = $recorrencia->lancamentos()->orderBy('data')->first();
    $pago->update(['status' => LancamentoStatus::Pago]);

    $recorrencia->update(['descricao' => 'Aluguel novo', 'valor' => '2000.00']);
    app(RecorrenciaService::class)->regenerarPendentes($recorrencia);

    $this->assertDatabaseHas('lancamentos', [
        'id' => $pago->getKey(),
        'status' => 'pago',
        'valor' => '1500.00',
    ]);

    expect($recorrencia->lancamentos()->count())->toBe(25)
        ->and($recorrencia->lancamentos()->pendentes()->count())->toBe(24)
        ->and($recorrencia->lancamentos()->pendentes()->orderBy('data')->first()->descricao)->toBe('Aluguel novo');
});

it('keeps the rolling horizon without recreating months excluded manually', function () {
    $this->travelTo('2026-01-01 12:00:00');

    $empresa = Empresa::factory()->create();

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

it('keeps the original value and clears future occurrences when the recorrência is turned off on edit', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'recorrente' => true,
            'data_fim' => null,
        ]);

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();
    $principal = $recorrencia->lancamentos()->orderBy('data')->firstOrFail();

    expect($recorrencia->lancamentos()->count())->toBe(25);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->put("/despesas/{$principal->getKey()}", [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'status' => 'pendente',
            'recorrente' => false,
            'data_fim' => null,
        ])
        ->assertRedirect(route('despesas.index'))
        ->assertSessionHas('success', 'Lançamento atualizado com sucesso.');

    $principal->refresh();
    $recorrencia->refresh();

    expect($principal->valor)->toBe('150.00')
        ->and($recorrencia->ativa)->toBeFalse()
        ->and($recorrencia->data_fim->format('Y-m-d'))->toBe('2026-01-10');

    $restantes = $recorrencia->lancamentos()->get();

    expect($restantes)->toHaveCount(1)
        ->and($restantes->first()->getKey())->toBe($principal->getKey())
        ->and($restantes->first()->valor)->toBe('150.00');
});

it('does not inflate the despesa value when editing without changing the valor', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/despesas', [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'status' => 'pago',
            'forma_pagamento' => 'pix',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
        ]);

    $recorrencia = Recorrencia::where('descricao', 'Internet')->sole();
    $principal = $recorrencia->lancamentos()->orderBy('data')->firstOrFail();
    $quantidadeInicial = $recorrencia->lancamentos()->count();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->put("/despesas/{$principal->getKey()}", [
            'descricao' => 'Internet',
            'valor' => '150.00',
            'data' => '2026-01-10',
            'status' => 'pago',
            'forma_pagamento' => 'pix',
            'recorrente' => true,
            'data_fim' => '2026-03-31',
        ])
        ->assertRedirect(route('despesas.index'));

    $principal->refresh();

    expect($principal->valor)->toBe('150.00')
        ->and($recorrencia->lancamentos()->count())->toBe($quantidadeInicial)
        ->and($recorrencia->lancamentos()->whereDate('data', '=', '2026-02-10')->sole()->valor)->toBe('150.00')
        ->and($recorrencia->lancamentos()->whereDate('data', '=', '2026-03-10')->sole()->valor)->toBe('150.00');
});

it('does not regenerate the month of an occurrence deleted from an active recorrência', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'descricao' => 'Internet',
        'valor' => '150.00',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    $service = app(RecorrenciaService::class);

    expect($service->manterHorizonte(24))->toBe(25);

    $ultima = $recorrencia->lancamentos()->orderByDesc('data')->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->delete("/despesas/{$ultima->getKey()}")
        ->assertRedirect(route('despesas.index'));

    expect($recorrencia->lancamentos()->count())->toBe(24);

    expect($service->manterHorizonte(24))->toBe(0);

    expect($recorrencia->lancamentos()->count())->toBe(24)
        ->and($recorrencia->lancamentos()
            ->whereBetween('data', ['2028-01-01', '2028-01-31'])
            ->count())->toBe(0);
});

it('keeps a deleted occurrence out of the series when it is regenerated on edit', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'descricao' => 'Internet',
        'valor' => '150.00',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    $service = app(RecorrenciaService::class);

    expect($service->manterHorizonte(24))->toBe(25);

    $junho = $recorrencia->lancamentos()
        ->whereBetween('data', ['2026-06-01', '2026-06-30'])
        ->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->delete("/despesas/{$junho->getKey()}")
        ->assertRedirect(route('despesas.index'));

    expect($recorrencia->lancamentos()->count())->toBe(24);

    $recorrencia->update(['valor' => '200.00']);
    $service->regenerarPendentes($recorrencia);

    expect($recorrencia->lancamentos()->count())->toBe(24)
        ->and($recorrencia->lancamentos()
            ->whereBetween('data', ['2026-06-01', '2026-06-30'])
            ->count())->toBe(0);
});

it('does not return a deleted recurring occurrence in the report', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $recorrencia = Recorrencia::factory()->for($empresa)->despesa()->create([
        'descricao' => 'Internet',
        'valor' => '150.00',
        'dia' => 10,
        'data_inicio' => '2026-01-01',
    ]);

    app(RecorrenciaService::class)->manterHorizonte(24);

    $ultima = $recorrencia->lancamentos()->orderByDesc('data')->firstOrFail();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?periodo=2028-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dados.totais.total_despesas', '150,00')
            ->where('dados.totais.qtd_despesas', 1));

    $this->delete("/despesas/{$ultima->getKey()}")
        ->assertRedirect(route('despesas.index'));

    app(RecorrenciaService::class)->manterHorizonte(24);

    $this->get('/relatorios?periodo=2028-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dados.totais.total_despesas', '0,00')
            ->where('dados.totais.qtd_despesas', 0));
});
