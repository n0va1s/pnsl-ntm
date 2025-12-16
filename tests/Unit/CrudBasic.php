<?php

namespace Tests\Unit;

trait CrudBasic
{
    public function verificaOperacoes(string $modelClass, array $requiredFields)
    {
        // Criação válida
        $model = $modelClass::factory()->create();
        expect($model)->not->toBeNull();

        // Campos obrigatórios
        foreach ($requiredFields as $field) {
            expect($model->{$field})->not->toBeNull();
        }

        // Update
        $model->update([$requiredFields[0] => 'Alterado']);
        expect($model->fresh()->{$requiredFields[0]})->toBe('Alterado');

        // Delete
        $model->delete();
        expect($modelClass::find($model->getKey()))->toBeNull();
    }
}
