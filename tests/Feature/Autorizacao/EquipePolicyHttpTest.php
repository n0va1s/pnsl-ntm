<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use App\Policies\EquipePolicy;
use Illuminate\Support\Facades\Gate;

// RefreshDatabase já aplicado globalmente via tests/Pest.php

beforeEach(function () {
    $this->vem = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );
    $this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

    // coord-geral: role=user no flat, mas coord_geral no pivot
    $this->coordGeral = User::factory()->create(['role' => 'user']);
    $this->coordGeral->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);

    // coord-equipe-h
    $this->coordH = User::factory()->create();
    $this->coordH->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordEquipeH->value,
    ]);

    // membro-equipe
    $this->membroEquipe = User::factory()->create();
    $this->membroEquipe->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::MembroEquipe->value,
    ]);

    // user sem vínculo algum
    $this->userSemVinculo = User::factory()->create(['role' => 'user']);
});

describe('EquipePolicy::viewAny (TEST-02)', function () {

    it('coord-geral pode viewAny', function () {
        $this->actingAs($this->coordGeral);
        expect(auth()->user()->can('viewAny', Equipe::class))->toBeTrue();
    });

    it('membro-equipe pode viewAny', function () {
        $this->actingAs($this->membroEquipe);
        expect(auth()->user()->can('viewAny', Equipe::class))->toBeTrue();
    });

    it('user sem vínculo pode viewAny (qualquer autenticado)', function () {
        $this->actingAs($this->userSemVinculo);
        expect(auth()->user()->can('viewAny', Equipe::class))->toBeTrue();
    });

});

describe('EquipePolicy::view (TEST-02)', function () {

    it('coord-geral pode view qualquer equipe', function () {
        $this->actingAs($this->coordGeral);
        expect(auth()->user()->can('view', $this->equipe))->toBeTrue();
    });

    it('membro-equipe pode view a equipe da qual é membro', function () {
        $this->actingAs($this->membroEquipe);
        expect(auth()->user()->can('view', $this->equipe))->toBeTrue();
    });

    it('user sem vínculo não pode view (403)', function () {
        $this->actingAs($this->userSemVinculo);
        expect(auth()->user()->can('view', $this->equipe))->toBeFalse();
    });

});

describe('EquipePolicy::update (TEST-02)', function () {

    it('coord-geral pode update qualquer equipe', function () {
        $this->actingAs($this->coordGeral);
        expect(auth()->user()->can('update', $this->equipe))->toBeTrue();
    });

    it('coord-equipe-h pode update a própria equipe', function () {
        $this->actingAs($this->coordH);
        expect(auth()->user()->can('update', $this->equipe))->toBeTrue();
    });

    it('membro-equipe não pode update (403)', function () {
        $this->actingAs($this->membroEquipe);
        expect(auth()->user()->can('update', $this->equipe))->toBeFalse();
    });

    it('user sem vínculo não pode update (403)', function () {
        $this->actingAs($this->userSemVinculo);
        expect(auth()->user()->can('update', $this->equipe))->toBeFalse();
    });

});

describe('EquipePolicy::assignMembers (TEST-02)', function () {

    it('coord-geral pode assignMembers', function () {
        $this->actingAs($this->coordGeral);
        expect(auth()->user()->can('assignMembers', $this->equipe))->toBeTrue();
    });

    it('membro-equipe não pode assignMembers (403)', function () {
        $this->actingAs($this->membroEquipe);
        expect(auth()->user()->can('assignMembers', $this->equipe))->toBeFalse();
    });

    it('user sem vínculo não pode assignMembers (403)', function () {
        $this->actingAs($this->userSemVinculo);
        expect(auth()->user()->can('assignMembers', $this->equipe))->toBeFalse();
    });

    it('coord-equipe-h não pode assignMembers — exclusivo de coord-geral', function () {
        $this->actingAs($this->coordH);
        expect(auth()->user()->can('assignMembers', $this->equipe))->toBeFalse();
    });

});

describe('RBAC-09: Policy registrada no AuthServiceProvider', function () {

    it('Gate resolve EquipePolicy para model Equipe', function () {
        // Gate::getPolicyFor retorna instância de EquipePolicy se $policies está registrado
        $policy = Gate::getPolicyFor(Equipe::class);
        expect($policy)->toBeInstanceOf(EquipePolicy::class);
    });

});

describe('RBAC-10: coord-geral coexiste com users.role sem remover permissões (TEST-02)', function () {

    it('coord-geral com role=user é autorizado na policy mas bloqueado por OnlyManagerMiddleware', function () {
        // Na policy: coord-geral com role=user pode viewAny (autorizado)
        $this->actingAs($this->coordGeral);
        expect(auth()->user()->can('viewAny', Equipe::class))->toBeTrue();

        // No middleware legado: role=user ainda recebe 403 em configuracoes.*
        $this->actingAs($this->coordGeral)
            ->get(route('configuracoes.index'))
            ->assertStatus(403);
    });

});
