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
        Schema::table('participante', function (Blueprint $table) {
            $table->boolean('ind_taxa_pagou')->default(false);
            $table->boolean('ind_presente')->default(false);
        });

        Schema::table('trabalhador', function (Blueprint $table) {
            $table->boolean('ind_taxa_pagou')->default(false);
            $table->boolean('ind_presente')->default(false);
        });

        Schema::table('evento', function (Blueprint $table) {
            $table->decimal('val_receita', 8, 2)->nullable();
            $table->decimal('val_despesa', 8, 2)->nullable();
            $table->text('txt_relatorio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participante', function (Blueprint $table) {
            $table->dropColumn('ind_taxa_pagou');
            $table->dropColumn('ind_presente');
        });

        Schema::table('trabalhador', function (Blueprint $table) {
            $table->dropColumn('ind_taxa_pagou');
            $table->dropColumn('ind_presente');
        });

        Schema::table('evento', function (Blueprint $table) {
            $table->dropColumn('val_receita');
            $table->dropColumn('val_despesa');
            $table->dropColumn('txt_relatorio');
        });
    }
};
