<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;

// RefreshDatabase já aplicado globalmente via tests/Pest.php — não adicionar uses() manual

it('role=user recebe 403 em configuracoes.index (TEST-07)', function () {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user)
        ->get(route('configuracoes.index'))
        ->assertStatus(403);
});

it('role=admin acessa configuracoes.index com 200', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->withoutVite()
        ->actingAs($admin)
        ->get(route('configuracoes.index'))
        ->assertStatus(200)
        ->assertViewIs('configuracoes.index');
});

it('role=coord acessa configuracoes.index com 200', function () {
    $coord = User::factory()->create(['role' => 'coord']);
    $this->withoutVite()
        ->actingAs($coord)
        ->get(route('configuracoes.index'))
        ->assertStatus(200)
        ->assertViewIs('configuracoes.index');
});

it('coord-geral com role=user nao acessa configuracoes.index (sistemas ortogonais - RBAC-10)', function () {
    // coord-geral é papel VEM (pivot), NÃO equivalente a role=admin/coord (flat)
    // Este teste documenta e protege a separação entre os dois sistemas
    $vem = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );
    $equipe = Equipe::factory()->create(['idt_movimento' => $vem->idt_movimento]);
    $coordGeral = User::factory()->create(['role' => 'user']); // role flat = user
    $coordGeral->equipes()->attach($equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);

    $this->actingAs($coordGeral)
        ->get(route('configuracoes.index'))
        ->assertStatus(403); // coord-geral NÃO tem acesso a configuracoes — sistemas ortogonais
});
