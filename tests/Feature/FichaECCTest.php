<?php

use App\Models\Evento;
use App\Models\Ficha;
use App\Models\FichaEcc;
use App\Models\TipoMovimento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = createUser();
    $this->actingAs($this->user);

    TipoMovimento::factory()->create(['des_sigla' => 'ECC']);
    $this->evento = createEvento();
});

describe('FichaEccController', function () {

    test('pode acessar listagem de fichas ECC', function () {
        $this->get(route('ecc.index'))
            ->assertStatus(200)
            ->assertViewIs('ficha.listECC')
            ->assertViewHas('fichas');
    });

    test('pode acessar formulario de criacao', function () {
        $this->get(route('ecc.create'))
            ->assertStatus(200)
            ->assertViewIs('ficha.formECC');
    });

    test('pode criar ficha ECC com dados do conjuge', function () {
        $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'Carlos',
            'nom_apelido' => 'Car',
            'dat_nascimento' => '1980-01-01',
            'eml_candidato' => 'carlos@email.com',
            'tam_camiseta' => 'M',
            'ind_consentimento' => true,
            'ind_restricao' => false,

            'nom_conjuge' => 'Maria',
            'tel_conjuge' => '11999999999',
            'dat_nascimento_conjuge' => '1982-01-01',
            'tam_camiseta_conjuge' => 'P',
        ])
            ->assertSessionHas('success');

        $ficha = Ficha::where('eml_candidato', 'carlos@email.com')->first();

        $this->assertDatabaseHas('ficha_ecc', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_conjuge' => 'Maria',
        ]);
    });

    test('falha se dados do conjuge estiverem incompletos', function () {
        $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'nom_conjuge' => 'Maria',
        ])
            ->assertSessionHasErrors([
                'tel_conjuge',
                'dat_nascimento_conjuge',
                'tam_camiseta_conjuge',
            ]);
    });

    test('pode visualizar ficha ECC', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->get(route('ecc.edit', $ficha->idt_ficha))
            ->assertStatus(200)
            ->assertViewIs('ficha.formECC');
    });

    test('pode atualizar ficha ECC', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->put(route('ecc.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'F',
            'nom_candidato' => 'Nome Atualizado',
            'nom_apelido' => 'Atual',
            'dat_nascimento' => '1985-01-01',
            'eml_candidato' => 'novo@email.com',
            'tam_camiseta' => 'G',
            'ind_consentimento' => true,
            'ind_restricao' => false,

            'nom_conjuge' => 'Conjuge Atualizada',
            'tel_conjuge' => '11888888888',
            'dat_nascimento_conjuge' => '1987-01-01',
            'tam_camiseta_conjuge' => 'M',
        ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_candidato' => 'Nome Atualizado',
        ]);

        $this->assertDatabaseHas('ficha_ecc', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_conjuge' => 'Conjuge Atualizada',
        ]);
    });

    test('pode aprovar ficha ECC', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'ind_aprovado' => false,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->get(route('ecc.approve', $ficha->idt_ficha))
            ->assertSessionHas('success');

        $ficha->refresh();
        expect($ficha->ind_aprovado)->toBeTrue();
    });

    test('pode excluir ficha ECC', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->delete(route('ecc.destroy', $ficha->idt_ficha))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
        ]);
    });
});
