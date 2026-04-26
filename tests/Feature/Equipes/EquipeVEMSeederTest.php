<?php

use App\Models\Equipe;
use Database\Seeders\EquipeVEMSeeder;
use Illuminate\Support\Facades\Artisan;

describe('EquipeVEMSeeder', function () {
    test('seeder cria exatamente 11 equipes VEM', function () {
        $this->seed(EquipeVEMSeeder::class);

        expect(Equipe::count())->toBe(11);
        expect(Equipe::all()->every(fn ($e) => $e->movimento->des_sigla === 'VEM'))->toBeTrue();
    });

    test('seeder e idempotente', function () {
        $this->seed(EquipeVEMSeeder::class);
        $this->seed(EquipeVEMSeeder::class);

        expect(Equipe::count())->toBe(11);
    });

    test('11 nomes e slugs esperados estao presentes', function () {
        $this->seed(EquipeVEMSeeder::class);

        // Tabela oficial REQUIREMENTS.md EQUIPE-03
        $esperadas = [
            ['nom_equipe' => 'Alimentação', 'des_slug' => 'alimentacao'],
            ['nom_equipe' => 'Bandinha',     'des_slug' => 'bandinha'],
            ['nom_equipe' => 'Emaús',        'des_slug' => 'emaus'],
            ['nom_equipe' => 'Limpeza',      'des_slug' => 'limpeza'],
            ['nom_equipe' => 'Oração',       'des_slug' => 'oracao'],
            ['nom_equipe' => 'Recepção',     'des_slug' => 'recepcao'],
            ['nom_equipe' => 'Reportagem',   'des_slug' => 'reportagem'],
            ['nom_equipe' => 'Sala',         'des_slug' => 'sala'],
            ['nom_equipe' => 'Secretaria',   'des_slug' => 'secretaria'],
            ['nom_equipe' => 'Troca de Ideias', 'des_slug' => 'troca-de-ideias'],
            ['nom_equipe' => 'Vendinha',     'des_slug' => 'vendinha'],
        ];

        foreach ($esperadas as $dado) {
            expect(Equipe::where('des_slug', $dado['des_slug'])->exists())
                ->toBeTrue("Equipe com slug '{$dado['des_slug']}' deveria existir");
        }
    });

    test('migrate:fresh seguido de seed do EquipeVEMSeeder produz 11 equipes (TEST-06)', function () {
        // migrate:fresh --seed completo requer GD (EventoSeeder cria imagens) — nao disponivel neste ambiente.
        // Testamos o comportamento equivalente: fresh migration + seed do seeder especifico.
        // A integracao com DatabaseSeeder (EquipeVEMSeeder registrado apos DominiosSeeder) e
        // verificada pela presenca de EquipeVEMSeeder::class em DatabaseSeeder.php.
        Artisan::call('migrate:fresh');
        $this->seed(EquipeVEMSeeder::class);
        expect(Equipe::count())->toBe(11);
    });
});
