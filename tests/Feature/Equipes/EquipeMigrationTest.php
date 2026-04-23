<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

describe('Migration equipes', function () {
    test('tabela equipes e criada com todas as colunas esperadas', function () {
        expect(Schema::hasTable('equipes'))->toBeTrue();
        expect(Schema::hasColumns('equipes', [
            'idt_equipe',
            'idt_movimento',
            'nom_equipe',
            'des_slug',
            'des_descricao',
            'ind_ativa',
            'created_at',
            'updated_at',
            'deleted_at',
        ]))->toBeTrue();
    });

    test('equipes tem PK idt_equipe bigint autoincrement', function () {
        $vem = DB::table('tipo_movimento')->insertGetId([
            'nom_movimento' => 'VEM Teste',
            'des_sigla' => 'VMTST',
            'dat_inicio' => '2000-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('equipes')->insertGetId([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Equipe Teste',
            'des_slug' => 'equipe-teste',
            'des_descricao' => null,
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        expect($id)->toBeGreaterThan(0);
    });

    test('equipes tem unique composto idt_movimento e des_slug', function () {
        $vem = DB::table('tipo_movimento')->insertGetId([
            'nom_movimento' => 'VEM Teste Unique',
            'des_sigla' => 'VMUNQ',
            'dat_inicio' => '2000-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('equipes')->insert([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Sala',
            'des_slug' => 'sala',
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        expect(fn () => DB::table('equipes')->insert([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Sala Duplicada',
            'des_slug' => 'sala',
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    test('equipes aceita soft delete', function () {
        $vem = DB::table('tipo_movimento')->insertGetId([
            'nom_movimento' => 'VEM Soft',
            'des_sigla' => 'VMSD',
            'dat_inicio' => '2000-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('equipes')->insertGetId([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Equipe Soft',
            'des_slug' => 'equipe-soft',
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('equipes')->where('idt_equipe', $id)->update(['deleted_at' => now()]);

        $found = DB::table('equipes')->where('idt_equipe', $id)->whereNull('deleted_at')->first();
        expect($found)->toBeNull();
    });

    test('migration de equipes e reversivel', function () {
        // Rollback only the equipes migration (step=1 prevents hitting the pre-existing
        // SQLite FK drop limitation in the legacy usu_inclusao migration)
        Artisan::call('migrate:rollback', ['--step' => 1]);
        expect(Schema::hasTable('equipes'))->toBeFalse();

        // Restore so next tests work fine
        Artisan::call('migrate');
        expect(Schema::hasTable('equipes'))->toBeTrue();
    });
});
