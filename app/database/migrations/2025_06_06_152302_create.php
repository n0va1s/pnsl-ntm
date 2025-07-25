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
        // Tabela Tipo_Situacao ex: cadastrada, encaminhada, aprovada
        Schema::create('tipo_situacao', function (Blueprint $table) {
            $table->id('idt_situacao');
            $table->string('des_situacao', 255);
            $table->timestamps();
        });

        // Tabela Tipo_Responsavel ex: avê, avó, pai, mae, padrinho, madrinha
        Schema::create('tipo_responsavel', function (Blueprint $table) {
            $table->id('idt_responsavel');
            $table->string('des_responsavel', 255);
            $table->timestamps();
        });

        // Tabela Tipo_Restricao ex: alergia a ovo, intolerancia remedio
        Schema::create('tipo_restricao', function (Blueprint $table) {
            $table->id('idt_restricao');
            $table->string('des_restricao', 255);
            $table->string('tip_restricao', 3); // alergia, intolerância, PNE
            $table->timestamps();
        });

        // Tabela Tipo_Movimento ex: ECC, Segue-Me, VEM
        Schema::create('tipo_movimento', function (Blueprint $table) {
            $table->id('idt_movimento');
            $table->string('nom_movimento', 255);
            $table->string('des_sigla', 10);
            $table->date('dat_inicio');
            $table->timestamps();
        });

        // Tabela Tipo_Equipe ex: bandinha, reportagem, oracao
        Schema::create('tipo_equipe', function (Blueprint $table) {
            $table->id('idt_equipe');
            $table->foreignId('idt_movimento')
                ->constrained('tipo_movimento', 'idt_movimento');
            $table->string('des_grupo', 255);
            $table->text('txt_documento')->nullable();
            $table->timestamps();
        });

        // Tabela Evento ex: XXX VEM,
        Schema::create('evento', function (Blueprint $table) {
            $table->id('idt_evento');
            $table->foreignId('idt_movimento')
                ->constrained('tipo_movimento', 'idt_movimento');
            $table->string('des_evento', 255);
            $table->string('num_evento', 5)->nullable();
            $table->date('dat_inicio');
            $table->date('dat_termino')->nullable();
            $table->string('val_camiseta', 10)->nullable(); // valor da camiseta
            $table->string('val_trabalhador', 10)->nullable(); // contribuição do trabalhador
            $table->string('val_venista', 10)->nullable(); // contribuição do venista (participante)
            $table->string('val_entrada', 10)->nullable(); // entrada no pos-encontro
            $table->boolean('ind_pos_encontro')->default(false);
            $table->timestamps();
        });

        // Tabela Evento_Foto ex: foto do evento 22 tirada durante o evento
        Schema::create('evento_foto', function (Blueprint $table) {
            $table->foreignId('idt_evento')
                ->constrained('evento', 'idt_evento')
                ->onDelete('cascade');
            $table->string('med_foto'); //armazenar no filesystem
            $table->timestamps();

            $table->primary(['idt_evento']);
        });

        // Tabela Pessoa dados básicos das pessoas
        // e criada apos a conclusao do evento. Ex: XXX Vem
        // os dados vem da ficha
        Schema::create('pessoa', function (Blueprint $table) {
            $table->id('idt_pessoa');
            $table->foreignId('idt_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nom_pessoa', 255);
            $table->string('nom_apelido', 255)->nullable();
            $table->string('tel_pessoa', 20)->nullable();
            $table->date('dat_nascimento');
            $table->string('des_endereco', 255)->nullable();
            $table->string('eml_pessoa', 255);
            $table->string('tam_camiseta', 2)->nullable();
            $table->string('tip_genero', 1)->nullable();
            $table->boolean('ind_toca_violao')->default(false);
            $table->boolean('ind_consentimento')->default(false);
            $table->boolean('ind_restricao')->default(false);
            $table->timestamps();
        });

        // Tabela Pessoa_Foto ex: foto da pessoa 22 tirada durante o evento
        Schema::create('pessoa_foto', function (Blueprint $table) {
            $table->foreignId('idt_pessoa')
                ->constrained('pessoa', 'idt_pessoa')
                ->onDelete('cascade');
            $table->string('med_foto'); //armazenar no filesystem
            $table->timestamps();

            $table->primary(['idt_pessoa']);
        });

        // Tabela Pessoa_Saude ex: pessoa 22 tem alergia a castanha
        Schema::create('pessoa_saude', function (Blueprint $table) {
            $table->foreignId('idt_pessoa')
                ->constrained('pessoa', 'idt_pessoa')
                ->onDelete('cascade');
            $table->foreignId('idt_restricao')
                ->constrained('tipo_restricao', 'idt_restricao');
            $table->boolean('ind_remedio_regular')->default(false);
            $table->text('txt_complemento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['idt_pessoa', 'idt_restricao']);
        });

        // Tabela Participante indica todos o encontro que a pessoa fez
        Schema::create('participante', function (Blueprint $table) {
            $table->id('idt_participante');
            $table->foreignId('idt_pessoa')
                ->constrained('pessoa', 'idt_pessoa')
                ->onDelete('cascade');
            $table->foreignId('idt_evento')
                ->constrained('evento', 'idt_evento')
                ->onDelete('cascade');
            $table->string('tip_cor_troca', 10)->nullable();
            $table->timestamps();

            $table->unique(['idt_pessoa', 'idt_evento'], 'unique_participante_per_evento');
        });

        // Tabela Presenca (Frequencia dos participantes nos eventos)
        Schema::create('presenca', function (Blueprint $table) {
            $table->foreignId('idt_participante')
                ->constrained('participante', 'idt_participante')
                ->onDelete('cascade');
            $table->date('dat_presenca');
            $table->boolean('ind_presente')->default(false); // se o participante estava presente nesse dia
            $table->timestamps();

            $table->unique(['idt_participante', 'dat_presenca'], 'unique_presenca_por_participante');
        });

        // Tabela Voluntario indica as equipes que a pessoas quer trabalhar
        Schema::create('voluntario', function (Blueprint $table) {
            $table->id('idt_voluntario');
            $table->foreignId('idt_pessoa')->constrained('pessoa', 'idt_pessoa');
            $table->foreignId('idt_evento')->constrained('evento', 'idt_evento');
            $table->foreignId('idt_equipe')->constrained('tipo_equipe', 'idt_equipe');
            $table->foreignId('idt_trabalhador')->nullable()->constrained('trabalhador', 'idt_trabalhador')->nullOnDelete();
            $table->text('txt_habilidade')->nullable(); // quais as suas habilidades para esta equipe
            $table->timestamps();
        });

        // Tabela Trabalhador indica os encontros que a pessoa trabalhou ou coordenou
        // Nao ha necessidade do trabalhador ter indicado as equipes que quer trabalhar
        Schema::create('trabalhador', function (Blueprint $table) {
            $table->id('idt_trabalhador');
            $table->foreignId('idt_pessoa')
                ->constrained('pessoa', 'idt_pessoa')
                ->onDelete('cascade');
            $table->foreignId('idt_evento')
                ->constrained('evento', 'idt_evento')
                ->onDelete('cascade');
            $table->foreignId('idt_equipe')
                ->constrained('tipo_equipe', 'idt_equipe');
            $table->boolean('ind_coordenador')->default(false); // foi a coordenadora da equipe
            $table->boolean('ind_primeira_vez')->default(false); // primeira vez no encontro
            $table->boolean('ind_recomendado')->default(false); // recomenda trabalhar novamente?
            $table->boolean('ind_lideranca')->default(false); // tem potencial para liderar uma equipe no futuro?
            $table->boolean('ind_destaque')->default(false); // indicaria para a coordenação geral?
            $table->boolean('ind_avaliacao')->default(false); // a pessoa foi avaliada
            $table->boolean('ind_camiseta_pediu')->default(false);
            $table->boolean('ind_camiseta_pagou')->default(false);
            $table->timestamps();
            $table->unique(['idt_pessoa', 'idt_evento', 'idt_equipe'], 'unique_trabalhador');
        });

        // Tabela Ficha com os dados básicos do participante
        // devem ser informacoes comuns aos movimentos
        Schema::create('ficha', function (Blueprint $table) {
            $table->id('idt_ficha');
            $table->foreignId('idt_evento')
                ->constrained('evento', 'idt_evento');
            $table->foreignId('idt_pessoa')->nullable()
                ->constrained('pessoa', 'idt_pessoa')->nullOnDelete(); //pessoa criada apos aprovacao
            $table->string('tip_genero', 3);
            $table->string('nom_candidato', 255);
            $table->string('nom_apelido', 255);
            $table->date('dat_nascimento');
            $table->string('tel_candidato', 20)->nullable();
            $table->string('eml_candidato', 255);
            $table->string('des_endereco', 255)->nullable();
            $table->string('tam_camiseta', 2);
            $table->string('tip_como_soube', 3)->nullable(); //indicacao, padre
            $table->boolean('ind_catolico')->default(false); //candidato catolico
            $table->boolean('ind_toca_instrumento')->default(false); //toca algum instrumento
            $table->boolean('ind_consentimento')->default(false); //concordou com o termo
            $table->boolean('ind_aprovado')->default(false); // flag para facilitar busca
            $table->boolean('ind_restricao')->default(false); // nao possui restricao alimentar
            $table->text('txt_observacao')->nullable(); //qual o instrumento, remedio continuo
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela Ficha com os detalhes do vem
        Schema::create('ficha_vem', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                ->constrained('ficha', 'idt_ficha')
                ->onDelete('cascade');
            $table->foreignId('idt_falar_com')
                ->constrained('tipo_responsavel', 'idt_responsavel');
            $table->string('des_onde_estuda', 255);
            $table->string('des_mora_quem', 255);
            $table->string('nom_pai', 150)->nullable();
            $table->string('tel_pai', 15)->nullable();
            $table->string('nom_mae', 150)->nullable();
            $table->string('tel_mae', 15)->nullable();
            $table->timestamps();
            $table->primary(['idt_ficha']);
        });

        // Tabela Ficha com os detalhes do ecc
        Schema::create('ficha_ecc', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                ->constrained('ficha', 'idt_ficha')
                ->onDelete('cascade');
            $table->string('nom_conjuge', 150);
            $table->string('nom_apelido_conjuge', 50)->nullable();
            $table->string('tel_conjuge', 15);
            $table->date('dat_nascimento_conjuge');
            $table->string('tam_camiseta_conjuge', 2);
            $table->timestamps();
            $table->primary(['idt_ficha']);
        });

        // Tabela Ficha com os detalhes do Segue-Me
        Schema::create('ficha_sgm', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                ->constrained('ficha', 'idt_ficha')
                ->onDelete('cascade');
            $table->timestamps();
            $table->primary(['idt_ficha']);
        });

        // Tabela Ficha_Saude com os dados de saude do candidato
        Schema::create('ficha_saude', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                ->constrained('ficha', 'idt_ficha')
                ->onDelete('cascade');
            $table->foreignId('idt_restricao')
                ->constrained('tipo_restricao', 'idt_restricao');
            $table->text('txt_complemento')->nullable();
            $table->timestamps();

            $table->unique(['idt_ficha', 'idt_restricao'], 'unique_ficha_saude');
        });

        // Tabela Ficha_Analise é o histórico da ficha ex: ficha 14 cadastrada, ficha 14 aprovada
        Schema::create('ficha_analise', function (Blueprint $table) {
            $table->foreignId('idt_ficha')
                ->constrained('ficha', 'idt_ficha')
                ->onDelete('cascade');
            $table->foreignId('idt_situacao')
                ->constrained('tipo_situacao', 'idt_situacao');
            $table->text('txt_analise')->nullable();
            $table->timestamps();

            $table->unique(['idt_ficha', 'idt_situacao'], 'unique_ficha_situacao');
        });


        // Tabela Contato para tirar dúvidas externas
        Schema::create('contato', function (Blueprint $table) {
            $table->id('idt_contato');
            $table->date('dat_contato')->default(now());
            $table->string('nom_contato', 255);
            $table->string('eml_contato', 255)->nullable();
            $table->string('tel_contato', 20);
            $table->text('txt_mensagem');
            $table->foreignId('idt_movimento')
                ->constrained('tipo_movimento', 'idt_movimento')
                ->onDelete('cascade'); // para direcionar para os responsaveis
            $table->timestamps();
            $table->softDeletes(); // para manter histórico de contatos\
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('contato');
        Schema::dropIfExists('ficha_analise');
        Schema::dropIfExists('ficha_saude');
        Schema::dropIfExists('ficha_sgm');
        Schema::dropIfExists('ficha_ecc');
        Schema::dropIfExists('ficha_vem');
        Schema::dropIfExists('ficha');
        Schema::dropIfExists('trabalhador');
        Schema::dropIfExists('voluntario');
        Schema::dropIfExists('participante');
        Schema::dropIfExists('pessoa_foto');
        Schema::dropIfExists('pessoa_saude');
        Schema::dropIfExists('pessoa');
        Schema::dropIfExists('evento');
        Schema::dropIfExists('tipo_movimento');
        Schema::dropIfExists('tipo_equipe');
        Schema::dropIfExists('tipo_restricao');
        Schema::dropIfExists('tipo_responsavel');
        Schema::dropIfExists('tipo_situacao');
    }
};
