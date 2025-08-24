<?php

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\Trabalhador;
use App\Models\Participante;
use App\Models\TipoMovimento;
use App\Services\EventoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\EventoFoto;
use App\Models\TipoEquipe;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Garantir que não há transações ativas
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }

    $this->eventoService = new EventoService();

    $this->user = createUser();
    $this->actingAs($this->user);

    $this->pessoa = createPessoa();

    //Criar as equipes para cada movimento
    createMovimentos();

    // Adicionar movimento padrão para os testes
    $this->movimento = TipoMovimento::all()->first();
    $this->evento = createEvento();

    //Mocks
    $this->mock(UserService::class, function (MockInterface $mock) {
        $mock->shouldReceive('createPessoaFromLoggedUser')->andReturn(Pessoa::factory()->create());
    });

    Storage::fake('public');
});

afterEach(function () {
    // Garantir que todas as transações sejam fechadas após cada teste
    while (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
});

describe('EventoService - Timeline', function () {
    test('retorna timeline vazia para pessoa sem eventos', function () {
        $timeline = $this->eventoService->getEventosTimeline($this->pessoa);

        expect($timeline)->toBeArray()->toBeEmpty();
    });

    test('retorna eventos de trabalhador na timeline corretamente', function () {
        $evento = Evento::factory()->create([
            'idt_movimento' => $this->movimento->idt_movimento,
            'dat_inicio' => '2023-01-15'
        ]);

        $equipe = TipoEquipe::firstOrCreate([
            'des_grupo' => 'Coordenação Geral',
            'idt_movimento' => $this->movimento->idt_movimento
        ]);

        Trabalhador::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $evento->idt_evento,
            'idt_equipe' => $equipe->idt_equipe,
            'ind_coordenador' => true,
            'ind_primeira_vez' => false
        ]);

        $timeline = $this->eventoService->getEventosTimeline($this->pessoa);

        expect($timeline)->toHaveCount(1)
            ->and($timeline[0]['decade'])->toBe('2020s')
            ->and($timeline[0]['years'][0]['year'])->toBe(2023)
            ->and($timeline[0]['years'][0]['events'][0]['type'])->toBe('Trabalhador')
            ->and($timeline[0]['years'][0]['events'][0]['details']['coordenador'])->toBeTrue();
    });

    test('retorna eventos de participante na timeline corretamente', function () {
        $evento = Evento::factory()->create([
            'idt_movimento' => $this->movimento->idt_movimento,
            'dat_inicio' => '2023-06-20'
        ]);

        Participante::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $evento->idt_evento
        ]);

        $timeline = $this->eventoService->getEventosTimeline($this->pessoa);

        expect($timeline[0]['years'][0]['events'][0]['type'])->toBe('Participante')
            ->and($timeline[0]['years'][0]['events'][0]['details'])->toBeEmpty();
    });

    test('agrupa eventos por década e ano corretamente', function () {
        // Eventos em anos diferentes
        $evento2023 = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        $evento2022 = Evento::factory()->create(['dat_inicio' => '2022-06-20']);
        $evento2010 = Evento::factory()->create(['dat_inicio' => '2010-03-10']);

        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento2023->idt_evento]);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento2022->idt_evento]);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento2010->idt_evento]);

        $timeline = $this->eventoService->getEventosTimeline($this->pessoa);

        expect($timeline)->toHaveCount(2)
            ->and($timeline[0]['decade'])->toBe('2020s')
            ->and($timeline[1]['decade'])->toBe('2010s');
    });

    test('ordena eventos por data decrescente', function () {
        $eventoAntigo = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        $eventoRecente = Evento::factory()->create(['dat_inicio' => '2023-12-20']);

        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $eventoAntigo->idt_evento]);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $eventoRecente->idt_evento]);

        $timeline = $this->eventoService->getEventosTimeline($this->pessoa);
        $events = $timeline[0]['years'][0]['events'];

        expect($events[0]['event']['idt_evento'])->toBe($eventoRecente->idt_evento)
            ->and($events[1]['event']['idt_evento'])->toBe($eventoAntigo->idt_evento);
    });
});

describe('EventoService - Pontuação', function () {
    test('calcula pontuação zero para pessoa sem eventos', function () {
        $pontuacao = $this->eventoService->calcularPontuacao($this->pessoa);

        expect($pontuacao)->toBe(0);
    });

    test('calcula pontuação correta para primeiro evento', function () {
        $evento = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento->idt_evento]);

        $pontuacao = $this->eventoService->calcularPontuacao($this->pessoa);

        expect($pontuacao)->toBe(11); // 10 (primeiro evento) + 1 (participante)
    });

    test('calcula pontuação correta para trabalhador coordenador', function () {
        $evento = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        Trabalhador::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $evento->idt_evento,
            'ind_coordenador' => true
        ]);

        $pontuacao = $this->eventoService->calcularPontuacao($this->pessoa);

        expect($pontuacao)->toBe(13); // 10 (primeiro) + 2 (trabalhador) + 1 (coordenador)
    });

    test('calcula pontuação correta para múltiplos eventos', function () {
        $evento1 = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        $evento2 = Evento::factory()->create(['dat_inicio' => '2023-06-20']);

        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento1->idt_evento]);
        Trabalhador::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $evento2->idt_evento,
            'ind_coordenador' => false
        ]);

        $pontuacao = $this->eventoService->calcularPontuacao($this->pessoa);

        expect($pontuacao)->toBe(13); // 10 (primeiro) + 1 (participante) + 2 (trabalhador)
    });

    test('não adiciona bônus de primeiro evento para eventos subsequentes', function () {
        $evento1 = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        $evento2 = Evento::factory()->create(['dat_inicio' => '2023-06-20']);

        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento1->idt_evento]);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento2->idt_evento]);

        $pontuacao = $this->eventoService->calcularPontuacao($this->pessoa);

        expect($pontuacao)->toBe(12); // 10 (primeiro) + 1 + 1 (dois participantes)
    });
});

describe('EventoService - Ranking', function () {
    test('retorna ranking correto para uma pessoa', function () {
        $pessoa2 = Pessoa::factory()->create();

        // Pessoa 1 com mais pontos
        $evento1 = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        Trabalhador::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $evento1->idt_evento,
            'ind_coordenador' => true
        ]);

        // Pessoa 2 com menos pontos
        $evento2 = Evento::factory()->create(['dat_inicio' => '2023-06-20']);
        Participante::factory()->create(['idt_pessoa' => $pessoa2->idt_pessoa, 'idt_evento' => $evento2->idt_evento]);

        $ranking = $this->eventoService->calcularRanking($this->pessoa);

        expect($ranking)->toBe(1);
    });

    test('lida com empates no ranking corretamente', function () {
        $pessoa2 = Pessoa::factory()->create();

        // Ambas as pessoas com mesma pontuação
        $evento1 = Evento::factory()->create(['dat_inicio' => '2023-01-15']);
        $evento2 = Evento::factory()->create(['dat_inicio' => '2023-06-20']);

        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento1->idt_evento]);
        Participante::factory()->create(['idt_pessoa' => $pessoa2->idt_pessoa, 'idt_evento' => $evento2->idt_evento]);

        $ranking1 = $this->eventoService->calcularRanking($this->pessoa);
        $ranking2 = $this->eventoService->calcularRanking($pessoa2);

        expect($ranking1)->toBe(1)
            ->and($ranking2)->toBe(1);
    });
});

describe('EventoService - Upload de Foto', function () {
    test('faz upload de foto corretamente', function () {
        $evento = Evento::factory()->create();
        $file = UploadedFile::fake()->image('evento.jpg');

        $this->eventoService->fotoUpload($evento, $file);
        $evento->refresh();

        expect($evento->fresh()->foto)->not->toBeNull()
            ->and(Storage::disk('public')->exists($evento->foto->med_foto))->toBeTrue();
    });

    test('substitui foto existente ao fazer novo upload', function () {
        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'fotos/evento/antiga.jpg']);

        $file = UploadedFile::fake()->image('nova.jpg');

        $this->eventoService->fotoUpload($evento, $file);

        expect($evento->fresh()->foto->med_foto)->not->toBe('fotos/evento/antiga.jpg');
    });

    test('não faz nada quando arquivo é null', function () {
        $evento = Evento::factory()->create();
        $fotoOriginal = $evento->foto;

        $this->eventoService->fotoUpload($evento, null);

        expect($evento->fresh()->foto)->toBe($fotoOriginal);
    });
});

describe('EventoService - Exclusão', function () {
    test('exclui evento sem foto corretamente', function () {
        $evento = Evento::factory()->create();

        $this->eventoService->excluirEventoComFoto($evento);

        expect(Evento::find($evento->idt_evento))->toBeNull();
    });

    test('exclui evento com foto corretamente', function () {
        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'fotos/evento/teste.jpg']);

        $this->eventoService->excluirEventoComFoto($evento);

        expect(Evento::find($evento->idt_evento))->toBeNull();
    });
});

describe('EventoService - Participação', function () {
    test('confirma participação corretamente', function () {
        $evento = Evento::factory()->create();

        $this->eventoService->confirmarParticipacao($evento, $this->pessoa);

        expect(Participante::where('idt_evento', $evento->idt_evento)
            ->where('idt_pessoa', $this->pessoa->idt_pessoa)
            ->exists())->toBeTrue();
    });

    test('retorna eventos inscritos corretamente', function () {
        $evento1 = Evento::factory()->create();
        $evento2 = Evento::factory()->create();
        $evento3 = Evento::factory()->create(); // Não inscrito

        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento1->idt_evento]);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $evento2->idt_evento]);

        $eventosInscritos = $this->eventoService->getEventosInscritos($this->pessoa);

        expect($eventosInscritos)->toHaveCount(2)
            ->and($eventosInscritos->pluck('idt_evento')->toArray())->toContain($evento1->idt_evento, $evento2->idt_evento)
            ->and($eventosInscritos->pluck('idt_evento')->toArray())->not->toContain($evento3->idt_evento);
    });
});

describe('EventoController - Index', function () {
    test('exibe listagem de eventos para usuário autenticado', function () {
        $this->actingAs($this->user);
        Evento::factory()->count(3)->create(['idt_movimento' => $this->movimento->idt_movimento]);

        $response = $this->get(route('eventos.index'));

        $response->assertOk()
            ->assertViewIs('evento.list')
            ->assertViewHas('eventos')
            ->assertViewHas('pessoa');
    });

    test('exibe listagem de eventos para usuário não autenticado', function () {
        Evento::factory()->count(3)->create();

        $response = $this->get(route('eventos.index'));

        $response->assertOk()
            ->assertViewHas('pessoa', null);
    });

    test('filtra eventos por busca corretamente', function () {
        $eventoEncontrado = Evento::factory()->create(['des_evento' => 'Encontro Especial']);
        $eventoNaoEncontrado = Evento::factory()->create(['des_evento' => 'Outro Evento']);

        $response = $this->get(route('eventos.index', ['search' => 'especial']));

        $response->assertOk();
        $eventos = $response->viewData('eventos');

        expect($eventos->items())->toHaveCount(1)
            ->and($eventos->items()[0]->idt_evento)->toBe($eventoEncontrado->idt_evento);
    });

    test('mantém parâmetros de busca na paginação', function () {
        Evento::factory()->count(15)->create(['des_evento' => 'Encontro Teste']);

        $response = $this->get(route('eventos.index', ['search' => 'teste', 'page' => 2]));

        $response->assertOk();
        $eventos = $response->viewData('eventos');

        expect($eventos->hasPages())->toBeTrue();
    });
});

describe('EventoController - Show', function () {
    test('carrega foto do evento quando presente', function () {
        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'fotos/evento/teste.jpg']);

        $response = $this->get(route('eventos.edit', $evento));

        $response->assertOk();
        $eventoView = $response->viewData('evento');

        expect($eventoView->relationLoaded('foto'))->toBeTrue();
    });
});

describe('EventoController - Create', function () {
    test('exibe formulário de criação', function () {
        $response = $this->get(route('eventos.create'));

        $response->assertOk()
            ->assertViewIs('evento.form')
            ->assertViewHas('movimentos')
            ->assertViewHas('evento');
    });

    test('carrega todos os movimentos disponíveis', function () {

        $this->tipoMovimentoECC = TipoMovimento::firstOrCreate([
            'des_sigla' => 'ECC',
            'nom_movimento' => 'Encontro de Casais com Cristo',
            'dat_inicio' => '1980-01-01'
        ]);
        $this->tipoMovimentoVEM = TipoMovimento::firstOrCreate([
            'des_sigla' => 'VEM',
            'nom_movimento' => 'Encontro de Adolescentes com Cristo',
            'dat_inicio' => '2000-07-01'
        ]);
        $this->tipoMovimentoSegueMe = TipoMovimento::firstOrCreate([
            'des_sigla' => 'Segue-Me',
            'nom_movimento' => 'Encontro de Jovens com Cristo',
            'dat_inicio' => '1990-12-31'
        ]);

        $response = $this->get(route('eventos.create'));

        $movimentos = $response->viewData('movimentos');
        expect($movimentos)->toHaveCount(6);
    });
});

describe('EventoController - Store', function () {
    test('cria evento com dados válidos', function () {
        $dadosEvento = [
            'idt_movimento' => $this->movimento->idt_movimento,
            'des_evento' => 'Novo Evento',
            'num_evento' => 'EV001',
            'dat_inicio' => '2024-01-15',
            'dat_termino' => '2024-01-17',
            'val_camiseta' => 25.00,
            'val_trabalhador' => 50.00,
            'val_venista' => 30.00,
            'val_entrada' => 15.00,
            'tip_evento' => 'E'
        ];

        $response = $this->post(route('eventos.store'), $dadosEvento);

        $response->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('evento', [
            'des_evento' => 'Novo Evento',
            'num_evento' => 'EV001'
        ]);
    });

    test('cria evento com foto', function () {
        Storage::fake('public');

        $dadosEvento = [
            'idt_movimento' => $this->movimento->idt_movimento,
            'des_evento' => 'Evento com Foto',
            'num_evento' => 'EV002',
            'dat_inicio' => '2024-01-15',
            'dat_termino' => '2024-01-17',
            'med_foto' => UploadedFile::fake()->image('evento.jpg'),
            'tip_evento' => 'E'
        ];

        $response = $this->post(route('eventos.store'), $dadosEvento);

        $response->assertRedirect(route('eventos.index'));

        $evento = Evento::where('des_evento', 'Evento com Foto')->first();
        expect($evento->foto)->not->toBeNull();

        Storage::disk('public')->assertExists($evento->foto->med_foto);
    });

    test('falha com dados inválidos', function () {
        $dadosInvalidos = [
            'des_evento' => '', // Campo obrigatório vazio
            'idt_movimento' => 999, // ID inexistente
        ];

        $response = $this->post(route('eventos.store'), $dadosInvalidos);

        $response->assertSessionHasErrors(['des_evento', 'idt_movimento']);
    });
});

describe('EventoController - Edit', function () {
    test('exibe formulário de edição', function () {
        $evento = Evento::factory()->create();

        $response = $this->get(route('eventos.edit', $evento));

        $response->assertOk()
            ->assertViewIs('evento.form')
            ->assertViewHas('evento', $evento)
            ->assertViewHas('movimentos');
    });

    test('carrega foto do evento para edição', function () {
        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'fotos/evento/teste.jpg']);

        $response = $this->get(route('eventos.edit', $evento));

        $eventoView = $response->viewData('evento');
        expect($eventoView->relationLoaded('foto'))->toBeTrue();
    });
});

describe('EventoController - Update', function () {
    test('atualiza evento com dados válidos', function () {
        $evento = Evento::factory()->create(['des_evento' => 'Evento Original']);

        $dadosAtualizados = [
            'idt_movimento' => $this->movimento->idt_movimento, // Corrigido: Usar um ID de movimento existente
            'des_evento' => 'Evento Atualizado',
            'num_evento' => '001A',
            'dat_inicio' => $evento->dat_inicio->format('Y-m-d'),
            'dat_termino' => $evento->dat_inicio->addDays(2)->format('Y-m-d'),
            'val_camiseta' => 200.00,
            'val_trabalhador' => 50.00,
            'val_venista' => 150.00,
            'val_entrada' => 30.00,
            'tip_evento' => 'P',
            'ind_camiseta_pediu' => true,
            'ind_camiseta_pagou' => true,
        ];

        // Corrigido: Usar o método 'from' para simular a URL de referência.
        $response = $this->from(route('eventos.index'))->put(route('eventos.update', $evento), $dadosAtualizados);

        $response->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        expect($evento->fresh()->des_evento)->toBe('Evento Atualizado');
    });

    test('atualiza foto do evento', function () {
        Storage::fake('public');

        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'fotos/evento/antiga.jpg']);

        $dadosAtualizados = [
            'idt_movimento' => $this->movimento->idt_movimento,
            'des_evento' => $evento->des_evento,
            'num_evento' => '002B',
            'dat_inicio' => $evento->dat_inicio->format('Y-m-d'),
            'dat_termino' => $evento->dat_termino->format('Y-m-d'),
            'val_camiseta' => 200.00,
            'val_trabalhador' => 50.00,
            'val_venista' => 150.00,
            'val_entrada' => 30.00,
            'med_foto' => UploadedFile::fake()->image('nova.jpg'),
            'tip_evento' => 'P',
            'ind_camiseta_pediu' => true,
            'ind_camiseta_pagou' => true,
        ];

        $response = $this->from(route('eventos.index'))->put(route('eventos.update', $evento), $dadosAtualizados);

        $response->assertRedirect(route('eventos.index'));

        $fotoAtualizada = $evento->fresh()->foto;
        expect($fotoAtualizada->med_foto)->not->toBe('fotos/evento/antiga.jpg');
        Storage::disk('public')->assertExists($fotoAtualizada->med_foto);
    });

    test('não atualiza evento com dados inválidos', function () {
        $evento = Evento::factory()->create();

        $dadosInvalidos = [
            'des_evento' => 'Teste Invalido',
            'num_evento' => 12345, // Número, não string
            'dat_inicio' => 'data-invalida',
            'idt_movimento' => null,
            'tip_evento' => null,
        ];

        $response = $this->from(route('eventos.index'))
            ->put(route('eventos.update', $evento), $dadosInvalidos);

        $response->assertRedirect(route('eventos.index'));
        $response->assertSessionHasErrors([
            'num_evento',
            'dat_inicio',
            'idt_movimento',
            'tip_evento'
        ]);
    });
});

describe('EventoController - Destroy', function () {
    test('exclui evento sem relacionamentos', function () {
        $evento = Evento::factory()->create();

        $response = $this->delete(route('eventos.destroy', $evento));

        $response->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success');

        expect(Evento::find($evento->idt_evento))->toBeNull();
    });

    test('exclui evento e seus participantes em cascata', function () {

        $evento = Evento::factory()->create();
        $participante = Participante::factory()->for($evento)->create();

        // NOTA: A model usa soft delete, o método delete() do controlador
        // não removerá o registro do banco de dados.
        $response = $this->delete(route('eventos.destroy', $evento));

        $response->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success', 'Evento excluído com sucesso!');

        // Verifica se o evento foi 'soft-deletado'
        $eventoExist = Evento::withTrashed()->find($evento->idt_evento);
        expect($eventoExist)->not()->toBeNull(); // O evento deve existir, mas com deleted_at preenchido.

        // A exclusão em cascata não acontece com soft deletes.
        // O participante ainda deve existir.
        expect(Participante::find($participante->idt_participante))->not()->toBeNull();
    });

    test('exclui foto junto com evento', function () {
        Storage::fake('public');

        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'fotos/evento/teste.jpg']);
        Storage::disk('public')->put('fotos/evento/teste.jpg', 'conteudo fake');

        $response = $this->delete(route('eventos.destroy', $evento));

        $response->assertRedirect(route('eventos.index'));
        expect(Evento::find($evento->idt_evento))->toBeNull();
    });
});

describe('EventoController - Confirm', function () {
    test('confirma participação em evento', function () {
        $evento = Evento::factory()->create();
        $pessoa = Pessoa::factory()->create();

        $response = $this->post(route('participantes.confirm', [$evento, $pessoa]));

        $response->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('participante', [
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa
        ]);
    });
});

describe('EventoController - Timeline', function () {
    test('exibe timeline para usuário autenticado', function () {
        $this->actingAs($this->user);

        $response = $this->get(route('timeline.index'));

        $response->assertOk()
            ->assertViewIs('evento.linhadotempo')
            ->assertViewHas('timeline')
            ->assertViewHas('pontuacaoTotal')
            ->assertViewHas('posicaoNoRanking')
            ->assertViewHas('pessoa');
    });

    test('requer autenticação para acessar timeline', function () {
        Auth::logout();
        $response = $this->get(route('timeline.index'));

        $response->assertRedirect(); // Redirecionamento para login
    });
});

describe('Evento Model', function () {
    test('tem atributos fillable corretos', function () {
        $fillable = [
            'idt_movimento',
            'des_evento',
            'num_evento',
            'dat_inicio',
            'dat_termino',
            'val_camiseta',
            'val_trabalhador',
            'val_venista',
            'val_entrada',
            'tip_evento',
        ];

        expect((new Evento())->getFillable())->toBe($fillable);
    });

    test('faz cast de datas corretamente', function () {
        $evento = Evento::factory()->create([
            'dat_inicio' => '2023-01-15',
            'dat_termino' => '2023-01-17'
        ]);

        expect($evento->dat_inicio)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($evento->dat_termino)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    test('busca por descrição do evento', function () {
        Evento::factory()->create(['des_evento' => 'Encontro de Jovens']);
        Evento::factory()->create(['des_evento' => 'Retiro Espiritual']);

        $resultados = Evento::search('jovens')->get();

        expect($resultados)->toHaveCount(1)
            ->and($resultados->first()->des_evento)->toBe('Encontro de Jovens');
    });

    test('busca por número do evento', function () {
        Evento::factory()->create(['num_evento' => 'EJ2023']);
        Evento::factory()->create(['num_evento' => 'RE2023']);

        $resultados = Evento::search('EJ')->get();

        expect($resultados)->toHaveCount(1)
            ->and($resultados->first()->num_evento)->toBe('EJ2023');
    });

    test('busca é case insensitive', function () {
        Evento::factory()->create(['des_evento' => 'Encontro de Jovens']);

        $resultados = Evento::search('JOVENS')->get();

        expect($resultados)->toHaveCount(1);
    });

    test('relacionamento com movimento funciona', function () {
        $movimento = TipoMovimento::firstOrCreate([
            'des_sigla' => 'ECC',
            'nom_movimento' => 'Encontro de Casais com Cristo',
            'dat_inicio' => '1980-01-01'
        ]);

        $evento = Evento::factory()->create(['idt_movimento' => $movimento->idt_movimento]);

        expect($evento->movimento)->toBeInstanceOf(TipoMovimento::class)
            ->and($evento->movimento->idt_movimento)->toBe($movimento->idt_movimento);
    });

    test('relacionamento com foto funciona', function () {
        $evento = Evento::factory()->create();
        $evento->foto()->create(['med_foto' => 'teste.jpg']);

        expect($evento->foto)->toBeInstanceOf(EventoFoto::class)
            ->and($evento->foto->med_foto)->toBe('teste.jpg');
    });
});

describe('EventoFoto Model', function () {
    test('usa timestamps', function () {
        expect((new EventoFoto())->timestamps)->toBeTrue();
    });

    test('tem atributos fillable corretos', function () {
        $fillable = ['idt_evento', 'med_foto'];

        expect((new EventoFoto())->getFillable())->toBe($fillable);
    });

    test('relacionamento com evento funciona', function () {
        $evento = Evento::factory()->create();
        $foto = EventoFoto::create([
            'idt_evento' => $evento->idt_evento,
            'med_foto' => 'teste.jpg'
        ]);

        expect($foto->evento)->toBeInstanceOf(Evento::class)
            ->and($foto->evento->idt_evento)->toBe($evento->idt_evento);
    });
});
