
<?php

use App\Models\TipoEquipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\CrudBasic;

uses(RefreshDatabase::class, CrudBasic::class);

describe('Equipe::CRUD', function () {
    test('tipo equipe respeita contrato basico', function () {
        $this->verificaOperacoes(
            TipoEquipe::class,
            ['des_grupo']
        );
    });

    test('tipo equipe pode ser usado como foreign key', function () {
        $equipe = TipoEquipe::factory()->create();

        expect($equipe->idt_equipe)->toBeInt();
    });
});
