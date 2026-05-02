<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FichaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idt_evento' => 'required|exists:evento,idt_evento',
            'tip_genero' => 'required|string|max:3',
            'cpf_candidato' => 'nullable|string|max:20',
            'nom_candidato' => 'required|string|max:255',
            'nom_apelido' => 'nullable|string|max:255',
            'dat_nascimento' => 'required|date',
            'tel_candidato' => 'nullable|string|max:20',
            'eml_candidato' => 'required|email|max:255',
            'nom_profissao' => 'nullable|string|max:150',
            'des_endereco' => 'nullable|string|max:255',
            'tam_camiseta' => 'required|string|max:3',
            'tip_como_soube' => 'nullable|string|max:3',
            'tip_habilidade' => 'nullable|string|max:1',
            'ind_catolico' => 'nullable|boolean',
            'ind_toca_instrumento' => 'nullable|boolean',
            'ind_consentimento' => 'required|accepted',
            'ind_restricao' => 'required|boolean',
            'txt_observacao' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'idt_evento.required' => 'O evento é obrigatório.',
            'idt_evento.exists' => 'O evento selecionado não existe.',

            'tip_genero.required' => 'O gênero é obrigatório.',
            'tip_genero.string' => 'O gênero deve ser um texto.',
            'tip_genero.max' => 'O gênero deve ter no máximo 3 caracteres.',

            'cpf_candidato.string' => 'O CPF do candidato deve ser um texto.',
            'cpf_candidato.max' => 'O CPF do candidato deve ter no máximo 20 caracteres.',

            'nom_candidato.required' => 'O nome do candidato é obrigatório.',
            'nom_candidato.string' => 'O nome do candidato deve ser um texto.',
            'nom_candidato.max' => 'O nome do candidato deve ter no máximo 255 caracteres.',

            'nom_apelido.string' => 'O apelido deve ser um texto.',
            'nom_apelido.max' => 'O apelido deve ter no máximo 255 caracteres.',

            'dat_nascimento.required' => 'A data de nascimento é obrigatória.',
            'dat_nascimento.date' => 'A data de nascimento deve ser uma data válida.',

            'tel_candidato.string' => 'O telefone deve ser um texto.',
            'tel_candidato.max' => 'O telefone deve ter no máximo 20 caracteres.',

            'eml_candidato.required' => 'O e-mail é obrigatório.',
            'eml_candidato.email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'eml_candidato.max' => 'O e-mail deve ter no máximo 255 caracteres.',

            'nom_profissao.string' => 'A profissão deve ser um texto.',
            'nom_profissao.max' => 'A profissão deve ter no máximo 150 caracteres.',

            'des_endereco.string' => 'O endereço deve ser um texto.',
            'des_endereco.max' => 'O endereço deve ter no máximo 255 caracteres.',

            'tam_camiseta.required' => 'O tamanho da camiseta é obrigatório.',
            'tam_camiseta.string' => 'O tamanho da camiseta deve ser um texto.',
            'tam_camiseta.max' => 'O tamanho da camiseta deve ter no máximo 3 caracteres.',

            'tip_como_soube.string' => 'O campo "Como soube do encontro" deve ser um texto.',
            'tip_como_soube.max' => 'O campo "Como soube do encontro" deve ter no máximo 3 caracteres.',

            'tip_habilidade.string' => 'A habilidade deve ser um texto.',
            'tip_habilidade.max' => 'A habilidade deve ter no máximo 1 caractere.',

            'ind_catolico.boolean' => 'O campo "É católico?" deve ser verdadeiro ou falso.',

            'ind_toca_instrumento.boolean' => 'O campo "Toca instrumento?" deve ser verdadeiro ou falso.',

            'ind_consentimento.required' => 'O consentimento é obrigatório.',
            'ind_consentimento.accepted' => 'Você deve aceitar o consentimento.',

            'ind_restricao.required' => 'O campo restrição é obrigatório.',
            'ind_restricao.boolean' => 'O campo restrição deve ser verdadeiro ou falso.',

            'txt_observacao.string' => 'As observações devem ser um texto.',
        ];
    }
}
