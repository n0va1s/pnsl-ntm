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
        ];
    }

    public function messages(): array
    {
        return [
            'idt_falar_com.required' => 'Informe com quem devemos falar em caso de necessidade.',
            'des_mora_quem.required' => 'Informe com quem o candidato mora.',
        ];
    }
}
