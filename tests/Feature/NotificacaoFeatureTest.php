<?php

use App\Enums\LancamentoStatus;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\Notificacao;
use App\Models\User;

afterEach(function () {
    $this->travelBack();
});

it('creates a notification for pending receitas and despesas due within 7 days', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $despesa = Lancamento::factory()->for($empresa)->despesa()->pendente()->create([
        'descricao' => 'Internet',
        'valor' => '150.00',
        'data' => '2026-01-18',
    ]);

    $receita = Lancamento::factory()->for($empresa)->receita()->pendente()->create([
        'descricao' => 'Serviço prestado',
        'valor' => '800.00',
        'data' => '2026-01-20',
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->getJson('/notificacoes')
        ->assertOk()
        ->assertJsonCount(2, 'novas')
        ->assertJsonPath('nao_lidas', 2)
        ->assertJsonPath('novas.0.mensagem', 'Despesa vence em 3 dias: Internet (150,00)')
        ->assertJsonPath('novas.1.mensagem', 'Receita vence em 5 dias: Serviço prestado (800,00)');

    expect(Notificacao::where('lancamento_id', $despesa->getKey())->exists())->toBeTrue()
        ->and(Notificacao::where('lancamento_id', $receita->getKey())->exists())->toBeTrue();
});

it('notifies overdue pending lancamentos before the upcoming ones', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)->despesa()->pendente()->create([
        'descricao' => 'Aluguel',
        'valor' => '1500.00',
        'data' => '2026-01-10',
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->getJson('/notificacoes')
        ->assertOk()
        ->assertJsonPath('novas.0.mensagem', 'Despesa venceu há 5 dias: Aluguel (1.500,00)');
});

it('ignores lancamentos beyond 7 days or already paid/cancelled', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)->despesa()->pendente()->create(['data' => '2026-01-16']);
    Lancamento::factory()->for($empresa)->despesa()->pendente()->create(['data' => '2026-01-22']);
    Lancamento::factory()->for($empresa)->despesa()->pendente()->create(['data' => '2026-01-23']);
    Lancamento::factory()->for($empresa)->despesa()->pago()->create(['data' => '2026-01-16']);
    Lancamento::factory()->for($empresa)->despesa()->create([
        'status' => LancamentoStatus::Cancelado,
        'data' => '2026-01-16',
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->getJson('/notificacoes')
        ->assertOk()
        ->assertJsonCount(2, 'novas')
        ->assertJsonPath('nao_lidas', 2);
});

it('does not duplicate notifications and persists the read state', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    Lancamento::factory()->for($empresa)->despesa()->pendente()->create([
        'descricao' => 'Internet',
        'data' => '2026-01-18',
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->getJson('/notificacoes')
        ->assertJsonPath('nao_lidas', 1);

    $this->getJson('/notificacoes')
        ->assertJsonCount(0, 'novas')
        ->assertJsonPath('nao_lidas', 1)
        ->assertJsonCount(1, 'notificacoes');

    $this->postJson('/notificacoes/marcar-lidas')
        ->assertOk()
        ->assertJson(['ok' => true]);

    $this->getJson('/notificacoes')
        ->assertJsonPath('nao_lidas', 0)
        ->assertJsonPath('notificacoes.0.lido', true);
});

it('scopes the notifications to the active empresa', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);
    $user->empresas()->attach($outraEmpresa);

    Lancamento::factory()->for($empresa)->despesa()->pendente()->create([
        'descricao' => 'Empresa ativa',
        'valor' => '150.00',
        'data' => '2026-01-16',
    ]);

    Lancamento::factory()->for($outraEmpresa)->despesa()->pendente()->create([
        'descricao' => 'Outra empresa',
        'valor' => '150.00',
        'data' => '2026-01-16',
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->getJson('/notificacoes')
        ->assertJsonPath('nao_lidas', 1)
        ->assertJsonPath('notificacoes.0.mensagem', 'Despesa vence em 1 dia: Empresa ativa (150,00)');
});

it('removes the notification once the lancamento is no longer pending', function () {
    $this->travelTo('2026-01-15 12:00:00');

    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $lancamento = Lancamento::factory()->for($empresa)->despesa()->pendente()->create([
        'data' => '2026-01-16',
    ]);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->getJson('/notificacoes')
        ->assertJsonPath('nao_lidas', 1);

    $lancamento->update(['status' => LancamentoStatus::Pago]);

    $this->getJson('/notificacoes')
        ->assertJsonCount(0, 'notificacoes')
        ->assertJsonPath('nao_lidas', 0);
});
