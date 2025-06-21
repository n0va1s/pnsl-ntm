<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FichaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajuste se necessário
    }

    public function rules(): array
    {
        return [
            // Ficha
            'idt_evento' => 'required|exists:evento,idt_evento',
            'tip_genero' => 'required|string|max:3',
            'nom_candidato' => 'required|string|max:255',
            'nom_apelido' => 'required|string|max:255',
            'dat_nascimento' => 'required|date',
            'tel_candidato' => 'nullable|string|max:20',
            'eml_candidato' => 'nullable|email|max:255',
            'des_endereco' => 'nullable|string|max:255',
            'tam_camiseta' => 'required|string|max:2',
            'tip_como_soube' => 'nullable|string|max:3',
            'ind_catolico' => 'boolean',
            'ind_toca_instrumento' => 'boolean',
            'ind_consentimento' => 'boolean',
            'txt_observacao' => 'nullable|string',

            // FichaVem
            'vem.idt_falar_com' => 'nullable|exists:tipo_responsavel,idt_responsavel',
            'vem.des_onde_estuda' => 'nullable|string|max:255',
            'vem.des_mora_quem' => 'nullable|string|max:255',
            'vem.nom_pai' => 'nullable|string|max:150',
            'vem.tel_pai' => 'nullable|string|max:15',
            'vem.nom_mae' => 'nullable|string|max:150',
            'vem.tel_mae' => 'nullable|string|max:10',

            // FichaEcc
            'ecc.nom_conjuge' => 'nullable|string|max:150',
            'ecc.nom_apelido_conjuge' => 'nullable|string|max:50',
            'ecc.tel_conjuge' => 'nullable|string|max:15',
            'ecc.dat_nascimento_conjuge' => 'nullable|date',
            'ecc.tam_camiseta_conjuge' => 'nullable|string|max:2',
        ];
    }
}