<?php

namespace App\Http\Requests;

class FichaEccRequest extends FichaRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [

            // ── Foto do cônjuge (upload) ──────────────────────────────────────
            'med_conjuge' => 'nullable|image|max:4096',

            // ── Cônjuge (tabela ficha_ecc) ────────────────────────────────────
            'cpf_conjuge'            => 'required|string|max:14',
            'nom_conjuge'            => 'required|string|max:255',
            'nom_apelido_conjuge'    => 'nullable|string|max:100',
            'tip_genero_conjuge'     => 'required|string|max:3',
            'dat_nascimento_conjuge' => 'required|date',
            'tel_conjuge'            => 'nullable|string|max:20',
            'eml_conjuge'            => 'nullable|email|max:255',
            'nom_profissao_conjuge'  => 'nullable|string|max:150',
            'ind_catolico_conjuge'   => 'nullable|boolean',
            'tip_habilidade_conjuge' => 'nullable|string|max:3',
            'tam_camiseta_conjuge'   => 'required|string|max:3',

            // ── Informações comuns do casal (tabela ficha_ecc) ───────────────
            'tip_estado_civil' => 'required|string|max:3',
            'nom_paroquia'     => 'nullable|string|max:150',
            'dat_casamento'    => 'nullable|date',
            'qtd_filhos'       => 'nullable|integer|min:0|max:20',

            // ── Filhos (array dinâmico → tabela ficha_ecc_filho) ─────────────
            'filhos'                        => 'nullable|array|max:20',
            'filhos.*.cpf_filho'            => 'nullable|string|max:14',
            'filhos.*.nom_filho'            => 'nullable|string|max:255',
            'filhos.*.dat_nascimento_filho' => 'nullable|date',
            'filhos.*.eml_filho'            => 'nullable|email|max:255',
            'filhos.*.tel_filho'            => 'nullable|string|max:20',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            // Cônjuge
            'cpf_conjuge.required'            => 'O CPF do cônjuge é obrigatório.',
            'nom_conjuge.required'            => 'O nome do cônjuge é obrigatório.',
            'tip_genero_conjuge.required'     => 'Informe o sexo do cônjuge.',
            'dat_nascimento_conjuge.required' => 'Informe a data de nascimento do cônjuge.',
            'tam_camiseta_conjuge.required'   => 'Informe o tamanho da camiseta do cônjuge.',

            // Informações comuns
            'tip_estado_civil.required' => 'Informe o regime do casal.',

            // Foto
            'med_conjuge.image' => 'A foto do cônjuge deve ser uma imagem.',
            'med_conjuge.max'   => 'A foto do cônjuge não pode ultrapassar 4MB.',

            // Filhos
            'filhos.*.eml_filho.email' => 'O e-mail do filho deve ser válido.',
        ]);
    }
}
