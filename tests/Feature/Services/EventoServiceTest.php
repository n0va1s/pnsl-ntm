<?php

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\Trabalhador;
use App\Services\EventoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new EventoService();
    $this->movimento = \App\Models\TipoMovimento::factory()->create();
    $this->pessoa = Pessoa::factory()->create();
});

describe('EventoService', function () {
    test('calcula pontuacao corretamente', function () {
        // Evento Participante (1 ponto)
        $eventoP = Evento::factory()->create([
            'tip_evento' => 'P', 
            'dat_inicio' => '2023-01-01',
            'idt_movimento' => $this->movimento->idt_movimento
        ]);
        Participante::factory()->create(['idt_pessoa' => $this->pessoa->idt_pessoa, 'idt_evento' => $eventoP->idt_evento]);

        // Evento Trabalhador (2 pontos) + Coordenador (2 pontos) = 4 pontos
        $eventoT = Evento::factory()->create([
            'tip_evento' => 'A', 
            'dat_inicio' => '2023-02-01',
            'idt_movimento' => $this->movimento->idt_movimento
        ]);
        Trabalhador::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $eventoT->idt_evento,
            'ind_coordenador' => true
        ]);

        // Bônus primeiro evento = 5 pontos
        // Total esperado: 5 (bonus) + 1 (P) + 4 (T+Coord) = 10 pontos

        $pontuacao = $this->service->calcularPontuacao($this->pessoa);
        expect($pontuacao)->toBe(10);
    });

    test('calcula ranking corretamente', function () {
        // Pessoa com mais pontos
        $pessoaTop = Pessoa::factory()->create();
        $eventoT = Evento::factory()->create([
            'tip_evento' => 'A', 
            'dat_inicio' => '2023-01-01',
            'idt_movimento' => $this->movimento->idt_movimento
        ]);
        Trabalhador::factory()->create(['idt_pessoa' => $pessoaTop->idt_pessoa, 'idt_evento' => $eventoT->idt_evento]); // 5+2=7 pts

        // Nossa pessoa sem eventos = 0 pts
        
        $rank = $this->service->calcularRanking($pessoaTop);
        expect($rank)->toBe(1);

        $rankPessoa = $this->service->calcularRanking($this->pessoa);
        expect($rankPessoa)->toBe(2);
    });

    test('upload de foto do evento', function () {
        Storage::fake('public');
        $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
        $file = UploadedFile::fake()->image('evento.jpg');

        $this->service->fotoUpload($evento, $file);

        $evento->refresh();
        expect($evento->foto)->not->toBeNull();
        Storage::disk('public')->assertExists($evento->foto->med_foto);
    });

    test('exclui evento com foto', function () {
        Storage::fake('public');
        $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
        $file = UploadedFile::fake()->image('evento.jpg');
        $this->service->fotoUpload($evento, $file);

        $this->service->excluirEventoComFoto($evento);

        $this->assertSoftDeleted('evento', ['idt_evento' => $evento->idt_evento]);
        // Note: The service deletes the model, but SoftDeletes might be enabled.
        // If SoftDeletes is enabled, the record still exists but deleted_at is set.
        // Also, the service deletes the photo record.
    });

    test('confirma participacao', function () {
        $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
        
        $this->service->confirmarParticipacao($evento, $this->pessoa);

        $this->assertDatabaseHas('participante', [
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $this->pessoa->idt_pessoa,
        ]);
    });
});
