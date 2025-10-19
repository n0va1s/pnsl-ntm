<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\EventoFoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFotoFactory extends Factory
{
    protected $model = EventoFoto::class;

    public function definition(): array
    {
        return [
            'idt_evento' => Evento::factory(),
            'med_foto' => 'fotos/evento/'.$this->faker->uuid.'.jpg', // caminho fictício
        ];
    }
}
