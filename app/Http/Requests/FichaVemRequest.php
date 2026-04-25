<?php

namespace App\Http\Requests;

class FichaVemRequest extends FichaRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [

            'idt_falar_com'         => 'required|exists:tipo_responsavel,idt_responsavel',
            'des_onde_estuda'       => 'required|string|max:255',
            'des_mora_quem'         => 'required|string|max:255',

            'nom_pai'               => 'nullable|string|max:150',
            'tel_pai'               => 'nullable|string|max:15',
            'eml_pai'               => 'nullable|email|max:255',

            'nom_mae'               => 'nullable|string|max:150',
            'tel_mae'               => 'nullable|string|max:15',
            'eml_mae'               => 'nullable|email|max:255',

            'nom_responsavel'       => 'nullable|string|max:150',
            'tel_responsavel'       => 'nullable|string|max:15',
            'eml_responsavel'       => 'nullable|email|max:255',

            'ind_batizado'          => 'required|boolean',
            'ind_primeira_comunhao' => 'required|boolean',
            'ind_crismado'          => 'required|boolean',
            'nom_paroquia'          => 'nullable|string|max:150',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'idt_falar_com.required'         => 'Informe com quem devemos falar em caso de necessidade.',
            'des_onde_estuda.required'       => 'Informe onde o candidato estuda.',
            'des_mora_quem.required'         => 'Informe com quem o candidato mora.',
            'ind_batizado.required'          => 'Informe se o candidato é batizado.',
            'ind_primeira_comunhao.required' => 'Informe se o candidato fez a primeira comunhão.',
            'ind_crismado.required'          => 'Informe se o candidato é crismado.',
            'eml_pai.email'                  => 'O e-mail do pai deve ser um endereço válido.',
            'eml_mae.email'                  => 'O e-mail da mãe deve ser um endereço válido.',
            'eml_responsavel.email'          => 'O e-mail do responsável deve ser um endereço válido.',
        ]);
    }
}
