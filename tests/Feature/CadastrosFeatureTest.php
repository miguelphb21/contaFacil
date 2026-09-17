<?php

use App\Enums\LancamentoTipo;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Empresa;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('lists the contrapartes of the active empresa filtering by tipo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $cliente = Contraparte::factory()->for($empresa)->cliente()->create(['nome' => 'Maria']);
    Contraparte::factory()->for($empresa)->fornecedor()->create(['nome' => 'Imobiliária']);
    Contraparte::factory()->for($outraEmpresa)->cliente()->create(['nome' => 'Cliente alheio']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/contrapartes?tipo=cliente')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Contrapartes/Index')
            ->has('contrapartes', 1)
            ->where('contrapartes.0.id', $cliente->getKey())
            ->where('filtro', 'cliente'));
});

it('creates a contraparte in the active empresa of the session', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/contrapartes', [
            'empresa_id' => $outraEmpresa->getKey(),
            'tipo' => 'cliente',
            'nome' => 'Maria Souza',
        ])
        ->assertRedirect(route('contrapartes.index'))
        ->assertSessionHas('success', 'Contraparte cadastrada com sucesso.');

    $this->assertDatabaseHas('contrapartes', [
        'empresa_id' => $empresa->getKey(),
        'tipo' => 'cliente',
        'nome' => 'Maria Souza',
    ]);
});

it('rejects a contraparte without nome or tipo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/contrapartes', [])
        ->assertSessionHasErrors(['nome', 'tipo']);
});

it('updates and deletes a contraparte', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $contraparte = Contraparte::factory()->for($empresa)->fornecedor()->create(['nome' => 'Antiga']);

    $payload = $verbo === 'put'
        ? ['tipo' => 'fornecedor', 'nome' => 'Nova razão']
        : [];

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/contrapartes/{$contraparte->getKey()}", $payload)
        ->assertRedirect(route('contrapartes.index'))
        ->assertSessionHas('success');

    if ($verbo === 'put') {
        $this->assertDatabaseHas('contrapartes', [
            'id' => $contraparte->getKey(),
            'nome' => 'Nova razão',
        ]);
    } else {
        $this->assertDatabaseMissing('contrapartes', ['id' => $contraparte->getKey()]);
    }
})->with([
    'update' => ['put'],
    'delete' => ['delete'],
]);

it('returns 404 when updating or deleting a contraparte of another empresa', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $contraparte = Contraparte::factory()->for($outraEmpresa)->fornecedor()->create();

    $payload = $verbo === 'put' ? ['tipo' => 'fornecedor', 'nome' => 'Invasão'] : [];

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/contrapartes/{$contraparte->getKey()}", $payload)
        ->assertNotFound();
})->with([
    'update' => ['put'],
    'delete' => ['delete'],
]);

it('lists the categorias of the active empresa filtering by tipo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $despesa = Categoria::factory()->for($empresa)->despesa()->create(['nome' => 'Aluguel']);
    Categoria::factory()->for($empresa)->receita()->create(['nome' => 'Vendas']);
    Categoria::factory()->for($outraEmpresa)->despesa()->create(['nome' => 'Categoria alheia']);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->get('/categorias?tipo=despesa')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Categorias/Index')
            ->has('categorias', 1)
            ->where('categorias.0.id', $despesa->getKey())
            ->where('filtro', 'despesa'));
});

it('creates a categoria in the active empresa of the session', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/categorias', [
            'empresa_id' => $outraEmpresa->getKey(),
            'nome' => 'Alimentação',
            'tipo' => LancamentoTipo::Despesa->value,
        ])
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('success', 'Categoria cadastrada com sucesso.');

    $this->assertDatabaseHas('categorias', [
        'empresa_id' => $empresa->getKey(),
        'nome' => 'Alimentação',
        'tipo' => 'despesa',
    ]);
});

it('rejects a categoria without nome or tipo', function () {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->post('/categorias', [])
        ->assertSessionHasErrors(['nome', 'tipo']);
});

it('updates and deletes a categoria', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($empresa)->despesa()->create(['nome' => 'Antiga']);

    $payload = $verbo === 'put'
        ? ['nome' => 'Nova', 'tipo' => LancamentoTipo::Despesa->value]
        : [];

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/categorias/{$categoria->getKey()}", $payload)
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('success');

    if ($verbo === 'put') {
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->getKey(),
            'nome' => 'Nova',
        ]);
    } else {
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->getKey()]);
    }
})->with([
    'update' => ['put'],
    'delete' => ['delete'],
]);

it('returns 404 when updating or deleting a categoria of another empresa', function (string $verbo) {
    $user = User::factory()->create();
    $empresa = Empresa::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $user->empresas()->attach($empresa);

    $categoria = Categoria::factory()->for($outraEmpresa)->despesa()->create();

    $payload = $verbo === 'put' ? ['nome' => 'Invasão', 'tipo' => 'despesa'] : [];

    $this->actingAs($user)
        ->withSession(['empresa_ativa_id' => $empresa->getKey()])
        ->{$verbo}("/categorias/{$categoria->getKey()}", $payload)
        ->assertNotFound();
})->with([
    'update' => ['put'],
    'delete' => ['delete'],
]);
