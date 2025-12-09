<?php

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\TipoMovimento;
use App\Models\Trabalhador;
use App\Services\EventoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new EventoService();
});

test('calculates score correctly', function () {
    $pessoa = Pessoa::factory()->create();
    $movimento = TipoMovimento::factory()->create();

    // 1. First event (Bonus +5) + Participant (+1) = 6 points
    $evento1 = Evento::factory()->create([
        'idt_movimento' => $movimento->idt_movimento,
        'tip_evento' => 'P',
        'dat_inicio' => Carbon::create(2020, 1, 1),
    ]);
    Participante::factory()->create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento1->idt_evento,
    ]);

    // 2. Worker (+2) = 2 points
    $evento2 = Evento::factory()->create([
        'idt_movimento' => $movimento->idt_movimento,
        'tip_evento' => 'A',
        'dat_inicio' => Carbon::create(2020, 2, 1),
    ]);
    Trabalhador::factory()->create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento2->idt_evento,
        'ind_coordenador' => false,
    ]);

    // 3. Worker (+2) + Coordinator (+2) = 4 points
    $evento3 = Evento::factory()->create([
        'idt_movimento' => $movimento->idt_movimento,
        'tip_evento' => 'A',
        'dat_inicio' => Carbon::create(2020, 3, 1),
    ]);
    Trabalhador::factory()->create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento3->idt_evento,
        'ind_coordenador' => true,
    ]);

    // 4. Challenge (+3) = 3 points
    $evento4 = Evento::factory()->create([
        'idt_movimento' => $movimento->idt_movimento,
        'tip_evento' => 'D',
        'dat_inicio' => Carbon::create(2020, 4, 1),
    ]);
    Participante::factory()->create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento4->idt_evento,
    ]);

    // Total expected: 6 + 2 + 4 + 3 = 15
    $score = $this->service->calcularPontuacao($pessoa);

    expect($score)->toBe(15);
});

test('calculates ranking correctly', function () {
    $movimento = TipoMovimento::factory()->create();

    // Person 1: 1 event (Participant) = 1 + 5 = 6 points
    $pessoa1 = Pessoa::factory()->create();
    $evento1 = Evento::factory()->create(['tip_evento' => 'P', 'dat_inicio' => Carbon::create(2020, 1, 1)]);
    Participante::factory()->create(['idt_pessoa' => $pessoa1->idt_pessoa, 'idt_evento' => $evento1->idt_evento]);

    // Person 2: 2 events (Participant) = (1+5) + 1 = 7 points
    $pessoa2 = Pessoa::factory()->create();
    $evento2 = Evento::factory()->create(['tip_evento' => 'P', 'dat_inicio' => Carbon::create(2020, 1, 1)]);
    $evento3 = Evento::factory()->create(['tip_evento' => 'P', 'dat_inicio' => Carbon::create(2020, 2, 1)]);
    Participante::factory()->create(['idt_pessoa' => $pessoa2->idt_pessoa, 'idt_evento' => $evento2->idt_evento]);
    Participante::factory()->create(['idt_pessoa' => $pessoa2->idt_pessoa, 'idt_evento' => $evento3->idt_evento]);

    // Person 3: Same as Person 1 = 6 points
    $pessoa3 = Pessoa::factory()->create();
    $evento4 = Evento::factory()->create(['tip_evento' => 'P', 'dat_inicio' => Carbon::create(2020, 1, 1)]);
    Participante::factory()->create(['idt_pessoa' => $pessoa3->idt_pessoa, 'idt_evento' => $evento4->idt_evento]);

    expect($this->service->calcularRanking($pessoa2))->toBe(1);
    expect($this->service->calcularRanking($pessoa1))->toBe(2);
    expect($this->service->calcularRanking($pessoa3))->toBe(2);
});

test('timeline structure', function () {
    $pessoa = Pessoa::factory()->create();
    $movimento = TipoMovimento::factory()->create();

    $evento = Evento::factory()->create([
        'idt_movimento' => $movimento->idt_movimento,
        'dat_inicio' => Carbon::create(2023, 5, 15),
        'tip_evento' => 'A'
    ]);

    Trabalhador::factory()->create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
    ]);

    $timeline = $this->service->getEventosTimeline($pessoa);

    expect($timeline)->toBeArray();
    expect($timeline)->not->toBeEmpty();
    
    // Check decade grouping
    expect($timeline[0]['decade'])->toBe('2020s');
    
    // Check year grouping
    $years = $timeline[0]['years'];
    expect($years[0]['year'])->toBe(2023);
    
    // Check event details
    $events = $years[0]['events'];
    expect($events)->toHaveCount(1);
    expect($events[0]['type'])->toBe('Trabalhador');
    expect($events[0]['event']->idt_evento)->toBe($evento->idt_evento);
});
