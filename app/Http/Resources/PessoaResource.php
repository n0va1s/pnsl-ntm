<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PessoaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pessoa_id' => $this->idt_pessoa,
            'usuario_id' => $this->idt_usuario,
            'nome' => $this->nom_pessoa,
            'apelido' => $this->nom_apelido,
            'email' => $this->eml_pessoa,
            'telefone' => $this->tel_pessoa,
            'data_nascimento' => $this->dat_nascimento?->format('d/m/Y'),
            'sexo' => $this->tip_genero,
            'endereco' => $this->des_endereco,
        ];
    }
}
