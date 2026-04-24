<?php

namespace App\Http\Requests;

use App\Models\Equipe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('equipe'));
    }

    public function rules(): array
    {
        /** @var Equipe $equipe */
        $equipe = $this->route('equipe');

        return [
            'nom_equipe' => ['required', 'string', 'max:60'],
            'des_slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('equipes', 'des_slug')
                    ->where('idt_movimento', $equipe->idt_movimento)
                    ->ignore($equipe->idt_equipe, 'idt_equipe'),
            ],
            'des_descricao' => ['nullable', 'string', 'max:500'],
            'ind_ativa' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_equipe.required' => 'O nome da equipe é obrigatório.',
            'nom_equipe.max' => 'O nome da equipe não pode ter mais de 60 caracteres.',
            'des_slug.unique' => 'Já existe uma equipe com esse slug neste movimento.',
            'des_slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
            'des_descricao.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'ind_ativa.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }
}
