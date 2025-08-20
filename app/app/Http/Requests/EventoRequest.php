<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'idt_movimento' => 'required|exists:tipo_movimento,idt_movimento',
            'des_evento' => 'required|string|max:255',
            'num_evento' => 'nullable|string|max:5',
            'dat_inicio' => 'required|date',
            'dat_termino' => 'nullable|date|after_or_equal:dat_inicio',
            'val_trabalhador' => 'nullable|numeric|min:0',
            'val_venista' => 'nullable|numeric|min:0',
            'val_camiseta' => 'nullable|numeric|min:0',
            'val_entrada' => 'nullable|numeric|min:0',
            'med_foto' => ['nullable', 'image', 'max:2048'],
            'tip_evento' => 'required|string|max:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'idt_movimento.required' => 'O movimento é obrigatório.',
            'idt_movimento.exists' => 'O movimento selecionado não é válido.',
            'des_evento.required' => 'A descrição do evento é obrigatória.',
            'des_evento.max' => 'A descrição do evento não pode ter mais de 255 caracteres.',
            'num_evento.string' => 'O número do evento deve ser um texto.',
            'num_evento.max' => 'O número do evento não pode ter mais de 5 caracteres.',
            'dat_inicio.required' => 'A data de início é obrigatória.',
            'dat_inicio.date' => 'A data de início deve ser uma data válida.',
            'dat_termino.date' => 'A data de término deve ser uma data válida.',
            'dat_termino.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'val_trabalhador.numeric' => 'O valor do trabalhador deve ser um número.',
            'val_venista.numeric' => 'O valor do venista deve ser um número.',
            'val_camiseta.numeric' => 'O valor da camiseta deve ser um número.',
            'val_entrada.numeric' => 'O valor de entrada deve ser um número.',
            'med_foto.image' => 'O arquivo deve ser uma imagem.',
            'med_foto.max' => 'O tamanho da imagem não pode exceder 2MB.',
            'ind_pos_encontro.required' => 'O campo pós encontro é obrigatório.',
            'ind_pos_encontro.boolean' => 'O campo pós encontro deve ser verdadeiro ou falso.',
        ];
    }
}
