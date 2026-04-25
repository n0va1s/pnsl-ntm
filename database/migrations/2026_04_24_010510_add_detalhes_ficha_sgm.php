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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
            $table->string('phone')->default('61988776655');
        }; // para ajudar na carga do forms
        });

        Schema::table('ficha_sgm', function (Blueprint $table) {
            $table->string('nom_responsavel', 150)->nullable(); // caso nao more com os pais
            $table->string('tel_responsavel', 20)->nullable();
            $table->string('eml_responsavel', 50)->nullable(); // para enviar a ficha para confirmacao
            $table->boolean('ind_batismo')->default(false); // batizado ou não
            $table->boolean('ind_eucaristia')->default(false); // primeira comunhão ou não
            $table->boolean('ind_crisma')->default(false); // crismado ou não
            $table->string('nom_paroquia', 150)->nullable(); // nome da paroquia que frequenta
            $table->string('religiao',150)->nullable();

            $table->string('naturalidade',150)->nullable();
            $table->string('escolaridade',150)->nullable();
            $table->string('curso',150)->nullable();
            $table->string('situacao',150)->nullable();
            $table->string('instituicao',150)->nullable();
            $table->string('part_movimento',150)->nullable();

            $table->string('nom_convidou',150)->nullable();
            $table->string('tel_convidou',20)->nullable();
            $table->string('end_convidou',150)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('ficha_sgm', function (Blueprint $table) {
            $table->dropColumn([
                'nom_responsavel',
                'tel_responsavel',
                'eml_responsavel',
                'ind_batizado',
                'ind_primeira_comunhao',
                'ind_crismado',
                'nom_paroquia',
                'naturalidade',
                'escolaridade',
                'curso',
                'situacao',
                'instituicao',
            ]);
        });
    }
};
