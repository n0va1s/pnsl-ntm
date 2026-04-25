<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

describe('Migration equipe_usuario', function () {
    test('tabela equipe_usuario existe com colunas esperadas', function () {
        expect(Schema::hasTable('equipe_usuario'))->toBeTrue();
        expect(Schema::hasColumns('equipe_usuario', [
            'idt_equipe_usuario',
            'idt_equipe',
            'user_id',
            'papel',
            'usr_inclusao',
            'usr_alteracao',
            'dat_inclusao',
            'dat_alteracao',
            'deleted_at',
        ]))->toBeTrue();
    });

    test('pivot tem unique user_id e idt_equipe', function () {
        $vem = DB::table('tipo_movimento')->insertGetId([
            'nom_movimento' => 'VEM Unique',
            'des_sigla' => 'VMUNQ2',
            'dat_inicio' => '2000-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipeId = DB::table('equipes')->insertGetId([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Sala',
            'des_slug' => 'sala',
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user1 = User::factory()->create();

        DB::table('equipe_usuario')->insert([
            'idt_equipe' => $equipeId,
            'user_id' => $user1->id,
            'papel' => 'membro_equipe',
        ]);

        expect(fn () => DB::table('equipe_usuario')->insert([
            'idt_equipe' => $equipeId,
            'user_id' => $user1->id,
            'papel' => 'coord_geral',
        ]))->toThrow(QueryException::class);
    });

    test('apagar equipe cascata vinculos', function () {
        $vem = DB::table('tipo_movimento')->insertGetId([
            'nom_movimento' => 'VEM Cascade',
            'des_sigla' => 'VMCAS',
            'dat_inicio' => '2000-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipeId = DB::table('equipes')->insertGetId([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Limpeza',
            'des_slug' => 'limpeza',
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create();

        DB::table('equipe_usuario')->insert([
            'idt_equipe' => $equipeId,
            'user_id' => $user->id,
            'papel' => 'membro_equipe',
        ]);

        expect(DB::table('equipe_usuario')->where('idt_equipe', $equipeId)->count())->toBe(1);

        DB::table('equipes')->where('idt_equipe', $equipeId)->delete();

        expect(DB::table('equipe_usuario')->where('idt_equipe', $equipeId)->count())->toBe(0);
    });

    test('apagar user nao cascata vinculo e apenas anula usr_inclusao', function () {
        $vem = DB::table('tipo_movimento')->insertGetId([
            'nom_movimento' => 'VEM NullOn',
            'des_sigla' => 'VMNUL',
            'dat_inicio' => '2000-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipeId = DB::table('equipes')->insertGetId([
            'idt_movimento' => $vem,
            'nom_equipe' => 'Oração',
            'des_slug' => 'oracao',
            'ind_ativa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $autor = User::factory()->create();
        $membro = User::factory()->create();

        DB::table('equipe_usuario')->insert([
            'idt_equipe' => $equipeId,
            'user_id' => $membro->id,
            'papel' => 'membro_equipe',
            'usr_inclusao' => $autor->id,
        ]);

        // Delete the autor user (not the membro)
        DB::table('users')->where('id', $autor->id)->delete();

        $pivot = DB::table('equipe_usuario')
            ->where('idt_equipe', $equipeId)
            ->where('user_id', $membro->id)
            ->first();

        // The pivot record should still exist (no cascade on user_id)
        expect($pivot)->not->toBeNull();
        // usr_inclusao should be nullified due to nullOnDelete
        expect($pivot->usr_inclusao)->toBeNull();
    });

    test('migrations sao reversiveis na ordem correta', function () {
        // Rollback steps 1 and 2 (equipe_usuario and equipes)
        Artisan::call('migrate:rollback', ['--step' => 1]);
        expect(Schema::hasTable('equipe_usuario'))->toBeFalse();

        Artisan::call('migrate:rollback', ['--step' => 1]);
        expect(Schema::hasTable('equipes'))->toBeFalse();

        // Restore migrations
        Artisan::call('migrate');
        expect(Schema::hasTable('equipes'))->toBeTrue();
        expect(Schema::hasTable('equipe_usuario'))->toBeTrue();
    });
});
