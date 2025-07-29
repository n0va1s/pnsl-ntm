<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PessoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_pessoa' => ['required', 'string', 'max:255'],
            'nom_apelido' => ['nullable', 'string', 'max:255'],
            'tel_pessoa' => ['nullable', 'regex:/^\(?\d{2}\)?[\s-]?\d{4,5}-?\d{4}$/'],
            'dat_nascimento' => ['required', 'date', 'before:today', 'after:1925-01-01'],
            'des_endereco' => ['nullable', 'string', 'min:10', 'max:255'],
            'eml_pessoa' => ['required', 'email', 'max:255'],
            'tam_camiseta' => ['required', 'string', 'in:PP,P,M,G,GG,EG'],
            'tip_genero' => ['required', 'string', 'in:M,F,O'], // m=masculino, f=feminino, n=não informado
            'ind_toca_violao' => ['boolean'],
            'ind_consentimento' => ['boolean'],
            'ind_restricao' => ['boolean'],
            'med_foto' => ['nullable', 'image', 'max:2048'], // até 2MB
            'idt_parceiro' => ['nullable', 'exists:pessoa,idt_pessoa'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_pessoa.required' => 'O nome da pessoa é obrigatório.',
            'nom_pessoa.max' => 'O nome da pessoa não pode ter mais de 255 caracteres.',

            'nom_apelido.max' => 'O apelido não pode ter mais de 255 caracteres.',

            'tel_pessoa.regex' => 'O telefone deve estar em um formato válido. Ex: (99) 99999-9999.',

            'dat_nascimento.required' => 'A data de nascimento é obrigatória.',
            'dat_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'dat_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'dat_nascimento.after' => 'A data de nascimento deve ser posterior a 01/01/1900.',

            'des_endereco.min' => 'O endereço deve conter pelo menos 10 caracteres.',
            'des_endereco.max' => 'O endereço não pode ter mais de 255 caracteres.',

            'eml_pessoa.required' => 'O e-mail é obrigatório.',
            'eml_pessoa.email' => 'Informe um e-mail válido.',
            'eml_pessoa.max' => 'O e-mail não pode ter mais de 255 caracteres.',

            'tam_camiseta.required' => 'Informe o tamanho da camiseta.',
            'tam_camiseta.in' => 'O tamanho da camiseta deve ser válido (ex: P, M, G, GG).',

            'tip_genero.required' => 'Informe o gênero.',
            'tip_genero.in' => 'Gênero inválido.',

            'url_foto.image' => 'O arquivo deve ser uma imagem.',
            'url_foto.mimes' => 'A imagem deve estar nos formatos: jpeg, png, jpg, webp.',
            'url_foto.max' => 'A imagem não pode ter mais de 2MB.',

            'ind_toca_violao.boolean' => 'Valor inválido para o campo "Toca Violão".',
            'ind_consentimento.boolean' => 'Valor inválido para o campo "Consentimento".',
        ];
    }
}
