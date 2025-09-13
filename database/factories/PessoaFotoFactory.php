<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\PessoaFoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PessoaFotoFactory extends Factory
{
    protected $model = PessoaFoto::class;

    public function definition(): array
    {
        Storage::fake('public');

        return [
            'idt_pessoa' => Pessoa::factory(),
            'med_foto' => UploadedFile::fake()->image('foto.jpg')->store('fotos/pessoa', 'public'),
        ];
    }
}
