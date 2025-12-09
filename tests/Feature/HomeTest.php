<?php

use App\Models\User;

// ==========================================
// TESTES DE PÁGINA INICIAL
// ==========================================

test('visitante pode acessar pagina inicial', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertViewIs('welcome');
});

test('pagina inicial exibe informacoes da organizacao', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('Sistema de Gestão de Movimentos Paroquiais');
    $response->assertSee('Não tenhais medo!');
});

test('pagina inicial exibe movimentos', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('VEM');
    $response->assertSee('Segue-Me');
    $response->assertSee('ECC');
});

// ==========================================
// TESTES DE REDIRECIONAMENTOS
// ==========================================

test('visitante nao autenticado permanece na home', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertViewIs('welcome');
});

// ==========================================
// TESTES DE SEO E META TAGS
// ==========================================

test('pagina inicial possui titulo correto', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('<title>Não Tenhais Medo</title>', false);
});

// ==========================================
// TESTES DE RESPONSIVIDADE
// ==========================================

test('pagina inicial e responsiva', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('viewport', false);
});
