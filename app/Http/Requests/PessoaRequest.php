<?php

namespace App\Http\Requests;

use App\Enums\EstadoCivil;
use App\Enums\Genero;
use App\Enums\HabilidadePrincipal;
use App\Enums\TamanhoCamiseta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
            'eml_pessoa' => ['required', 'email', 'max:255', 
                Rule::unique('pessoa', 'eml_pessoa')->ignore($this->pessoa, 'idt_pessoa')
            ],
            'tam_camiseta' => ['required', new Enum(TamanhoCamiseta::class)],
            'tip_genero' => ['required', new Enum(Genero::class)],
            'tip_estado_civil' => ['nullable', new Enum(EstadoCivil::class)],
            'tip_habilidade' => ['nullable', new Enum(HabilidadePrincipal::class)],
            'idt_parceiro' => ['nullable', 'exists:pessoa,idt_pessoa'],
            'med_foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'ind_restricao'  => ['nullable', 'boolean'],
            'restricoes'     => ['nullable', 'array'],
            'restricoes.*'   => ['boolean'],
            'complementos'   => ['nullable', 'array'],
            'complementos.*' => ['nullable', 'string', 'max:255'],
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
            'dat_nascimento.after' => 'A data de nascimento deve ser posterior a 01/01/1925.',
            'des_endereco.min' => 'O endereço deve conter pelo menos 10 caracteres.',
            'des_endereco.max' => 'O endereço não pode ter mais de 255 caracteres.',
            'eml_pessoa.required' => 'O e-mail é obrigatório.',
            'eml_pessoa.email' => 'Informe um e-mail válido.',
            'eml_pessoa.max' => 'O e-mail não pode ter mais de 255 caracteres.',

            'tam_camiseta.required' => 'Informe o tamanho da camiseta.',
            'tam_camiseta.Illuminate\Validation\Rules\Enum' => 'O tamanho da camiseta selecionado é inválido.',

            'tip_genero.required' => 'Informe o sexo.',
            'tip_genero.Illuminate\Validation\Rules\Enum' => 'Sexo inválido.',

            'tip_estado_civil.Illuminate\Validation\Rules\Enum' => 'Estado civil inválido.',
            'tip_habilidade.Illuminate\Validation\Rules\Enum' => 'Habilidade inválida.',

            'med_foto.image' => 'O arquivo deve ser uma imagem.',
            'med_foto.mimes' => 'A imagem deve estar nos formatos: jpeg, png, jpg, webp.',
            'med_foto.max' => 'A imagem não pode ter mais de 5MB.',
        ];
    }
}
