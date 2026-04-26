<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipe_usuario', function (Blueprint $table) {
            $table->id('idt_equipe_usuario');
            $table->foreignId('idt_equipe')->constrained('equipes', 'idt_equipe')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('papel', 30);
            $table->foreignId('usr_inclusao')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usr_alteracao')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dat_inclusao')->nullable();
            $table->timestamp('dat_alteracao')->nullable();
            $table->softDeletes();
            $table->unique(['user_id', 'idt_equipe'], 'equipe_usuario_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipe_usuario');
    }
};
