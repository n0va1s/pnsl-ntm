<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'idt_movimento' => 'required|exists:tipo_movimento,idt_movimento',
            'des_evento' => 'required|string|max:255',
            'num_evento' => 'nullable|string|max:5',
            'dat_inicio' => 'required|date',
            'dat_termino' => 'nullable|date|after_or_equal:dat_inicio',
            'ind_pos_encontro' => 'required|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idt_movimento.required' => 'O movimento é obrigatório.',
            'des_evento.required' => 'A descrição do evento é obrigatória.',
            'des_evento.max' => 'A descrição do evento não pode ter mais de 255 caracteres.',
            'num_evento.string' => 'O número do evento deve ser um texto.',
            'num_evento.max' => 'O número do evento não pode ter mais de 5 caracteres.',
            'dat_inicio.required' => 'A data de início é obrigatória',
            'dat_inicio.date' => 'A data de início deve ser uma data válida.',
            'dat_termino.date' => 'A data de término deve ser uma data válida.',
            'dat_termino.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'ind_pos_encontro.boolean' => 'O campo pós encontro deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'des_evento' => 'descrição do evento',
            'num_evento' => 'número do evento',
            'dat_inicio' => 'data de início',
            'dat_termino' => 'data de término',
            'ind_pos_encontro' => 'pós encontro',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ind_pos_encontro' => $this->has('ind_pos_encontro') ? true : false,
        ]);
    }
}