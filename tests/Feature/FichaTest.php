<?php

use App\Models\Evento;
use App\Models\Ficha;
use App\Models\FichaVem;
use App\Models\FichaEcc;
use App\Models\TipoMovimento;
use App\Models\User;
use App\Services\FichaService;
use Database\Factories\TipoEquipeFactory;
use Database\Factories\TipoMovimentoFactory;
use Database\Factories\TipoResponsavelFactory;
use Database\Factories\TipoRestricaoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Dados comuns para todos os testes foram movidos para o beforeEach global

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->fichaService = new FichaService();

    // Criar usuário e logar
    $this->user = createUser();
    $this->actingAs($this->user);

    // Criar pessoa genérica para testes
    $this->pessoa = createPessoa();

    TipoMovimentoFactory::seedDefaults();
    TipoEquipeFactory::seedDefaults();
    TipoResponsavelFactory::seedDefaults();
    TipoRestricaoFactory::seedDefaults();

    $this->restricoes = \App\Models\TipoRestricao::all();

    $this->movimento = TipoMovimento::all()->first();
    $this->evento = createEvento();
});

// ==================== TESTES VEM ====================

describe('Cadastro de Ficha VEM', function () {

    test('pode acessar página de criação de ficha VEM', function () {
        $this->actingAs($this->user)
            ->get(route('vem.create'))
            ->assertStatus(200)
            ->assertViewIs('ficha.formVEM')
            ->assertViewHas(['ficha', 'eventos', 'movimentopadrao']);
    });

    test('pode criar ficha VEM com dados obrigatórios', function () {
        $fichaData = [
            // Ficha
            'idt_evento'        => $this->evento->idt_evento,
            'tip_genero'        => 'M',
            'nom_candidato'     => 'João Silva',
            'nom_apelido'      => 'João',
            'eml_candidato'     => 'joao@email.com',
            // Dados obrigatórios
            'dat_nascimento'    => '2005-01-15',
            'tam_camiseta'      => 'M',
            'ind_consentimento' => true,
            'ind_restricao'     => false,

            // Ficha VEM
            'idt_falar_com'     => 1,
            'des_onde_estuda'   => 'Escola Estadual Central',
            'des_mora_quem'     => 'Com os pais',
        ];
        $this->actingAs($this->user)
            ->post(route('vem.store'), $fichaData)
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Ficha cadastrada com sucesso!');

        $this->assertDatabaseHas('ficha', [
            'nom_candidato' => 'João Silva',
            'eml_candidato' => 'joao@email.com',
            'idt_evento' => $this->evento->idt_evento,
        ]);
    });

    test('pode criar ficha VEM com dados dos pais', function () {
        $fichaData = [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'F',
            'nom_candidato' => 'Maria Santos',
            'nom_apelido' => 'Maria',
            'dat_nascimento' => '2006-03-20',
            'eml_candidato' => 'maria@email.com',
            'tam_camiseta' => 'P',
            'ind_consentimento' => true,
            'ind_restricao' => false,
            // Dados VEM específicos
            'nom_mae' => 'Ana Santos',
            'tel_mae' => '11999887766',
            'nom_pai' => 'Carlos Santos',
            'tel_pai' => '11999554433',

            'idt_falar_com'     => 1,
            'des_onde_estuda' => 'Colégio São José',
            'des_mora_quem' => 'Com os pais',
        ];

        $this->actingAs($this->user)
            ->post(route('vem.store'), $fichaData)
            ->assertRedirect(route('home'));

        $ficha = Ficha::where('eml_candidato', 'maria@email.com')->first();

        $this->assertDatabaseHas('ficha_vem', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_mae' => 'Ana Santos',
            'nom_pai' => 'Carlos Santos',
        ]);
    });

    test('pode criar ficha VEM com restrições de saúde', function () {
        $fichaData = [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'Pedro Costa',
            'nom_apelido' => 'Pedro',
            'dat_nascimento' => '2004-07-10',
            'eml_candidato' => 'pedro@email.com',
            'tam_camiseta' => 'G',
            'ind_consentimento' => true,
            'ind_restricao' => true,
            // Dados VEM específicos
            'idt_falar_com'     => 1,
            'des_onde_estuda' => 'Colégio São José',
            'des_mora_quem' => 'Com os pais',
            'restricoes' => [
                $this->restricoes[0]->idt_restricao => true,
                $this->restricoes[1]->idt_restricao => true,
            ],
            'complementos' => [
                $this->restricoes[0]->idt_restricao => 'Alergia severa a amendoim',
                $this->restricoes[1]->idt_restricao => 'Medicação diária necessária',
            ],
            'nom_mae' => 'Julia Costa',
        ];

        $this->actingAs($this->user)
            ->post(route('vem.store'), $fichaData)
            ->assertRedirect(route('home'));

        $ficha = Ficha::where('eml_candidato', 'pedro@email.com')->first();

        expect($ficha->fichaSaude)->toHaveCount(2);

        $this->assertDatabaseHas('ficha_saude', [
            'idt_ficha' => $ficha->idt_ficha,
            'idt_restricao' => $this->restricoes[0]->idt_restricao,
            'txt_complemento' => 'Alergia severa a amendoim',
        ]);
    });

    test('falha ao criar ficha VEM sem dados obrigatórios', function () {
        $this->actingAs($this->user)
            ->post(route('vem.store'), [])
            ->assertSessionHasErrors([
                'idt_evento',
                'tip_genero',
                'nom_candidato',
                'nom_apelido',
                'dat_nascimento',
                'eml_candidato',
                'tam_camiseta',
                'ind_consentimento',
            ]);
    });

    test('pode visualizar ficha VEM criada', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        FichaVem::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $this->actingAs($this->user)
            ->get(route('vem.edit', $ficha->idt_ficha))
            ->assertStatus(200)
            ->assertViewHas('ficha');
    });

    test('pode atualizar ficha VEM', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $updateData = [
            'idt_evento' => $ficha->idt_evento,
            'tip_genero' => $ficha->tip_genero,
            'nom_candidato' => 'Nome Atualizado',
            'nom_apelido' => 'Apelido Atualizado',
            'dat_nascimento' => $ficha->dat_nascimento->format('Y-m-d'),
            'eml_candidato' => 'novo@email.com',
            'tam_camiseta' => $ficha->tam_camiseta,
            'ind_consentimento' => true,
            'ind_restricao' => false,
            'nom_mae' => 'Mãe Atualizada',

            'idt_falar_com'     => 2,
            'des_onde_estuda' => 'Colégio Atualizado',
            'des_mora_quem' => 'Com a avó',

        ];

        $this->actingAs($this->user)
            ->put(route('vem.update', $ficha->idt_ficha), $updateData)
            ->assertRedirect(route('vem.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_candidato' => 'Nome Atualizado',
            'eml_candidato' => 'novo@email.com',
        ]);
    });
});

// ==================== TESTES ECC ====================

describe('Cadastro de Ficha ECC', function () {

    test('pode acessar página de criação de ficha ECC', function () {
        $this->actingAs($this->user)
            ->get(route('ecc.create'))
            ->assertStatus(200)
            ->assertViewIs('ficha.formECC')
            ->assertViewHas(['ficha', 'eventos', 'movimentopadrao']);
    });

    test('pode criar ficha ECC com dados obrigatórios', function () {
        $fichaData = [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'Roberto Silva',
            'nom_apelido' => 'Roberto',
            'dat_nascimento' => '1985-05-20',
            'eml_candidato' => 'roberto@email.com',
            'tam_camiseta' => 'G',
            'ind_consentimento' => true,
            'ind_restricao' => false,
            // Dados ECC específicos
            'nom_conjuge' => 'Maria Oliveira',
            'nom_apelido_conjuge' => 'Maria',
            'tel_conjuge' => '11987654321',
            'dat_nascimento_conjuge' => '1982-08-10',
            'tam_camiseta_conjuge' => 'M',
        ];

        $this->actingAs($this->user)
            ->post(route('ecc.store'), $fichaData)
            ->assertRedirect(route('ecc.index'))
            ->assertSessionHas('success', 'Ficha cadastrada com sucesso!');

        $this->assertDatabaseHas('ficha', [
            'nom_candidato' => 'Roberto Silva',
            'eml_candidato' => 'roberto@email.com',
            'idt_evento' => $this->evento->idt_evento,
        ]);
    });

    test('pode criar ficha ECC com dados do cônjuge', function () {
        $fichaData = [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'Carlos Oliveira',
            'nom_apelido' => 'Carlos',
            'dat_nascimento' => '1980-12-15',
            'eml_candidato' => 'carlos@email.com',
            'tam_camiseta' => 'M',
            'ind_consentimento' => true,
            'ind_restricao' => false,
            // Dados ECC específicos
            'nom_conjuge' => 'Maria Oliveira',
            'nom_apelido_conjuge' => 'Maria',
            'tel_conjuge' => '11987654321',
            'dat_nascimento_conjuge' => '1982-08-10',
            'tam_camiseta_conjuge' => 'M',
        ];

        $this->actingAs($this->user)
            ->post(route('ecc.store'), $fichaData)
            ->assertRedirect(route('ecc.index'));

        $ficha = Ficha::where('eml_candidato', 'carlos@email.com')->first();

        $this->assertDatabaseHas('ficha_ecc', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_conjuge' => 'Maria Oliveira',
            'tel_conjuge' => '11987654321',
        ]);
    });

    test('pode criar ficha ECC com restrições de saúde', function () {
        $fichaData = [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'F',
            'nom_candidato' => 'Ana Fernandes',
            'nom_apelido' => 'Ana',
            'dat_nascimento' => '1990-03-25',
            'eml_candidato' => 'ana@email.com',
            'tam_camiseta' => 'P',
            'ind_consentimento' => true,
            'ind_restricao' => true,
            'restricoes' => [
                $this->restricoes[0]->idt_restricao => true,
            ],
            'complementos' => [
                $this->restricoes[0]->idt_restricao => 'Diabetes tipo 1',
            ],
            'nom_conjuge' => 'Paulo Fernandes',
            'nom_apelido_conjuge' => 'Paulinho',
            'tel_conjuge' => '11999999999',
            'dat_nascimento_conjuge' => '1992-03-10',
            'tam_camiseta_conjuge' => 'G',
        ];

        $this->actingAs($this->user)
            ->post(route('ecc.store'), $fichaData)
            ->assertRedirect(route('ecc.index'));

        $ficha = Ficha::where('eml_candidato', 'ana@email.com')->first();

        expect($ficha->fichaSaude)->toHaveCount(1);

        $this->assertDatabaseHas('ficha_saude', [
            'idt_ficha' => $ficha->idt_ficha,
            'txt_complemento' => 'Diabetes tipo 1',
        ]);
    });

    test('falha ao criar ficha ECC com dados do cônjuge incompletos', function () {
        $fichaData = [
            'idt_evento' => $this->evento->idt_evento,
            'tip_genero' => 'M',
            'nom_candidato' => 'João Santos',
            'nom_apelido' => 'João',
            'dat_nascimento' => '1985-01-01',
            'eml_candidato' => 'joao@email.com',
            'tam_camiseta' => 'M',
            'ind_consentimento' => true,
            'ind_restricao' => false,
            'nom_conjuge' => 'Maria Santos',
            // Faltam: tel_conjuge, dat_nascimento_conjuge, tam_camiseta_conjuge
        ];

        $this->actingAs($this->user)
            ->post(route('ecc.store'), $fichaData)
            ->assertSessionHasErrors([
                'tel_conjuge',
                'dat_nascimento_conjuge',
                'tam_camiseta_conjuge',
            ]);
    });

    test('pode atualizar ficha ECC existente', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $fichaEcc = FichaEcc::factory()->create([
            'idt_ficha' => $ficha->idt_ficha,
        ]);

        $updateData = [
            'idt_evento' => $ficha->idt_evento,
            'tip_genero' => $ficha->tip_genero,
            'nom_candidato' => 'Nome Atualizado ECC',
            'nom_apelido' => $ficha->nom_apelido,
            'dat_nascimento' => $ficha->dat_nascimento->format('Y-m-d'),
            'eml_candidato' => 'atualizado@email.com',
            'tam_camiseta' => $ficha->tam_camiseta,
            'ind_consentimento' => true,
            'ind_restricao' => false,
            'tel_conjuge' => '11999888777',
            'dat_nascimento_conjuge' => '1985-01-01',
            'tam_camiseta_conjuge' => 'XGG',
            // Dados ECC específicos
            'nom_conjuge' => 'Cônjuge Atualizado',
            'tel_conjuge' => '11999888777',
            'dat_nascimento_conjuge' => '1985-01-01',
            'tam_camiseta_conjuge' => 'XGG',

        ];

        $this->actingAs($this->user)
            ->put(route('ecc.update', $ficha->idt_ficha), $updateData)
            ->assertRedirect(route('ecc.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_candidato' => 'Nome Atualizado ECC',
            'eml_candidato' => 'atualizado@email.com',
        ]);

        $this->assertDatabaseHas('ficha_ecc', [
            'idt_ficha' => $ficha->idt_ficha,
            'nom_conjuge' => 'Cônjuge Atualizado',
        ]);
    });

    test('pode deletar ficha ECC', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $this->actingAs($this->user)
            ->delete(route('ecc.destroy', $ficha->idt_ficha))
            ->assertRedirect(route('ecc.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ficha', [
            'idt_ficha' => $ficha->idt_ficha,
        ]);
    });
});

// ==================== TESTES DE LISTAGEM ====================

describe('Listagem de Fichas', function () {

    test('pode listar fichas VEM', function () {
        Ficha::factory()->count(5)->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        $this->actingAs($this->user)
            ->get(route('vem.index'))
            ->assertStatus(200)
            ->assertViewIs('ficha.listVEM')
            ->assertViewHas('fichas');
    });

    test('pode buscar fichas VEM por nome', function () {
        Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'nom_candidato' => 'João',
        ]);

        Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'nom_candidato' => 'Maria Silva',
        ]);

        $this->actingAs($this->user)
            ->get(route('vem.index', ['search' => 'João']))
            ->assertStatus(200)
            ->assertSee('João')
            ->assertDontSee('Maria Silva');
    });

    test('pode filtrar fichas ECC por evento', function () {
        $outroEvento = Evento::factory()->create([
            'idt_movimento' => TipoMovimento::ECC
        ]);

        Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
        ]);

        Ficha::factory()->create([
            'idt_evento' => $outroEvento->idt_evento,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('ecc.index', ['evento' => $this->evento->idt_evento]))
            ->assertStatus(200);

        $fichas = $response->viewData('fichas');
        expect($fichas)->toHaveCount(1);
        expect($fichas->first()->idt_evento)->toBe($this->evento->idt_evento);
    });
});

// ==================== TESTES DE APROVAÇÃO ====================

describe('Aprovação de Fichas', function () {

    test('pode aprovar ficha VEM', function () {
        $ficha = Ficha::factory()->create([
            'idt_evento' => $this->evento->idt_evento,
            'ind_aprovado' => false,
        ]);

        $this->actingAs($this->user)
            ->get(route('vem.approve', $ficha->idt_ficha))
            ->assertRedirect(route('vem.index'))
            ->assertSessionHas('success');

        $ficha->refresh();
        expect($ficha->ind_aprovado)->toBeTrue();
    });

    test('pode desaprovar ficha ECC aprovada', function () {
        $evento = Evento::factory()->create([
            'idt_movimento' => TipoMovimento::ECC
        ]);

        $ficha = Ficha::factory()->create([
            'idt_evento' => $evento->idt_evento,
            'ind_aprovado' => true,
        ]);

        $this->actingAs($this->user)
            ->get(route('ecc.approve', $ficha->idt_ficha))
            ->assertRedirect(route('ecc.index'));

        $ficha->refresh();
        expect($ficha->ind_aprovado)->toBeFalse();
    });
});

// ==================== TESTES DE VALIDAÇÃO ====================

describe('Validações de Dados', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->evento = Evento::factory()->create();
    });

    test('valida email obrigatório e formato', function () {
        $this->actingAs($this->user)
            ->post(route('vem.store'), [
                'eml_candidato' => 'email-inválido',
            ])
            ->assertSessionHasErrors(['eml_candidato']);
    });

    test('valida data de nascimento', function () {
        $this->actingAs($this->user)
            ->post(route('ecc.store'), [
                'dat_nascimento' => 'data-inválida',
            ])
            ->assertSessionHasErrors(['dat_nascimento']);
    });

    test('valida consentimento obrigatório', function () {
        $this->actingAs($this->user)
            ->post(route('vem.store'), [
                'ind_consentimento' => false,
            ])
            ->assertSessionHasErrors(['ind_consentimento']);
    });

    test('valida existência do evento', function () {
        $this->actingAs($this->user)
            ->post(route('ecc.store'), [
                'idt_evento' => 99999,
            ])
            ->assertSessionHasErrors(['idt_evento']);
    });
});
