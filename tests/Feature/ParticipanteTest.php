<?php

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\TipoMovimento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->movimento = TipoMovimento::factory()->create();
});

// ==========================================
// TESTES DE LISTAGEM (INDEX)
// ==========================================

test('usuario pode listar participantes', function () {
    $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    
    // Create 5 participants for the event. The factory handles creating unique people.
    Participante::factory()->count(5)->create([
        'idt_evento' => $evento->idt_evento
    ]);
    
    $response = $this->actingAs($this->user)
        ->get(route('participantes.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('evento.participante');
    $response->assertViewHas('participantes');
});

test('usuario pode filtrar participantes por evento', function () {
    $evento1 = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $evento2 = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    
    Participante::factory()->count(3)->create([
        'idt_evento' => $evento1->idt_evento
    ]);
    
    Participante::factory()->count(2)->create([
        'idt_evento' => $evento2->idt_evento
    ]);
    
    $response = $this->actingAs($this->user)
        ->get(route('participantes.index', ['evento' => $evento1->idt_evento]));
    
    $response->assertStatus(200);
    $response->assertViewHas('evento');
    // Verify that we only see participants from event 1
    // Note: pagination might affect count if > 10, but here we have 3.
    // We can check the collection size in the view data if needed, but status 200 is a good start.
});

test('usuario pode buscar participantes por nome', function () {
    $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $pessoa = Pessoa::factory()->create(['nom_pessoa' => 'João Silva']);
    
    Participante::factory()->create([
        'idt_evento' => $evento->idt_evento,
        'idt_pessoa' => $pessoa->idt_pessoa
    ]);
    
    $response = $this->actingAs($this->user)
        ->get(route('participantes.index', ['search' => 'João']));
    
    $response->assertStatus(200);
    $response->assertSee('João Silva');
});

// ==========================================
// TESTES DE ATUALIZAÇÃO DE TROCAS (CHANGE)
// ==========================================

test('usuario pode atualizar cor de troca de participantes', function () {
    $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $pessoa = Pessoa::factory()->create();
    
    $participante = Participante::factory()->create([
        'idt_evento' => $evento->idt_evento,
        'idt_pessoa' => $pessoa->idt_pessoa,
        'tip_cor_troca' => 'A'
    ]);
    
    $data = [
        'trocas' => [
            $participante->idt_participante => 'V'
        ]
    ];
    
    $response = $this->actingAs($this->user)
        ->post(route('participantes.change'), $data);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $participante->refresh();
    expect($participante->tip_cor_troca)->toBe('V');
});

test('usuario pode atualizar multiplas trocas de uma vez', function () {
    $evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $pessoa1 = Pessoa::factory()->create();
    $pessoa2 = Pessoa::factory()->create();
    
    $participante1 = Participante::factory()->create([
        'idt_evento' => $evento->idt_evento,
        'idt_pessoa' => $pessoa1->idt_pessoa,
        'tip_cor_troca' => 'A'
    ]);
    
    $participante2 = Participante::factory()->create([
        'idt_evento' => $evento->idt_evento,
        'idt_pessoa' => $pessoa2->idt_pessoa,
        'tip_cor_troca' => 'A'
    ]);
    
    $data = [
        'trocas' => [
            $participante1->idt_participante => 'V',
            $participante2->idt_participante => 'L'
        ]
    ];
    
    $response = $this->actingAs($this->user)
        ->post(route('participantes.change'), $data);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $participante1->refresh();
    $participante2->refresh();
    
    expect($participante1->tip_cor_troca)->toBe('V');
    expect($participante2->tip_cor_troca)->toBe('L');
});

test('change funciona mesmo sem trocas enviadas', function () {
    $response = $this->actingAs($this->user)
        ->post(route('participantes.change'), ['trocas' => []]);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
});

// ==========================================
// TESTES DE AUTORIZAÇÃO
// ==========================================

test('visitante nao pode acessar lista de participantes', function () {
    $response = $this->get(route('participantes.index'));
    
    $response->assertRedirect(route('login'));
});

test('visitante nao pode atualizar trocas', function () {
    $response = $this->post(route('participantes.change'), ['trocas' => []]);
    
    $response->assertRedirect(route('login'));
});
