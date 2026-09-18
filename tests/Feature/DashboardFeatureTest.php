<?php

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the financial metrics of the active empresa only', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outra = Empresa::factory()->create();
    $user->empresas()->attach($empresa);
    $user->empresas()->attach($outra);

    $data = now()->startOfMonth()->addDays(5)->format('Y-m-d');

    Lancamento::factory()
        ->for($empresa)
        ->receita()
        ->pago()
        ->create(['valor' => 100, 'data' => $data]);

    Lancamento::factory()
        ->for($empresa)
        ->despesa()
        ->pago()
        ->create(['valor' => 40, 'data' => $data]);

    Lancamento::factory()
        ->for($outra)
        ->receita()
        ->pago()
        ->create(['valor' => 999, 'data' => $data]);

    Lancamento::factory()
        ->for($outra)
        ->despesa()
        ->pago()
        ->create(['valor' => 500, 'data' => $data]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dashboard.metrica.receitas.total', '100,00')
            ->where('dashboard.metrica.receitas.qtd', 1)
            ->where('dashboard.metrica.despesas.total', '40,00')
            ->where('dashboard.metrica.despesas.qtd', 1)
            ->where('dashboard.metrica.resultado.total', '60,00'));
});

it('lists the upcoming pendentes ordered by the nearest date', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $primeira = now()->addDays(2)->format('Y-m-d');
    $segunda = now()->addDays(9)->format('Y-m-d');

    Lancamento::factory()
        ->for($empresa)
        ->despesa()
        ->pendente()
        ->create(['data' => $segunda, 'descricao' => 'Segunda']);

    Lancamento::factory()
        ->for($empresa)
        ->receita()
        ->pendente()
        ->create(['data' => $primeira, 'descricao' => 'Primeira']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('dashboard.proximas', 2)
            ->where('dashboard.proximas.0.descricao', 'Primeira')
            ->where('dashboard.proximas.1.descricao', 'Segunda'));
});

it('counts the despesas vencidas that require attention', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $vencida = now()->subDays(3)->format('Y-m-d');

    Lancamento::factory()
        ->for($empresa)
        ->despesa()
        ->pendente()
        ->create(['data' => $vencida]);

    Lancamento::factory()
        ->for($empresa)
        ->receita()
        ->pendente()
        ->create(['data' => $vencida]);

    Lancamento::factory()
        ->for($empresa)
        ->despesa()
        ->pendente()
        ->create(['data' => now()->addDay()->format('Y-m-d')]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dashboard.vencidas', 1));
});

it('breaks down the despesas by categoria of the periodo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()
        ->for($empresa)
        ->despesa()
        ->create(['nome' => 'Energia']);

    $data = now()->startOfMonth()->addDays(4)->format('Y-m-d');

    Lancamento::factory()
        ->for($empresa)
        ->despesa()
        ->pago()
        ->create(['valor' => 200, 'data' => $data, 'categoria_id' => $categoria->getKey()]);

    Lancamento::factory()
        ->for($empresa)
        ->despesa()
        ->pago()
        ->create(['valor' => 100, 'data' => $data]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dashboard.categorias.0.nome', 'Energia')
            ->where('dashboard.categorias.0.valor', '200,00')
            ->where('dashboard.categorias.0.percentual', 66.7)
            ->where('dashboard.categorias.1.nome', 'Sem categoria')
            ->where('dashboard.categorias.1.valor', '100,00'));
});

it('compares the current period with the previous month', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $mesAtual = now()->startOfMonth()->addDays(5)->format('Y-m-d');
    $mesAnterior = now()->subMonth()->startOfMonth()->addDays(5)->format('Y-m-d');

    Lancamento::factory()
        ->for($empresa)
        ->receita()
        ->pago()
        ->create(['valor' => 100, 'data' => $mesAtual]);

    Lancamento::factory()
        ->for($empresa)
        ->receita()
        ->pago()
        ->create(['valor' => 50, 'data' => $mesAnterior]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dashboard.comparativo.anterior_receitas', 50)
            ->where('dashboard.comparativo.variacao_receitas', 100)
            ->where('dashboard.comparativo.receitas_pct', 100));
});
