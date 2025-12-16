<?php

use App\Models\Ficha;
use App\Models\FichaVem;
use App\Models\TipoMovimento;
use App\Models\TipoResponsavel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = createUser();
    $this->actingAs($this->user);

    TipoMovimento::factory()->create(['des_sigla' => 'VEM']);
    $this->evento = createEvento();

    $this->responsavel = TipoResponsavel::factory()->create();
    $this->restricoes = \App\Models\TipoRestricao::factory()->count(2)->create();
});

describe('FichaVemController', function () {

    test('pode acessar listagem de fichas VEM', function () {
        $this->get(route('vem.index'))
            ->assertStatus(200)
            ->assertViewIs('ficha.listVEM')
            ->assertViewHas('fichas');
    });

    test('pode acessar formulario de criacao', function () {
        $this->get(route('vem.create'))
            ->assertStatus(200)
            ->assertViewIs('ficha.formVEM');
    });

    test('pode criar ficha VEM com dados obrigatorios', function () {
        $this->post(route('vem.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'João',
            'nom_apelido' => 'Jo',
            'dat_nascimento' => '2005-01-01',
            'eml_candidato' => 'joao@email.com',
            'tam_camiseta' => 'M',
            'ind_consentimento' => true,
            'ind_restricao' => false,

            'nom_pai' => 'Pai VEM',
            'tel_pai' => '11999999999',
            'nom_mae' => 'Mae VEM',
            'tel_mae' => '11888888888',
            'idt_falar_com' => $this->responsavel->idt_responsavel,
            'des_onde_estuda' => 'Escola',
            'des_mora_quem' => 'Pais',
        ])
            ->assertSessionHas('success');

        $ficha = Ficha::where('eml_candidato', 'joao@email.com')->first();

        $this->assertDatabaseHas('ficha_vem', [
            'idt_ficha' => $ficha->idt_ficha,
            'des_onde_estuda' => 'Escola',
        ]);
    });

    test('pode criar ficha VEM com restricoes de saude', function () {
        $this->post(route('vem.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'Pedro',
            'nom_apelido' => 'Ped',
            'dat_nascimento' => '2004-01-01',
            'eml_candidato' => 'pedro@email.com',
            'des_endereco' => 'Rua Teste, 123',
            'tam_camiseta' => 'G',
            'ind_consentimento' => true,
            'ind_restricao' => true,

            'idt_falar_com' => $this->responsavel->idt_responsavel,
            'des_onde_estuda' => 'Escola Municipal',
            'des_mora_quem' => 'Pais',
            'nom_pai' => 'Pai Pedro',
            'tel_pai' => '11977777777',
            'nom_mae' => 'Mae Pedro',
            'tel_mae' => '11866666666',

            'restricoes' => [
                $this->restricoes[0]->idt_restricao => true,
            ],
            'complementos' => [
                $this->restricoes[0]->idt_restricao => 'Alergia',
            ],
        ]);

        $ficha = Ficha::latest('idt_ficha')->first();

        //expect($ficha)->not->toBeNull();
        expect($ficha->fichaSaude)->toHaveCount(1);
    });

    test('pode visualizar ficha VEM', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaVem::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->get(route('vem.edit', $ficha->idt_ficha))
            ->assertStatus(200)
            ->assertViewIs('ficha.formVEM');
    });

    test('pode atualizar ficha VEM', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaVem::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->put(route('vem.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'F',
            'nom_candidato' => 'Nome Atualizado',
            'nom_apelido' => 'Apelido',
            'dat_nascimento' => '2006-01-01',
            'eml_candidato' => 'novo@email.com',
            'tam_camiseta' => 'P',
            'ind_consentimento' => true,
            'ind_restricao' => false,

            'nom_pai' => 'Pai VEM atualizado',
            'tel_pai' => '11999999999',
            'nom_mae' => 'Mae VEM atualizada',
            'tel_mae' => '11888888888',
            'idt_falar_com' => $this->responsavel->idt_responsavel,
            'des_onde_estuda' => 'Outra Escola',
            'des_mora_quem' => 'Avós',
        ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_candidato' => 'Nome Atualizado',
        ]);
    });

    test('pode aprovar ficha VEM', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'ind_aprovado' => false,
        ]);

        FichaVem::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->get(route('vem.approve', $ficha->idt_ficha))
            ->assertSessionHas('success');

        $ficha->refresh();
        expect($ficha->ind_aprovado)->toBeTrue();
    });

    test('pode excluir ficha VEM', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaVem::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->delete(route('vem.destroy', $ficha->idt_ficha))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
        ]);
    });
});
