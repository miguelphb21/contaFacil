<?php

use App\Models\Empresa;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('lists the empresas of the user on the selection screen', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->get('/empresas/selecionar')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Empresas/Selecionar')
            ->has('empresas', 1)
            ->where('empresas.0.id', $empresa->getKey()));
});

it('redirects to the selection screen when no empresa is active and the user has many', function () {
    $user = User::factory()->create();
    $user->empresas()->attach(Empresa::factory()->create());
    $user->empresas()->attach(Empresa::factory()->create());

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('empresas.selecionar'));
});

it('selects the single empresa of the user automatically', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->get('/receitas')
        ->assertOk();

    expect(session('empresa_ativa_id'))->toBe($empresa->getKey());
});

it('uses the empresa_id query to define the active empresa', function () {
    $user = User::factory()->create();
    $primeira = Empresa::factory()->create();
    $segunda = Empresa::factory()->create();
    $user->empresas()->attach($primeira);
    $user->empresas()->attach($segunda);

    $this->actingAs($user)
        ->get("/receitas?empresa_id={$segunda->getKey()}")
        ->assertOk();

    expect(session('empresa_ativa_id'))->toBe($segunda->getKey());
});

it('ignores an empresa_id query for an empresa the user does not belong to', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $alheia = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->get("/receitas?empresa_id={$alheia->getKey()}")
        ->assertOk();

    expect(session('empresa_ativa_id'))->toBe($empresa->getKey());
});

it('creates an empresa and selects it as the active one', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/empresas', [
            'razao_social' => 'Loja do Miguel LTDA',
            'nome_fantasia' => 'Loja do Miguel',
            'cnpj' => '11222333000181',
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Empresa criada e selecionada com sucesso.');

    $empresa = Empresa::where('cnpj', '11222333000181')->firstOrFail();

    $this->assertDatabaseHas('empresa_user', [
        'empresa_id' => $empresa->getKey(),
        'user_id' => $user->getKey(),
    ]);

    expect(session('empresa_ativa_id'))->toBe($empresa->getKey());
});

it('rejects an invalid cnpj', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/empresas', [
            'razao_social' => 'Loja do Miguel LTDA',
            'cnpj' => '1234',
        ])
        ->assertSessionHasErrors('cnpj');
});

it('rejects the selection of an empresa the user does not belong to', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $alheia = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->from('/empresas/selecionar')
        ->post('/empresas/selecionar', ['empresa_id' => $alheia->getKey()])
        ->assertRedirect('/empresas/selecionar')
        ->assertSessionHas('error', 'Não foi possível selecionar a empresa.');

    expect(session('empresa_ativa_id'))->toBeNull();
});

it('selects an empresa the user belongs to', function () {
    $user = User::factory()->create();
    $primeira = Empresa::factory()->create();
    $segunda = Empresa::factory()->create();
    $user->empresas()->attach($primeira);
    $user->empresas()->attach($segunda);

    $this->actingAs($user)
        ->post('/empresas/selecionar', ['empresa_id' => $segunda->getKey()])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Empresa selecionada com sucesso.');

    expect(session('empresa_ativa_id'))->toBe($segunda->getKey());
});

it('renders the dashboard honoring the selected periodo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/dashboard?periodo=2026-03')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('periodo.chave', '2026-03')
            ->where('periodo.label', 'Março de 2026'));

    expect(session('periodo_ativo'))->toBe('2026-03');
});

it('forbids deleting an empresa of another user', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $alheia = Empresa::factory()->create();
    $user->empresas()->attach($empresa);
    User::factory()->create()->empresas()->attach($alheia);

    $this->actingAs($user)
        ->delete("/empresas/{$alheia->getKey()}")
        ->assertForbidden();

    $this->assertDatabaseHas('empresas', ['id' => $alheia->getKey()]);
});
