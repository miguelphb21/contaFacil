<?php

use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the report for a specific month scoped to the active empresa', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $receita = Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-05', 'valor' => 100]);
    Lancamento::factory()->for($empresa)
        ->despesa()->pago()->create(['data' => '2026-01-10', 'valor' => 40]);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-02-10', 'valor' => 999]);
    Lancamento::factory()->for($outraEmpresa)
        ->receita()->pago()->create(['data' => '2026-01-20', 'valor' => 500]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Relatorios/Index')
            ->where('periodo.tipo', 'mes')
            ->where('periodo.label', 'Janeiro de 2026')
            ->has('dados.receitas', 1)
            ->where('dados.receitas.0.id', $receita->getKey())
            ->where('dados.totais.total_receitas', '100,00')
            ->where('dados.totais.total_despesas', '40,00')
            ->where('dados.totais.resultado', '60,00')
            ->where('dados.totais.qtd_receitas', 1)
            ->where('dados.totais.qtd_despesas', 1));
});

it('renders the report for a custom period', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-03-05', 'valor' => 100]);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-07-10', 'valor' => 300]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?inicio=2026-01-01&fim=2026-06-30')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('periodo.tipo', 'periodo')
            ->has('dados.receitas', 1)
            ->where('dados.totais.total_receitas', '100,00'));
});

it('renders the report for a year', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2025-12-31', 'valor' => 100]);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-01', 'valor' => 200]);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-12-31', 'valor' => 300]);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2027-01-01', 'valor' => 400]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?ano=2026')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('periodo.tipo', 'ano')
            ->where('periodo.label', 'Ano de 2026')
            ->has('dados.receitas', 2)
            ->where('dados.totais.total_receitas', '500,00'));
});

it('renders the report for the whole period when no filter is provided', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2025-01-01', 'valor' => 100]);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-06-15', 'valor' => 200]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('periodo.tipo', 'tudo')
            ->where('periodo.label', 'Todo o período')
            ->has('dados.receitas', 2)
            ->where('dados.totais.total_receitas', '300,00'));
});

it('excludes cancelled lancamentos from the report totals', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()
        ->create(['data' => '2026-01-05', 'valor' => 100, 'status' => 'cancelado']);
    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-06', 'valor' => 50]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('dados.receitas', 2)
            ->where('dados.receitas.0.cancelado', true)
            ->where('dados.totais.total_receitas', '50,00'));
});

it('updates the report totals after a {tipo} lancamento is deleted', function (string $tipo, string $rota, string $campoTotal, string $campoQtd) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $lancamento = Lancamento::factory()->for($empresa)
        ->{$tipo}()->pago()->create(['data' => '2026-01-05', 'valor' => 100]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where("dados.totais.{$campoTotal}", '100,00')
            ->where("dados.totais.{$campoQtd}", 1));

    $this->delete("/{$rota}/{$lancamento->getKey()}")
        ->assertRedirect(route("{$rota}.index"));

    $this->get('/relatorios?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where("dados.totais.{$campoTotal}", '0,00')
            ->where("dados.totais.{$campoQtd}", 0));
})->with([
    'receita' => ['receita', 'receitas', 'total_receitas', 'qtd_receitas'],
    'despesa' => ['despesa', 'despesas', 'total_despesas', 'qtd_despesas'],
]);

it('isolates the report data between empresas', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);
    $user->empresas()->attach($outraEmpresa);

    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-10', 'valor' => 100]);
    Lancamento::factory()->for($outraEmpresa)
        ->receita()->pago()->create(['data' => '2026-01-10', 'valor' => 900]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dados.totais.total_receitas', '100,00')
            ->where('dados.totais.qtd_receitas', 1));
});

it('includes the categoria and contraparte names in the report rows', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->despesa()->create(['nome' => 'Aluguel']);
    $fornecedor = Contraparte::factory()->for($empresa)->fornecedor()->create(['nome' => 'Imobiliária XPTO']);

    Lancamento::factory()->for($empresa)
        ->despesa()->pago()->create([
            'data' => '2026-01-10',
            'valor' => 1200,
            'categoria_id' => $categoria->getKey(),
            'contraparte_id' => $fornecedor->getKey(),
        ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios?periodo=2026-01')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dados.despesas.0.categoria', 'Aluguel')
            ->where('dados.despesas.0.contraparte', 'Imobiliária XPTO'));
});

it('downloads the report as PDF', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-10', 'valor' => 100]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios/pdf?periodo=2026-01')
        ->assertOk()
        ->assertDownload('relatorio-financeiro.pdf');
});

it('downloads the report as Excel', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)
        ->receita()->pago()->create(['data' => '2026-01-10', 'valor' => 100]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/relatorios/excel?periodo=2026-01')
        ->assertOk()
        ->assertDownload('relatorio-financeiro.xlsx');
});
