<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FichaEccRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_conjuge'             => 'required|string|max:150',
            'nom_apelido_conjuge'     => 'nullable|string|max:50',
            'tel_conjuge'             => 'required|string|max:15',
            'dat_nascimento_conjuge'  => 'required|date',
            'tam_camiseta_conjuge'    => 'required|string|max:2',
        ];
    }

    public function messages(): array
    {
        return [
            'nom_conjuge.required' => 'O nome do cônjuge é obrigatório.',
            'tel_conjuge.required' => 'O telefone do cônjuge é obrigatório.',
            'dat_nascimento_conjuge.required' => 'Informe a data de nascimento do cônjuge.',
            'tam_camiseta_conjuge.required' => 'Informe o tamanho da camiseta do cônjuge.',
        ];
    }
}
