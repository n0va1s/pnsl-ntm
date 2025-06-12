<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Situações da ficha: Pendente, Aprovada e Cancelada
        DB::table('tipo_situacao')->insert([
            ['des_situacao' => 'Cadastrada', 'created_at' => now(), 'updated_at' => now()],
            ['des_situacao' => 'Avaliada', 'created_at' => now(), 'updated_at' => now()],
            ['des_situacao' => 'Visitada', 'created_at' => now(), 'updated_at' => now()],
            ['des_situacao' => 'Aprovada', 'created_at' => now(), 'updated_at' => now()],
            ['des_situacao' => 'Cancelada', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Tipos de responsáveis: Pai, Mãe, Avô, Avó, Madrinha, Padrinho, Outro
        DB::table('tipo_responsavel')->insert([
            ['des_responsavel' => 'Pai', 'created_at' => now(), 'updated_at' => now()],
            ['des_responsavel' => 'Mãe', 'created_at' => now(), 'updated_at' => now()],
            ['des_responsavel' => 'Avô', 'created_at' => now(), 'updated_at' => now()],
            ['des_responsavel' => 'Avó', 'created_at' => now(), 'updated_at' => now()],
            ['des_responsavel' => 'Madrinha', 'created_at' => now(), 'updated_at' => now()],
            ['des_responsavel' => 'Padrinho', 'created_at' => now(), 'updated_at' => now()],
            ['des_responsavel' => 'Outro', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Tipos de restrições: Glúten, Ovo, Castanhas, Frutos do Mar, Motor, Auditivo, Neurodivergente
        DB::table('tipo_restricao')->insert([
            ['des_restricao' => 'Glúten', 'tip_restricao' => 'ALE', 'created_at' => now(), 'updated_at' => now()],
            ['des_restricao' => 'Ovo', 'tip_restricao' => 'ALE', 'created_at' => now(), 'updated_at' => now()],
            ['des_restricao' => 'Castanhas', 'tip_restricao' => 'ALE', 'created_at' => now(), 'updated_at' => now()],
            ['des_restricao' => 'Frutos do Mar', 'tip_restricao' => 'ALE', 'created_at' => now(), 'updated_at' => now()],
            ['des_restricao' => 'Motor', 'tip_restricao' => 'PNE', 'created_at' => now(), 'updated_at' => now()],
            ['des_restricao' => 'Auditivo', 'tip_restricao' => 'PNE', 'created_at' => now(), 'updated_at' => now()],
            ['des_restricao' => 'Neurodivergente', 'tip_restricao' => 'PNE', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Tipos de equipes: Bandinha, Recepção, Reportagem, Coordenação Geral, Alimentação, Sala, Limpeza
        DB::table('tipo_equipe')->insert([
            ['des_grupo' => 'Bandinha', 'created_at' => now(), 'updated_at' => now()],
            ['des_grupo' => 'Recepção', 'created_at' => now(), 'updated_at' => now()],
            ['des_grupo' => 'Reportagem', 'created_at' => now(), 'updated_at' => now()],
            ['des_grupo' => 'Coordenação Geral', 'created_at' => now(), 'updated_at' => now()],
            ['des_grupo' => 'Alimentação', 'created_at' => now(), 'updated_at' => now()],
            ['des_grupo' => 'Sala', 'created_at' => now(), 'updated_at' => now()],
            ['des_grupo' => 'Limpeza', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}