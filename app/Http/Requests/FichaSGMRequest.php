<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FichaSGMRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idt_falar_com' => 'required|exists:tipo_responsavel,idt_responsavel',
            'des_mora_quem' => 'required|string|max:255',
            'nom_pai' => 'nullable|string|max:255',
            'tel_pai' => 'nullable|string|max:15',
            'nom_mae' => 'nullable|string|max:255',
            'tel_mae' => 'nullable|string|max:15',

            'naturalidade' => 'required|string|max:255',
            'escolaridade' => 'nullable|string|max:255',
            'situacao' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'instituicao' => 'nullable|string|max:255',
            'religiao' => 'nullable|string|max:255',
            'nom_paroquia' => 'nullable|string|max:255',
            'ind_batismo' => 'nullable|boolean',
            'ind_eucaristia' => 'nullable|boolean',
            'ind_crisma' => 'nullable|boolean',
            'part_movimento' => 'nullable|string|max:255',
            'nom_convidou' => 'nullable|string|max:255',
            'tel_convidou' => 'nullable|string|max:255',
            'end_convidou' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'idt_falar_com.required' => 'Informe com quem devemos falar em caso de necessidade.',
            'des_mora_quem.required' => 'Informe com quem o candidato mora.',
            'naturalidade.required' => 'Informe a naturalidade do candidato.',
            'situacao.required' => 'Informe a situação do candidato.',
            'curso.required' => 'Informe o curso do candidato.',
            'instituicao.required' => 'Informe a instituição do candidato.',
            'religiao.required' => 'Informe a religião do candidato.',
            'nom_paroquia.required' => 'Informe o nome da paróquia do candidato.',
            'nom_convidou.required' => 'Informe o nome de quem convidou o candidato.',
            'tel_convidou.required' => 'Informe o telefone de quem convidou o candidato.',
            'end_convidou.required' => 'Informe o endereço de quem convidou o candidato.',
        ];
    }
}
