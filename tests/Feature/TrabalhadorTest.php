<?php

use App\Models\User;
use App\Models\Trabalhador;
use App\Models\Pessoa;
use App\Models\TipoEquipe;
use App\Models\Evento;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Cria um usuário para autenticação. 
    // O UserObserver (ou boot do model) cria automaticamente uma Pessoa vinculada.
    $this->user = User::factory()->create();
    $this->pessoa = $this->user->pessoa;
});

// ==========================================
// TESTES DE LISTAGEM (INDEX)
// ==========================================

test('usuario autenticado pode acessar listagem de trabalhadores', function () {
    $response = $this->actingAs($this->user)->get('/trabalhadores');
    
    $response->assertStatus(200);
    $response->assertViewIs('trabalhador.list');
});

test('listagem exibe trabalhadores cadastrados', function () {
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    
    // Cria trabalhadores com pessoas diferentes para evitar erro de unique constraint
    Trabalhador::factory()->count(5)->create([
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe
    ]);
    
    $response = $this->actingAs($this->user)->get('/trabalhadores');
    
    $response->assertStatus(200);
    $response->assertViewHas('trabalhadores');
    expect($response['trabalhadores'])->toHaveCount(5);
});

test('listagem pode ser filtrada por evento', function () {
    $evento1 = Evento::factory()->create();
    $evento2 = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create(); 
    
    Trabalhador::factory()->create(['idt_evento' => $evento1->idt_evento, 'idt_equipe' => $equipe->idt_equipe]);
    Trabalhador::factory()->create(['idt_evento' => $evento2->idt_evento, 'idt_equipe' => $equipe->idt_equipe]);
    
    $response = $this->actingAs($this->user)
        ->get('/trabalhadores?evento=' . $evento1->idt_evento);
    
    $response->assertStatus(200);
    expect($response['trabalhadores'])->toHaveCount(1);
    expect($response['trabalhadores']->first()->idt_evento)->toBe($evento1->idt_evento);
});

// ==========================================
// TESTES DE CANDIDATURA (STORE - VOLUNTARIADO)
// ==========================================

test('usuario pode acessar formulario de candidatura', function () {
    $response = $this->actingAs($this->user)->get('/trabalhadores/create');
    
    $response->assertStatus(200);
    $response->assertViewIs('trabalhador.form');
});

test('usuario pode se candidatar como voluntario', function () {
    $evento = Evento::factory()->create();
    $equipe1 = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    $equipe2 = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    
    $data = [
        'idt_evento' => $evento->idt_evento,
        'equipes' => [
            $equipe1->idt_equipe => [
                'selecionado' => '1',
                'habilidade' => 'Tenho experiência com vendas e atendimento.',
            ],
            // Equipe 2 não enviada ou enviada sem 'selecionado' => '1'
        ],
    ];
    
    $response = $this->actingAs($this->user)->post('/trabalhadores', $data);
    
    $response->assertRedirect(route('eventos.index'));
    $response->assertSessionHas('success');
    
    // Verifica se criou o voluntário
    $this->assertDatabaseHas('voluntario', [
        'idt_pessoa' => $this->pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe1->idt_equipe,
        'txt_habilidade' => 'Tenho experiência com vendas e atendimento.',
    ]);
    
    // Verifica se NÃO criou para a equipe não selecionada
    $this->assertDatabaseMissing('voluntario', [
        'idt_pessoa' => $this->pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe2->idt_equipe,
    ]);
});

test('validacao de candidatura: habilidade curta demais', function () {
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    
    $data = [
        'idt_evento' => $evento->idt_evento,
        'equipes' => [
            $equipe->idt_equipe => [
                'selecionado' => '1',
                'habilidade' => 'Oi', // Muito curto
            ],
        ],
    ];
    
    $response = $this->actingAs($this->user)->post('/trabalhadores', $data);
    
    $response->assertSessionHasErrors(['equipes']);
});

test('validacao de candidatura: nenhuma equipe selecionada', function () {
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    
    $data = [
        'idt_evento' => $evento->idt_evento,
        'equipes' => [], // Nenhuma equipe
    ];
    
    $response = $this->actingAs($this->user)->post('/trabalhadores', $data);
    
    $response->assertSessionHasErrors(['equipes']);
});

// ==========================================
// TESTES DE MONTAGEM E CONFIRMAÇÃO
// ==========================================

test('admin pode ver lista de montagem de equipes', function () {
    $evento = Evento::factory()->create();
    
    $response = $this->actingAs($this->user)->get('/montagem?evento=' . $evento->idt_evento);
    
    $response->assertStatus(200);
    $response->assertViewIs('evento.montagem');
    $response->assertViewHas('voluntarios');
});

test('admin pode confirmar um voluntario como trabalhador', function () {
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    
    // Cria um voluntário
    $voluntario = Voluntario::create([
        'idt_pessoa' => $this->pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe,
        'txt_habilidade' => 'Habilidade teste suficiente',
    ]);
    
    $data = [
        'idt_voluntario' => $voluntario->idt_voluntario,
        'idt_equipe' => $equipe->idt_equipe,
        'ind_coordenador' => '1',
        'ind_primeira_vez' => '0',
    ];
    
    $response = $this->actingAs($this->user)->post('/montagem', $data);
    
    $response->assertRedirect(route('eventos.index', ['evento' => $evento->idt_evento]));
    $response->assertSessionHas('success');
    
    // Verifica se criou o trabalhador
    $this->assertDatabaseHas('trabalhador', [
        'idt_pessoa' => $this->pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe,
        'ind_coordenador' => true,
    ]);
    
    // Verifica se atualizou o voluntário com o ID do trabalhador
    $trabalhador = Trabalhador::where('idt_pessoa', $this->pessoa->idt_pessoa)
        ->where('idt_evento', $evento->idt_evento)
        ->first();
        
    $this->assertDatabaseHas('voluntario', [
        'idt_voluntario' => $voluntario->idt_voluntario,
        'idt_trabalhador' => $trabalhador->idt_trabalhador,
    ]);
});

// ==========================================
// TESTES DE AVALIAÇÃO
// ==========================================

test('admin pode salvar avaliacao de trabalhador', function () {
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create(['idt_movimento' => $evento->idt_movimento]);
    
    $trabalhador = Trabalhador::factory()->create([
        'idt_pessoa' => $this->pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe,
        'ind_avaliacao' => false,
    ]);
    
    $data = [
        'idt_trabalhador' => $trabalhador->idt_trabalhador,
        'ind_recomendado' => '1',
        'ind_lideranca' => '1',
        'ind_destaque' => '0',
        'ind_camiseta_pediu' => '1',
        'ind_camiseta_pagou' => '1',
    ];
    
    $response = $this->actingAs($this->user)->post('/avaliacao', $data);
    
    $response->assertRedirect(route('quadrante.list', ['evento' => $evento->idt_evento]));
    
    // Verifica atualização
    $this->assertDatabaseHas('trabalhador', [
        'idt_trabalhador' => $trabalhador->idt_trabalhador,
        'ind_recomendado' => true,
        'ind_lideranca' => true,
        'ind_destaque' => false,
        'ind_camiseta_pediu' => true,
        'ind_camiseta_pagou' => true,
        'ind_avaliacao' => true, 
    ]);
});

// ==========================================
// TESTES DE QUADRANTE
// ==========================================

test('admin pode visualizar quadrante', function () {
    $evento = Evento::factory()->create();
    
    $response = $this->actingAs($this->user)->get('/quadrante?evento=' . $evento->idt_evento);
    
    $response->assertStatus(200);
    $response->assertViewIs('evento.quadrante');
    $response->assertViewHas('trabalhadoresPorEquipe');
});