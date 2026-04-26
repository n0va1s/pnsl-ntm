<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendinha_produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nom_produto', 120);
            $table->text('des_produto')->nullable();
            $table->decimal('vlr_custo', 10, 2)->default(0);
            $table->decimal('vlr_venda', 10, 2)->default(0);
            $table->integer('qtd_estoque')->nullable();
            $table->boolean('ind_ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('vendinha_vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idt_pessoa')->nullable()->constrained('pessoa', 'idt_pessoa')->nullOnDelete();
            $table->foreignId('idt_equipe')->nullable()->constrained('equipes', 'idt_equipe')->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nom_comprador', 120)->nullable();
            $table->string('status', 20)->default('pago')->index();
            $table->decimal('vlr_custo_total', 10, 2)->default(0);
            $table->decimal('vlr_total', 10, 2)->default(0);
            $table->decimal('vlr_lucro_total', 10, 2)->default(0);
            $table->timestamp('dat_pagamento')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('vendinha_venda_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendinha_venda_id')->constrained('vendinha_vendas')->cascadeOnDelete();
            $table->foreignId('vendinha_produto_id')->nullable()->constrained('vendinha_produtos')->nullOnDelete();
            $table->string('nom_produto', 120);
            $table->integer('qtd_item');
            $table->decimal('vlr_custo_unitario', 10, 2)->default(0);
            $table->decimal('vlr_venda_unitario', 10, 2)->default(0);
            $table->decimal('vlr_custo_total', 10, 2)->default(0);
            $table->decimal('vlr_total', 10, 2)->default(0);
            $table->decimal('vlr_lucro_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendinha_venda_itens');
        Schema::dropIfExists('vendinha_vendas');
        Schema::dropIfExists('vendinha_produtos');
    }
};
