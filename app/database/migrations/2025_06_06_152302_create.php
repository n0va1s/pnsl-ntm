<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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
            $table->string('val_trabalhador', 10)->nullable(); // contribuição do trabalhador
            $table->string('val_venista', 10)->nullable(); // contribuição do venista (participante)
            $table->string('val_camiseta', 10)->nullable(); // valor da camiseta
            $table->boolean('ind_pos_encontro')->default(false);
            $table->timestamps();
        });

        // Tabela Tipo_Equipe
        Schema::create('tipo_equipe', function (Blueprint $table) {
            $table->id('idt_equipe');
            $table->string('des_grupo', 255);
            $table->timestamps();
        });

        // Tabela Ficha (agora sem relacionamento direto com participante/trabalhador)
        Schema::create('ficha', function (Blueprint $table) {
            $table->id('idt_ficha');
            $table->foreignId('idt_tipo_responsavel')
                  ->constrained('tipo_responsavel', 'idt_responsavel');
            $table->string('nom_responsavel', 255)->nullable();
            $table->string('tel_responsavel', 20)->nullable();
            $table->string('nom_candidato', 255);
            $table->string('des_telefone', 20)->nullable();
            $table->string('des_email', 255)->nullable();
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

        // Tabela Pessoa (dados básicos das pessoas) quando ingress
        Schema::create('pessoa', function (Blueprint $table) {
            $table->id('idt_pessoa');
            $table->string('nom_pessoa', 255);
            $table->string('nom_apelido', 255)->nullable();
            $table->string('tel_pessoa', 20)->nullable();
            $table->string('des_endereco', 255)->nullable();
            $table->string('eml_pessoa', 255)->nullable();
            $table->boolean('ind_toca_violao')->default(false);
            $table->date('dat_nascimento')->nullable();
            $table->string('tam_camiseta', 2)->nullable();
            $table->string('tip_genero', 10)->nullable(); // masculino, feminino, outro
            $table->string('ind_consentimento', 3)->default('não'); // sim, não
            $table->timestamps();
        });

        // Tabela Pessoa_Saude (nova - restrições de saúde das pessoas)
        Schema::create('pessoa_saude', function (Blueprint $table) {
            $table->foreignId('idt_pessoa')
                  ->constrained('pessoa', 'idt_pessoa')
                  ->onDelete('cascade');
            $table->foreignId('idt_restricao')
                  ->constrained('tipo_restricao', 'idt_restricao');
            $table->text('txt_complemento')->nullable();
            $table->timestamps();

            $table->primary(['idt_pessoa', 'idt_restricao']);
        });

        // Tabela Participante (agora referencia Pessoa em vez de Ficha)
        Schema::create('participante', function (Blueprint $table) {
            $table->foreignId('idt_participante');
            $table->foreignId('idt_pessoa')
                  ->constrained('pessoa', 'idt_pessoa')
                  ->onDelete('cascade');
            $table->foreignId('idt_evento')
                  ->constrained('evento', 'idt_evento')
                  ->onDelete('cascade');
            $table->string('tip_cor_troca', 10)->nullable();
            $table->timestamps();

            $table->primary(['idt_pessoa', 'idt_evento']);
        });

         // Tabela Presenca (Frequencia dos participantes nos eventos)
        Schema::create('presenca', function (Blueprint $table) {
            $table->foreignId('idt_participante')
                  ->constrained('participante', 'idt_participante')
                  ->onDelete('cascade');
            $table->date('dat_presenca');
            $table->boolean('ind_presente')->default(false); // se o participante estava presente nesse dia
            $table->timestamps();
            $table->primary(['idt_participante', 'dat_presenca']);
        });

        // Tabela Trabalhador (agora referencia Pessoa em vez de ter ID próprio)
        Schema::create('trabalhador', function (Blueprint $table) {
            $table->foreignId('idt_pessoa')
                  ->constrained('pessoa', 'idt_pessoa')
                  ->onDelete('cascade');
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

            $table->primary(['idt_pessoa', 'idt_evento', 'idt_equipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabalhador');
        Schema::dropIfExists('participante');
        Schema::dropIfExists('pessoa_saude');
        Schema::dropIfExists('ficha_saude');
        Schema::dropIfExists('ficha_analise');
        Schema::dropIfExists('ficha');
        Schema::dropIfExists('pessoa');
        Schema::dropIfExists('tipo_equipe');
        Schema::dropIfExists('evento');
        Schema::dropIfExists('tipo_restricao');
        Schema::dropIfExists('tipo_responsavel');
        Schema::dropIfExists('tipo_situacao');
    }
};
