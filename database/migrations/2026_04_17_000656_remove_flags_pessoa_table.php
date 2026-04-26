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
        Schema::table('pessoa', function (Blueprint $table) {
            $table->dropColumn([
                'ind_toca_violao',
                'ind_consentimento',
            ]);
            $table->char('tip_estado_civil', 1)->nullable()->after('tip_genero');
            $table->char('tip_habilidade', 1)->nullable()->after('tip_estado_civil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pessoa', function (Blueprint $table) {
            $table->dropColumn('tip_habilidade');
            $table->dropColumn('tip_estado_civil');
            $table->boolean('ind_toca_violao')->default(false);
            $table->boolean('ind_consentimento')->default(false);
        });
    }
};
