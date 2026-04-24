<?php

namespace App\Http\Requests;

use App\Models\Equipe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EquipeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Equipe::class);
    }

    public function rules(): array
    {
        return [
            'nom_equipe' => ['required', 'string', 'max:60'],
            // des_slug e auto-gerado pelo mutator quando omitido; se enviado, deve ser unico no movimento.
            'des_slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('equipes', 'des_slug')
                    ->where('idt_movimento', $this->idtMovimentoUsuario()),
            ],
            'des_descricao' => ['nullable', 'string', 'max:500'],
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
        ];
    }

    private function idtMovimentoUsuario(): ?int
    {
        return DB::table('equipe_usuario')
            ->join('equipes', 'equipes.idt_equipe', '=', 'equipe_usuario.idt_equipe')
            ->where('equipe_usuario.user_id', Auth::id())
            ->whereNull('equipe_usuario.deleted_at')
            ->value('equipes.idt_movimento');
    }
}
