<?php

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

afterEach(function () {
    $this->travelBack();
});

it('lists only the receitas of the active empresa in the selected period', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-05']);
    Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-02-05']);
    Lancamento::factory()->for($outraEmpresa)->receita()->create(['data' => '2026-01-10']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Financeiro/Receitas')
            ->has('lancamentos', 1)
            ->where('lancamentos.0.id', $receita->getKey())
            ->where('periodo.chave', '2026-01'));
});

it('renders the resumo of the month considering receitas and despesas', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)->receita()->pago()->create(['data' => '2026-01-05', 'valor' => 100]);
    Lancamento::factory()->for($empresa)->despesa()->pendente()->create(['data' => '2026-01-10', 'valor' => 50]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('resumo.total_receitas', '100,00')
            ->where('resumo.pagas', '100,00')
            ->where('resumo.pendentes', '50,00')
            ->where('resumo.saldo', '50,00'));
});

it('excludes cancelled lancamentos from the resumo of the month', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()->create(['data' => '2026-01-05', 'valor' => 100, 'status' => 'cancelado']);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-06', 'valor' => 50]);
    Lancamento::factory()->for($empresa)
        ->despesa()->create(['data' => '2026-01-10', 'valor' => 30, 'status' => 'cancelado']);
    Lancamento::factory()->for($empresa)
        ->despesa()->pendente()->create(['data' => '2026-01-11', 'valor' => 20]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('resumo.total_receitas', '50,00')
            ->where('resumo.total_despesas', '20,00')
            ->where('resumo.pagas', '50,00')
            ->where('resumo.pendentes', '20,00')
            ->where('resumo.saldo', '30,00'));
});

it('creates a receita using the empresa of the session, ignoring the informed empresa_id', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->receita()->create();
    $cliente = Contraparte::factory()->for($empresa)->cliente()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'empresa_id' => $outraEmpresa->getKey(),
            'descricao' => 'Venda à vista',
            'valor' => '320.75',
            'data' => '2026-01-10',
            'categoria_id' => $categoria->getKey(),
            'contraparte_id' => $cliente->getKey(),
        ])
        ->assertRedirect(route('receitas.index'))
        ->assertSessionHas('success', 'Receita registrada com sucesso.');

    $lancamento = Lancamento::where('descricao', 'Venda à vista')->sole();

    expect($lancamento->empresa_id)->toBe($empresa->getKey())
        ->and($lancamento->tipo)->toBe(LancamentoTipo::Receita)
        ->and($lancamento->valor)->toBe('320.75')
        ->and($lancamento->data->format('Y-m-d'))->toBe('2026-01-10')
        ->and($lancamento->status)->toBe(LancamentoStatus::Pendente)
        ->and($lancamento->categoria_id)->toBe($categoria->getKey())
        ->and($lancamento->contraparte_id)->toBe($cliente->getKey());
});

it('rejects a receita when required fields are missing', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [])
        ->assertSessionHasErrors(['descricao', 'valor', 'data']);
});

it('rejects a receita when valor or data are invalid', function (array $payload, string $campo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'descricao' => 'Venda',
            'valor' => '10',
            'data' => '2026-01-10',
            ...$payload,
        ])
        ->assertSessionHasErrors($campo);
})->with([
    'valor zero' => [['valor' => '0'], 'valor'],
    'valor não numérico' => [['valor' => 'abc'], 'valor'],
    'data inválida' => [['data' => '31/02/2026'], 'data'],
]);

it('rejects a receita with a {campo} of the wrong tipo', function (string $campo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Categoria::factory()->for($empresa)->despesa()->create(['descricao' => 'Aluguel']);
    Contraparte::factory()->for($empresa)->fornecedor()->create(['nome' => 'Imobiliária']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'descricao' => 'Venda',
            'valor' => '10',
            'data' => '2026-01-10',
            'categoria_id' => Categoria::where('descricao', 'Aluguel')->value('id'),
            'contraparte_id' => Contraparte::where('nome', 'Imobiliária')->value('id'),
        ])
        ->assertSessionHasErrors($campo);
})->with([
    'categoria' => ['categoria_id'],
    'contraparte' => ['contraparte_id'],
]);

it('rejects a receita with a categoria of another empresa', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoriaAlheia = Categoria::factory()->for($outraEmpresa)->receita()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'descricao' => 'Venda',
            'valor' => '10',
            'data' => '2026-01-10',
            'categoria_id' => $categoriaAlheia->getKey(),
        ])
        ->assertSessionHasErrors('categoria_id');
});

it('updates a receita keeping the original empresa', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-05']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->put("/receitas/{$receita->getKey()}", [
            'empresa_id' => $outraEmpresa->getKey(),
            'descricao' => 'Venda alterada',
            'valor' => '99.90',
            'data' => '2026-01-20',
        ])
        ->assertRedirect(route('receitas.index'))
        ->assertSessionHas('success');

    $receita->refresh();

    expect($receita->empresa_id)->toBe($empresa->getKey())
        ->and($receita->tipo)->toBe(LancamentoTipo::Receita)
        ->and($receita->descricao)->toBe('Venda alterada')
        ->and($receita->valor)->toBe('99.90')
        ->and($receita->data->format('Y-m-d'))->toBe('2026-01-20');
});

it('returns 404 when updating or deleting a receita of another empresa', function (string $verbo, string $rota) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($outraEmpresa)->receita()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/receitas/{$receita->getKey()}", $rota === 'delete' ? [] : [
            'descricao' => 'Invadida',
            'valor' => '10',
            'data' => '2026-01-10',
        ])
        ->assertNotFound();
})->with([
    'update' => ['put', 'update'],
    'delete' => ['delete', 'delete'],
]);

it('returns 404 when accessing a receita through the despesas routes', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($empresa)->receita()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->put("/despesas/{$receita->getKey()}", [
            'descricao' => 'Invasão',
            'valor' => '10',
            'data' => '2026-01-10',
        ])
        ->assertNotFound();
});

it('marks a receita as pago via the status endpoint', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($empresa)->receita()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post("/receitas/{$receita->getKey()}/status", ['status' => 'pago', 'forma_pagamento' => 'pix'])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('lancamentos', [
        'id' => $receita->getKey(),
        'status' => 'pago',
        'forma_pagamento' => 'pix',
    ]);
});

it('rejects an invalid status through the status endpoint', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($empresa)->receita()->pendente()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post("/receitas/{$receita->getKey()}/status", ['status' => 'faturado'])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('lancamentos', ['id' => $receita->getKey(), 'status' => 'pendente']);
});

it('deletes a despesa', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $despesa = Lancamento::factory()->for($empresa)->despesa()->create();

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->delete("/despesas/{$despesa->getKey()}")
        ->assertRedirect(route('despesas.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('lancamentos', ['id' => $despesa->getKey()]);
});

it('defaults to the current month when no periodo is informed', function () {
    $this->travelTo('2026-05-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $atual = Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-05-10']);
    Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-04-10']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Financeiro/Receitas')
            ->has('lancamentos', 1)
            ->where('lancamentos.0.id', $atual->getKey())
            ->where('periodo.chave', '2026-05')
            ->where('filtroData', null));
});

it('resets the period to the current month when returning to the page', function () {
    $this->travelTo('2026-05-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-05']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('periodo.chave', '2026-01'));

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('periodo.chave', '2026-05'));
});

it('filters the lancamentos of a specific date', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $alvo = Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-05']);
    Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-10']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas?data=2026-01-05')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Financeiro/Receitas')
            ->has('lancamentos', 1)
            ->where('lancamentos.0.id', $alvo->getKey())
            ->where('filtroData', '2026-01-05')
            ->where('periodo.chave', '2026-01'));
});

it('ignores an invalid specific date and falls back to the period', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-05']);
    Lancamento::factory()->for($empresa)->receita()->create(['data' => '2026-01-10']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/receitas?periodo=2026-01&data=2026-02-31')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('lancamentos', 2)
            ->where('filtroData', null)
            ->where('periodo.chave', '2026-01'));
});

it('keeps the informed period after creating a lancamento', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/receitas', [
            'descricao' => 'Venda antiga',
            'valor' => '10',
            'data' => '2026-01-10',
            'periodo' => '2026-01',
        ])
        ->assertRedirect(route('receitas.index', ['periodo' => '2026-01']));
});
