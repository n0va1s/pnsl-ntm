<?php

use App\Models\Ficha;
use App\Models\FichaEcc;
use App\Models\FichaEccFilho;
use App\Models\TipoMovimento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = createUser();
    $this->actingAs($this->user);

    TipoMovimento::factory()->create(['des_sigla' => 'ECC']);
    $this->evento = createEvento();
});

describe('FichaEccController - INCLUSÃO', function () {

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

    test('pode criar ficha ECC com dados completos do participante', function () {
        $response = $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => '123.456.789-00',
            'tip_genero' => 'M',
            'nom_candidato' => 'Carlos Silva',
            'nom_apelido' => 'Car',
            'dat_nascimento' => '1980-01-01',
            'tel_candidato' => '(61) 99999-9999',
            'eml_candidato' => 'carlos@email.com',
            'nom_profissao' => 'Engenheiro',
            'tam_camiseta' => 'M',
            'tip_como_soube' => 'A',
            'tip_habilidade' => 'A',
            'tip_como_soube' => 'IND',
            'ind_catolico' => 1,
            'ind_consentimento' => 1,
            'ind_restricao' => 0,
            'txt_observacao' => 'Comentário do participante',

            'cpf_conjuge' => '987.654.321-00',
            'nom_conjuge' => 'Maria Silva',
            'nom_apelido_conjuge' => 'Mari',
            'tip_genero_conjuge' => 'F',
            'dat_nascimento_conjuge' => '1982-01-01',
            'tel_conjuge' => '(61) 98888-8888',
            'eml_conjuge' => 'maria@email.com',
            'nom_profissao_conjuge' => 'Médica',
            'ind_catolico_conjuge' => 1,
            'tip_habilidade_conjuge' => 'A',
            'tam_camiseta_conjuge' => 'P',
            'tip_estado_civil' => 'C',
            'nom_paroquia' => 'Paróquia do Lago',
            'dat_casamento' => '2010-06-15',
            'qtd_filhos' => 0,
        ]);

        $response->assertSessionHas('success');

        $ficha = Ficha::where('eml_candidato', 'carlos@email.com')->first();

        $this->assertDatabaseHas('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_candidato' => 'Carlos Silva',
            'cpf_candidato' => '123.456.789-00',
            'ind_consentimento' => true,
        ]);

        $this->assertDatabaseHas('ficha_ecc', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_conjuge' => 'Maria Silva',
            'tip_estado_civil' => 'C',
        ]);
    });

    test('pode criar ficha ECC com filhos', function () {
        $response = $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => '111.111.111-11',
            'tip_genero' => 'M',
            'nom_candidato' => 'João',
            'dat_nascimento' => '1975-05-10',
            'eml_candidato' => 'joao@email.com',
            'tam_camiseta' => 'G',
            'ind_consentimento' => 1,
            'ind_restricao' => 0,

            'cpf_conjuge' => '222.222.222-22',
            'nom_conjuge' => 'Ana',
            'tip_genero_conjuge' => 'F',
            'dat_nascimento_conjuge' => '1976-03-20',
            'tam_camiseta_conjuge' => 'M',
            'tip_estado_civil' => 'C',
            'qtd_filhos' => 2,
            
            'filhos' => [
                ['nom_filho' => 'Pedro', 'dat_nascimento_filho' => '2005-01-15'],
                ['nom_filho' => 'Lucas', 'dat_nascimento_filho' => '2008-06-20'],
            ],
        ]);

        $response->assertSessionHas('success');

        $ficha = Ficha::where('eml_candidato', 'joao@email.com')->first();

        $this->assertDatabaseHas('ficha_ecc_filho', [
            'nom_filho' => 'Pedro',
        ]);

        $this->assertDatabaseHas('ficha_ecc_filho', [
            'nom_filho' => 'Lucas',
        ]);

        $this->assertEquals(2, FichaEccFilho::where('idt_ficha', $ficha->fichaEcc->idt_ficha)->count());
    });

    test('pode criar ficha ECC com restrições de saúde', function () {
        $response = $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => '333.333.333-33',
            'tip_genero' => 'M',
            'nom_candidato' => 'Paulo',
            'dat_nascimento' => '1980-07-12',
            'eml_candidato' => 'paulo@email.com',
            'tam_camiseta' => 'M',
            'ind_consentimento' => 1,
            'ind_restricao' => 1,

            'cpf_conjuge' => '444.444.444-44',
            'nom_conjuge' => 'Julia',
            'tip_genero_conjuge' => 'F',
            'dat_nascimento_conjuge' => '1982-09-08',
            'tam_camiseta_conjuge' => 'P',
            'tip_estado_civil' => 'C',
            
            'restricoes' => [
                1 => 1,
                2 => 1,
            ],
            'complementos' => [
                1 => 'Alergia a amendoim',
                2 => 'Sem glúten',
            ],
        ]);

        $response->assertSessionHas('success');

        $ficha = Ficha::where('eml_candidato', 'paulo@email.com')->first();

        $this->assertEquals(2, $ficha->fichaSaude->count());
    });

    test('falha ao criar ficha sem campos obrigatórios do participante', function () {
        $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
        ])
            ->assertSessionHasErrors([
                'nom_candidato',
                'dat_nascimento',
                'eml_candidato',
                'tam_camiseta',
                'ind_consentimento',
            ]);
    });

    test('falha ao criar ficha sem dados obrigatórios do cônjuge', function () {
        $this->post(route('ecc.store'), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => '555.555.555-55',
            'tip_genero' => 'M',
            'nom_candidato' => 'Marcos',
            'dat_nascimento' => '1975-04-25',
            'eml_candidato' => 'marcos@email.com',
            'tam_camiseta' => 'G',
            'ind_consentimento' => 1,
            'ind_restricao' => 0,
        ])
            ->assertSessionHasErrors([
                'nom_conjuge',
                'tip_genero_conjuge',
                'dat_nascimento_conjuge',
                'tam_camiseta_conjuge',
            ]);
    });
});

describe('FichaEccController - ALTERAÇÃO', function () {

    test('pode atualizar dados do participante', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'nom_candidato' => 'Nome Original',
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $response = $this->put(route('ecc.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => $ficha->cpf_candidato,
            'tip_genero' => 'F',
            'nom_candidato' => 'Nome Atualizado',
            'nom_apelido' => 'Novo Apelido',
            'dat_nascimento' => '1985-01-01',
            'eml_candidato' => 'novo@email.com',
            'tam_camiseta' => 'G',
            'ind_consentimento' => 1,
            'ind_restricao' => 0,

            'cpf_conjuge' => $ficha->fichaEcc->cpf_conjuge,
            'nom_conjuge' => $ficha->fichaEcc->nom_conjuge,
            'tip_genero_conjuge' => $ficha->fichaEcc->tip_genero_conjuge?->value,
            'dat_nascimento_conjuge' => $ficha->fichaEcc->dat_nascimento_conjuge->format('Y-m-d'),
            'tam_camiseta_conjuge' => $ficha->fichaEcc->tam_camiseta_conjuge?->value,
            'tip_habilidade_conjuge' => $ficha->fichaEcc->tip_habilidade_conjuge?->value,
            'tip_estado_civil' => $ficha->fichaEcc->tip_estado_civil?->value,
        ]);

        $response->assertSessionHas('success');

        $ficha->refresh();

        $this->assertEquals('Nome Atualizado', $ficha->nom_candidato);
        $this->assertEquals('F', $ficha->tip_genero->value);
        $this->assertEquals('novo@email.com', $ficha->eml_candidato);
    });

    test('pode atualizar dados do cônjuge', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
            'nom_conjuge' => 'Nome Cônjuge Original',
        ]);

        $response = $this->put(route('ecc.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => $ficha->cpf_candidato,
            'tip_genero' => $ficha->tip_genero?->value,
            'nom_candidato' => $ficha->nom_candidato,
            'dat_nascimento' => $ficha->dat_nascimento->format('Y-m-d'),
            'eml_candidato' => $ficha->eml_candidato,
            'tam_camiseta' => $ficha->tam_camiseta?->value,
            'ind_consentimento' => 1,
            'ind_restricao' => 0,

            'cpf_conjuge' => $ficha->fichaEcc->cpf_conjuge,
            'nom_conjuge' => 'Nome Cônjuge Atualizado',
            'tip_genero_conjuge' => 'F',
            'dat_nascimento_conjuge' => '1990-05-15',
            'tam_camiseta_conjuge' => 'M',
            'tip_habilidade_conjuge' => $ficha->fichaEcc->tip_habilidade_conjuge?->value,
            'tip_estado_civil' => 'C',
        ]);

        $response->assertSessionHas('success');

        $ficha->fichaEcc->refresh();

        $this->assertEquals('Nome Cônjuge Atualizado', $ficha->fichaEcc->nom_conjuge);
    });

    test('pode adicionar filhos em ficha existente', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
            'qtd_filhos' => 0,
        ]);

        $response = $this->put(route('ecc.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => $ficha->cpf_candidato,
            'tip_genero' => $ficha->tip_genero?->value,
            'nom_candidato' => $ficha->nom_candidato,
            'dat_nascimento' => $ficha->dat_nascimento->format('Y-m-d'),
            'eml_candidato' => $ficha->eml_candidato,
            'tam_camiseta' => $ficha->tam_camiseta?->value,
            'ind_consentimento' => 1,
            'ind_restricao' => 0,

            'cpf_conjuge' => $ficha->fichaEcc->cpf_conjuge,
            'nom_conjuge' => $ficha->fichaEcc->nom_conjuge,
            'tip_genero_conjuge' => $ficha->fichaEcc->tip_genero_conjuge?->value,
            'dat_nascimento_conjuge' => $ficha->fichaEcc->dat_nascimento_conjuge->format('Y-m-d'),
            'tam_camiseta_conjuge' => $ficha->fichaEcc->tam_camiseta_conjuge?->value,
            'tip_habilidade_conjuge' => $ficha->fichaEcc->tip_habilidade_conjuge?->value,
            'tip_estado_civil' => $ficha->fichaEcc->tip_estado_civil?->value,
            'qtd_filhos' => 1,
            
            'filhos' => [
                ['nom_filho' => 'Novo Filho', 'dat_nascimento_filho' => '2010-03-20'],
            ],
        ]);

        $response->assertSessionHas('success');

        $ficha->fichaEcc->refresh();

        $this->assertEquals(1, $ficha->fichaEcc->filhos->count());
        $this->assertEquals('Novo Filho', $ficha->fichaEcc->filhos->first()->nom_filho);
    });

    test('pode substituir filhos ao atualizar ficha', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $fichaEcc = FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        // Criar filhos antigos
        FichaEccFilho::factory()->create([
            'idt_ficha' => $fichaEcc->idt_ficha,
            'nom_filho' => 'Filho Antigo',
        ]);

        $this->assertEquals(1, $fichaEcc->filhos->count());

        // Atualizar com novos filhos
        $this->put(route('ecc.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => $ficha->cpf_candidato,
            'tip_genero' => $ficha->tip_genero?->value,
            'nom_candidato' => $ficha->nom_candidato,
            'dat_nascimento' => $ficha->dat_nascimento->format('Y-m-d'),
            'eml_candidato' => $ficha->eml_candidato,
            'tam_camiseta' => $ficha->tam_camiseta?->value,
            'ind_consentimento' => 1,
            'ind_restricao' => 0,

            'cpf_conjuge' => $fichaEcc->cpf_conjuge,
            'nom_conjuge' => $fichaEcc->nom_conjuge,
            'tip_genero_conjuge' => $fichaEcc->tip_genero_conjuge?->value,
            'dat_nascimento_conjuge' => $fichaEcc->dat_nascimento_conjuge->format('Y-m-d'),
            'tam_camiseta_conjuge' => $fichaEcc->tam_camiseta_conjuge?->value,
            'tip_habilidade_conjuge' => $fichaEcc->tip_habilidade_conjuge?->value,
            'tip_estado_civil' => $fichaEcc->tip_estado_civil?->value,
            'qtd_filhos' => 2,
            
            'filhos' => [
                ['nom_filho' => 'Novo Filho 1', 'dat_nascimento_filho' => '2005-01-15'],
                ['nom_filho' => 'Novo Filho 2', 'dat_nascimento_filho' => '2008-06-20'],
            ],
        ]);

        $fichaEcc->refresh();

        $this->assertEquals(2, $fichaEcc->filhos->count());
        $this->assertDatabaseMissing('ficha_ecc_filho', [
            'nom_filho' => 'Filho Antigo',
        ]);
    });

    test('pode atualizar restrições de saúde', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'ind_restricao' => 0,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->put(route('ecc.update', $ficha->idt_ficha), [
            'idt_evento' => $this->evento->idt_evento,
            'cpf_candidato' => $ficha->cpf_candidato,
            'tip_genero' => $ficha->tip_genero?->value,
            'nom_candidato' => $ficha->nom_candidato,
            'dat_nascimento' => $ficha->dat_nascimento->format('Y-m-d'),
            'eml_candidato' => $ficha->eml_candidato,
            'tam_camiseta' => $ficha->tam_camiseta?->value,
            'ind_consentimento' => 1,
            'ind_restricao' => 1,

            'cpf_conjuge' => $ficha->fichaEcc->cpf_conjuge,
            'nom_conjuge' => $ficha->fichaEcc->nom_conjuge,
            'tip_genero_conjuge' => $ficha->fichaEcc->tip_genero_conjuge?->value,
            'dat_nascimento_conjuge' => $ficha->fichaEcc->dat_nascimento_conjuge->format('Y-m-d'),
            'tam_camiseta_conjuge' => $ficha->fichaEcc->tam_camiseta_conjuge?->value,
            'tip_habilidade_conjuge' => $ficha->fichaEcc->tip_habilidade_conjuge?->value,
            'tip_estado_civil' => $ficha->fichaEcc->tip_estado_civil?->value,
            
            'restricoes' => [1 => 1],
            'complementos' => [1 => 'Alergia a frutos do mar'],
        ]);

        $ficha->refresh();

        $this->assertTrue($ficha->ind_restricao);
        $this->assertEquals(1, $ficha->fichaSaude->count());
    });
});

describe('FichaEccController - EXCLUSÃO', function () {

    test('pode excluir ficha ECC com sucesso', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $fichaId = $ficha->idt_ficha;

        $response = $this->delete(route('ecc.destroy', $fichaId))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ficha', [
            'idt_ficha' => $fichaId,
        ]);
    });

    test('deleta em cascata filhos ao remover ficha', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $fichaEcc = FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        FichaEccFilho::factory()->create([
            'idt_ficha' => $fichaEcc->idt_ficha,
        ]);

        $fichaId = $ficha->idt_ficha;

        $this->delete(route('ecc.destroy', $fichaId));

        $this->assertSoftDeleted('ficha', ['idt_ficha' => $fichaId]);
    });

    test('deleta em cascata restrições de saúde ao remover ficha', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $fichaId = $ficha->idt_ficha;

        $this->delete(route('ecc.destroy', $fichaId));

        $this->assertSoftDeleted('ficha', ['idt_ficha' => $fichaId]);
    });

    test('exibe mensagem de erro ao tentar excluir ficha com constraints', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->delete(route('ecc.destroy', $ficha->idt_ficha))
            ->assertSessionHas('success');
    });
});
