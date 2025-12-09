<?php

use App\Models\Evento;
use App\Models\Ficha;
use App\Models\FichaSGM;
use App\Models\TipoMovimento;
use App\Models\TipoResponsavel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->movimento = TipoMovimento::factory()->create(['des_sigla' => 'Segue-Me']);
    $this->evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $this->responsavel = TipoResponsavel::factory()->create();
});

describe('FichaSGMController', function () {
    test('pode acessar listagem de fichas sgm', function () {
        $this->actingAs($this->user)
            ->get(route('sgm.index'))
            ->assertStatus(200)
            ->assertViewIs('ficha.listSGM')
            ->assertViewHas('fichas');
    });

    test('pode acessar formulario de criacao', function () {
        $this->actingAs($this->user)
            ->get(route('sgm.create'))
            ->assertStatus(200)
            ->assertViewIs('ficha.formSGM');
    });

    test('pode criar ficha sgm com dados validos', function () {
        $fichaData = [
            // Dados Ficha
            'idt_evento' => $this->evento->idt_evento,
            'nom_candidato' => 'Candidato SGM',
            'nom_apelido' => 'SGM',
            'dat_nascimento' => '2000-01-01',
            'eml_candidato' => 'sgm@email.com',
            'tip_genero' => 'M',
            'tam_camiseta' => 'M',
            'ind_consentimento' => true,
            'ind_restricao' => false,
            
            // Dados FichaSGM
            'idt_falar_com' => $this->responsavel->idt_responsavel,
            'des_mora_quem' => 'Pais',
            'nom_pai' => 'Pai SGM',
            'tel_pai' => '11999999999',
            'nom_mae' => 'Mae SGM',
            'tel_mae' => '11888888888',
        ];

        $this->actingAs($this->user)
            ->post(route('sgm.store'), $fichaData)
            ->assertRedirect(route('sgm.index', ['evento' => $this->evento->idt_evento]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'nom_candidato' => 'Candidato SGM',
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $ficha = Ficha::where('nom_candidato', 'Candidato SGM')->first();
        $this->assertDatabaseHas('ficha_sgm', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_pai' => 'Pai SGM',
        ]);
    });

    test('pode visualizar ficha sgm', function () {
        $ficha = Ficha::factory()->create(['idt_evento' => $this->evento->idt_evento]);
        FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);

        $this->actingAs($this->user)
            ->get(route('sgm.show', $ficha->idt_ficha))
            ->assertStatus(200)
            ->assertViewIs('ficha.formSGM');
    });

    test('pode editar ficha sgm', function () {
        $ficha = Ficha::factory()->create(['idt_evento' => $this->evento->idt_evento]);
        FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);

        $this->actingAs($this->user)
            ->get(route('sgm.edit', $ficha->idt_ficha))
            ->assertStatus(200)
            ->assertViewIs('ficha.formSGM');
    });

    test('pode atualizar ficha sgm', function () {
        $ficha = Ficha::factory()->create(['idt_evento' => $this->evento->idt_evento]);
        $sgm = FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);

        $updateData = [
            'idt_evento' => $this->evento->idt_evento,
            'nom_candidato' => 'Candidato Atualizado',
            'nom_apelido' => 'Atualizado',
            'dat_nascimento' => '2000-01-01',
            'eml_candidato' => 'atualizado@email.com',
            'tip_genero' => 'F',
            'tam_camiseta' => 'G',
            'ind_consentimento' => true,
            'ind_restricao' => false,
            
            'idt_falar_com' => $this->responsavel->idt_responsavel,
            'des_mora_quem' => 'Avós',
            'nom_mae' => 'Mae Atualizada',
            'tel_mae' => '11777777777',
        ];

        $this->actingAs($this->user)
            ->put(route('sgm.update', $ficha->idt_ficha), $updateData)
            ->assertRedirect(route('sgm.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_candidato' => 'Candidato Atualizado',
        ]);

        $this->assertDatabaseHas('ficha_sgm', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_mae' => 'Mae Atualizada',
        ]);
    });

    test('pode aprovar ficha sgm', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'ind_aprovado' => false
        ]);
        FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);

        $this->actingAs($this->user)
            ->get(route('sgm.approve', $ficha->idt_ficha))
            ->assertRedirect(route('sgm.index'))
            ->assertSessionHas('success');

        $ficha->refresh();
        expect($ficha->ind_aprovado)->toBeTrue();
    });

    test('pode excluir ficha sgm', function () {
        $ficha = Ficha::factory()->create(['idt_evento' => $this->evento->idt_evento]);
        FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);

        $this->actingAs($this->user)
            ->delete(route('sgm.destroy', $ficha->idt_ficha))
            ->assertRedirect(route('sgm.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ficha', ['idt_ficha' => $ficha->idt_ficha]);
    });
});
