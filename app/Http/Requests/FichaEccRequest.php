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
            'cpf_conjuge.string'              => 'O CPF do cônjuge deve ser um texto.',
            'cpf_conjuge.max'                 => 'O CPF do cônjuge deve ter no máximo 14 caracteres.',
            'nom_conjuge.required'            => 'O nome do cônjuge é obrigatório.',
            'nom_conjuge.string'              => 'O nome do cônjuge deve ser um texto.',
            'nom_conjuge.max'                 => 'O nome do cônjuge deve ter no máximo 255 caracteres.',
            'nom_apelido_conjuge.string'      => 'O apelido do cônjuge deve ser um texto.',
            'nom_apelido_conjuge.max'         => 'O apelido do cônjuge deve ter no máximo 100 caracteres.',
            'tip_genero_conjuge.required'     => 'O gênero do cônjuge é obrigatório.',
            'tip_genero_conjuge.string'       => 'O gênero do cônjuge deve ser um texto.',
            'tip_genero_conjuge.max'          => 'O gênero do cônjuge deve ter no máximo 3 caracteres.',
            'dat_nascimento_conjuge.required' => 'A data de nascimento do cônjuge é obrigatória.',
            'dat_nascimento_conjuge.date'     => 'A data de nascimento do cônjuge deve ser uma data válida.',
            'tel_conjuge.string'              => 'O telefone do cônjuge deve ser um texto.',
            'tel_conjuge.max'                 => 'O telefone do cônjuge deve ter no máximo 20 caracteres.',
            'eml_conjuge.email'               => 'O e-mail do cônjuge deve ser um endereço de e-mail válido.',
            'eml_conjuge.max'                 => 'O e-mail do cônjuge deve ter no máximo 255 caracteres.',
            'nom_profissao_conjuge.string'    => 'A profissão do cônjuge deve ser um texto.',
            'nom_profissao_conjuge.max'       => 'A profissão do cônjuge deve ter no máximo 150 caracteres.',
            'ind_catolico_conjuge.boolean'    => 'O campo "É católico?" do cônjuge deve ser verdadeiro ou falso.',
            'tip_habilidade_conjuge.string'   => 'A habilidade do cônjuge deve ser um texto.',
            'tip_habilidade_conjuge.max'      => 'A habilidade do cônjuge deve ter no máximo 3 caracteres.',
            'tam_camiseta_conjuge.required'   => 'O tamanho da camiseta do cônjuge é obrigatório.',
            'tam_camiseta_conjuge.string'     => 'O tamanho da camiseta do cônjuge deve ser um texto.',
            'tam_camiseta_conjuge.max'        => 'O tamanho da camiseta do cônjuge deve ter no máximo 3 caracteres.',

            // Informações comuns
            'tip_estado_civil.required' => 'O estado civil é obrigatório.',
            'tip_estado_civil.string'   => 'O estado civil deve ser um texto.',
            'tip_estado_civil.max'      => 'O estado civil deve ter no máximo 3 caracteres.',
            'nom_paroquia.string'       => 'O nome da paróquia deve ser um texto.',
            'nom_paroquia.max'          => 'O nome da paróquia deve ter no máximo 150 caracteres.',
            'dat_casamento.date'        => 'A data do casamento deve ser uma data válida.',
            'qtd_filhos.integer'        => 'A quantidade de filhos deve ser um número inteiro.',
            'qtd_filhos.min'            => 'A quantidade de filhos deve ser no mínimo 0.',
            'qtd_filhos.max'            => 'A quantidade de filhos deve ser no máximo 20.',

            // Foto
            'med_conjuge.image' => 'A foto do cônjuge deve ser uma imagem.',
            'med_conjuge.max'   => 'A foto do cônjuge não pode ultrapassar 4MB.',

            // Filhos
            'filhos.array'                  => 'Os filhos devem ser um array.',
            'filhos.max'                    => 'Não é possível cadastrar mais de 20 filhos.',
            'filhos.*.cpf_filho.string'     => 'O CPF do filho deve ser um texto.',
            'filhos.*.cpf_filho.max'        => 'O CPF do filho deve ter no máximo 14 caracteres.',
            'filhos.*.nom_filho.string'     => 'O nome do filho deve ser um texto.',
            'filhos.*.nom_filho.max'        => 'O nome do filho deve ter no máximo 255 caracteres.',
            'filhos.*.dat_nascimento_filho.date' => 'A data de nascimento do filho deve ser uma data válida.',
            'filhos.*.eml_filho.email'      => 'O e-mail do filho deve ser válido.',
            'filhos.*.eml_filho.max'        => 'O e-mail do filho deve ter no máximo 255 caracteres.',
            'filhos.*.tel_filho.string'     => 'O telefone do filho deve ser um texto.',
            'filhos.*.tel_filho.max'        => 'O telefone do filho deve ter no máximo 20 caracteres.',
        ]);
    }
}
