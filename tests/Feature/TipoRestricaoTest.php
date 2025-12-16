
<?php

use App\Models\TipoEquipe;
use App\Models\TipoRestricao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\CrudBasic;

uses(RefreshDatabase::class, CrudBasic::class);

describe('Restricao::CRUD', function () {
    test('tipo restricao respeita contrato basico', function () {
        $this->verificaOperacoes(
            TipoRestricao::class,
            ['des_restricao']
        );
    });

    test('tipo restricao pode ser usado como foreign key', function () {
        $restricao = TipoRestricao::factory()->create();

        expect($restricao->idt_restricao)->toBeInt();
    });
});
