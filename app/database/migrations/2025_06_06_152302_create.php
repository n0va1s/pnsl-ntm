<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela Tipo_Situacao
        Schema::create('tipo_situacao', function (Blueprint $table) {
            $table->id('idt_situacao');
            $table->string('des_situacao', 255);
            $table->timestamps();
        });

        // Tabela Tipo_Responsavel
        Schema::create('tipo_responsavel', function (Blueprint $table) {
            $table->id('idt_responsavel');
            $table->string('des_responsavel', 255);
            $table->timestamps();
        });

        // Tabela Tipo_Restricao
        Schema::create('tipo_restricao', function (Blueprint $table) {
            $table->id('idt_restricao');
            $table->string('des_restricao', 255);
            $table->string('tip_restricao', 3); // alergia, intolerância, PNE
            $table->timestamps();
        });

        // Tabela Evento
        Schema::create('evento', function (Blueprint $table) {
            $table->id('idt_evento');
            $table->string('des_evento', 255);
            $table->string('num_evento', 5)->nullable();
            $table->date('dat_inicio')->nullable();
            $table->date('dat_termino')->nullable();
            $table->boolean('ind_pos_encontro')->default(false);
            $table->timestamps();
        });

        // Tabela Tipo_Equipe
        Schema::create('tipo_equipe', function (Blueprint $table) {
            $table->id('idt_equipe');
            $table->string('des_grupo', 255);
            $table->timestamps();
        });

        // Tabela Ficha
        Schema::create('ficha', function (Blueprint $table) {
            $table->id('idt_ficha');
            $table->foreignId('idt_tipo_responsavel')
                  ->constrained('tipo_responsavel', 'idt_responsavel');
            $table->string('nom_responsavel', 255)->nullable();
            $table->string('tel_responsavel', 20)->nullable();
            $table->string('nom_candidato', 255);
            $table->string('des_telefone', 20)->nullable();
            $table->string('des_endereco', 255)->nullable();
            $table->date('dat_nascimento')->nullable();
            $table->string('des_onde_estuda', 255)->nullable();
            $table->string('des_mora_quem', 255)->nullable();
            $table->string('tam_camiseta', 2)->nullable();
            $table->integer('num_satisfacao')->nullable(); // avaliação do evento de 0 a 10
            $table->boolean('ind_toca_instrumento')->default(false);
            $table->boolean('ind_aprovado')->default(false);
            $table->timestamps();
        });

        // Tabela Ficha_Analise
        Schema::create('ficha_analise', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                  ->constrained('ficha', 'idt_ficha')
                  ->onDelete('cascade');
            $table->foreignId('idt_situacao')
                  ->constrained('tipo_situacao', 'idt_situacao');
            $table->text('txt_analise')->nullable();
            $table->timestamps();
            
            $table->primary(['idt_ficha', 'idt_situacao']);
        });

        // Tabela Ficha_Saude
        Schema::create('ficha_saude', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                  ->constrained('ficha', 'idt_ficha')
                  ->onDelete('cascade');
            $table->foreignId('idt_restricao')
                  ->constrained('tipo_restricao', 'idt_restricao');
            $table->text('txt_complemento')->nullable();
            $table->timestamps();
            
            $table->primary(['idt_ficha', 'idt_restricao']);
        });

        // Tabela Participante
        Schema::create('participante', function (Blueprint $table) {
            $table->id('idt_participante');
            $table->foreignId('idt_ficha')
                  ->constrained('ficha', 'idt_ficha')
                  ->onDelete('cascade');
            $table->foreignId('idt_evento')
                  ->constrained('evento', 'idt_evento')
                  ->onDelete('cascade');
            $table->string('tip_cor_troca', 10)->nullable();
            $table->timestamps();
        });

        // Tabela Trabalhador
        Schema::create('trabalhador', function (Blueprint $table) {
            $table->id('idt_trabalhador');
            $table->foreignId('idt_evento')
                  ->constrained('evento', 'idt_evento')
                  ->onDelete('cascade');
            $table->foreignId('idt_equipe')
                  ->constrained('tipo_equipe', 'idt_equipe');
            $table->boolean('ind_recomendado')->default(false); // recomenda trabalhar novamente?
            $table->boolean('ind_lideranca')->default(false); // tem potencial para liderar uma equipe no futuro?
            $table->boolean('ind_destaque')->default(false); // indicaria para a coordenação geral?
            $table->boolean('ind_coordenador')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabalhador');
        Schema::dropIfExists('participante');
        Schema::dropIfExists('ficha_saude');
        Schema::dropIfExists('ficha_analise');
        Schema::dropIfExists('ficha');
        Schema::dropIfExists('tipo_equipe');
        Schema::dropIfExists('evento');
        Schema::dropIfExists('tipo_restricao');
        Schema::dropIfExists('tipo_responsavel');
        Schema::dropIfExists('tipo_situacao');
    }
};
