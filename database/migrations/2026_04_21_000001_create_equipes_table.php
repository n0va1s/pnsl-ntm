<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipes', function (Blueprint $table) {
            $table->id('idt_equipe');
            $table->foreignId('idt_movimento')->constrained('tipo_movimento', 'idt_movimento');
            $table->string('nom_equipe', 100);
            $table->string('des_slug', 120)->index();
            $table->text('des_descricao')->nullable();
            $table->boolean('ind_ativa')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['idt_movimento', 'des_slug'], 'equipes_movimento_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipes');
    }
};
