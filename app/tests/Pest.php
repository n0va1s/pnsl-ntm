<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// Cleanup após cada teste (opcional)
afterEach(function () {
    // Limpa cache se necessário
    Cache::flush();
});

function createUser(): \App\Models\User
{
    return \App\Models\User::factory()->create();
}

function createMovimentos(): void
{
    \App\Models\TipoMovimento::firstOrCreate([
        'des_sigla' => 'ECC',
        'nom_movimento' => 'Encontro de Casais com Cristo',
        'dat_inicio' => '1980-01-01'
    ]);

    \App\Models\TipoMovimento::firstOrCreate([
        'des_sigla' => 'VEM',
        'nom_movimento' => 'Encontro de Adolescentes com Cristo',
        'dat_inicio' => '2000-07-01'
    ]);

    \App\Models\TipoMovimento::firstOrCreate([
        'des_sigla' => 'Segue-Me',
        'nom_movimento' => 'Encontro de Jovens com Cristo',
        'dat_inicio' => '1990-12-31'
    ]);

    $tipoMovimento = \App\Models\TipoMovimento::all()->first()->idt_movimento;

    $equipes = [
        ['des_grupo' => 'Alimentação', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Bandinha', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Coordenação Geral', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Emaús', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Limpeza', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Oração', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Recepção', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Reportagem', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Sala', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Secretaria', 'idt_movimento' => $tipoMovimento],
        ['des_grupo' => 'Vendinha', 'idt_movimento' => $tipoMovimento],
    ];

    DB::table('tipo_equipe')->insertOrIgnore($equipes);
}

function createEvento(): \App\Models\Evento
{
    return \App\Models\Evento::factory()->create([
        'idt_movimento' => \App\Models\TipoMovimento::all()->first()->idt_movimento,
    ]);
}

function createPessoa(): \App\Models\Pessoa
{
    return \App\Models\Pessoa::factory()->create([
        'idt_parceiro' => null,
    ]);
}
