<x-layouts.app :title="'Pessoa'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ $pessoa->exists ? 'Editar Pessoa' : 'Nova Pessoa' }}</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">
                {{ $pessoa->exists ? 'Edite os dados da pessoa' : 'Cadastre uma nova pessoa com dados básicos e restrições de saúde' }}
            </p>
        </div>

        @if (Auth::user()->isAdmin())
            <div class="flex justify-end mt-4">
                <a href="{{ route('pessoas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                    Pessoas
                </a>
            </div>
        @endif

        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-md shadow p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                <x-heroicon-o-user-plus class="text-blue-600 w-6 h-6" /> Dados da Pessoa
            </h2>

            <form method="POST"
                action="{{ $pessoa->exists ? route('pessoas.update', $pessoa) : route('pessoas.store') }}"
                enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if ($pessoa->exists)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nom_pessoa" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nome <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nom_pessoa" name="nom_pessoa" maxlength="255"
                            value="{{ old('nom_pessoa', $pessoa->nom_pessoa) }}" placeholder="Digite o nome completo"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nom_pessoa') border-red-500 @enderror" />
                        @error('nom_pessoa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nom_apelido" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Apelido
                        </label>
                        <input type="text" id="nom_apelido" name="nom_apelido" maxlength="255"
                            value="{{ old('nom_apelido', $pessoa->nom_apelido) }}"
                            placeholder="Como prefere ser chamado"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{
                        datNascimento: '{{ old('dat_nascimento', $pessoa->getDataNascimentoFormatada()) }}',
                        menorDeIdade: false,
                        calcularIdade() {
                            if (!this.datNascimento) return;
                            const nascimento = new Date(this.datNascimento);
                            const hoje = new Date();
                            let idade = hoje.getFullYear() - nascimento.getFullYear();
                            const m = hoje.getMonth() - nascimento.getMonth();
                            if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) {
                                idade--;
                            }
                            this.menorDeIdade = idade < 18;
                        }
                        }"
                        x-init="calcularIdade()">
                        <div>
                        <label for="tel_pessoa"
                            class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone</label>
                        <input type="tel" id="tel_pessoa" name="tel_pessoa"
                            value="{{ old('tel_pessoa', $pessoa->tel_pessoa) }}" placeholder="(99) 99999-9999"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100" />
                    </div>
                        <div>

                            <label for="dat_nascimento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Data de Nascimento <span class="text-red-600">*</span></label>
                            <input type="date" id="dat_nascimento" name="dat_nascimento" x-model="datNascimento"
                                @change="calcularIdade()"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100" />
                            @error('dat_nascimento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="menor_idade" :value="menorDeIdade ? 1 : 2">

                        <template x-if="menorDeIdade">
                            <div
                                class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 bg-blue-100 dark:bg-zinc-900/20 p-4 rounded-lg border border-gray-400 dark:border-gray-300">
                                <div class="md:col-span-2">
                                    <p class="text-gray-800 dark:text-gray-300 font-semibold text-sm mb-2">
                                        Informações
                                        do Responsável (Obrigatório para menores)</p>
                                    <div />

                                    <div>
                                        <label for="nom_responsavel"
                                            class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do
                                            Responsável <span class="text-red-600">*</span></label>
                                        <input type="text" id="nom_responsavel" name="nom_responsavel"
                                            value="{{ old('nom_responsavel', $pessoa->nom_responsavel) }}"
                                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('nom_responsavel') border-red-500 @enderror" />
                                        @error('nom_responsavel')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="tel_responsavel"
                                            class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone
                                            do
                                            Responsável <span class="text-red-600">*</span></label>
                                        <input type="tel" id="tel_responsavel" name="tel_responsavel"
                                            value="{{ old('tel_responsavel', $pessoa->tel_responsavel) }}"
                                            placeholder="(99) 99999-9999"
                                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('tel_responsavel') border-red-500 @enderror" />
                                        @error('tel_responsavel')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </template>


                        <div>
                            <label for="eml_pessoa"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                <span class="text-red-600">*</span></label>
                            <input type="email" id="eml_pessoa" name="eml_pessoa"
                                value="{{ old('eml_pessoa', $pessoa->eml_pessoa) }}"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100" />
                        </div>

                        <div>
                            <label for="des_endereco"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Endereço</label>
                            <input type="text" id="des_endereco" name="des_endereco"
                                value="{{ old('des_endereco', $pessoa->des_endereco) }}"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100" />
                        </div>

                        <div>
                            <label for="tam_camiseta"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Tamanho da Camiseta
                                <span class="text-red-600">*</span></label>
                            <select id="tam_camiseta" name="tam_camiseta"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100">
                                <option value="">Selecione o tamanho</option>
                                @foreach (['PP', 'P', 'M', 'G', 'GG', 'EG'] as $tamanho)
                                    <option value="{{ $tamanho }}"
                                        {{ old('tam_camiseta', $pessoa->tam_camiseta) == $tamanho ? 'selected' : '' }}>
                                        {{ $tamanho }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tip_genero"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Sexo
                                <span class="text-red-600">*</span></label>
                            <select id="tip_genero" name="tip_genero"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100">
                                <option value="">Selecione o sexo</option>
                                <option value="M"
                                    {{ old('tip_genero', $pessoa->tip_genero) == 'M' ? 'selected' : '' }}>Masculino
                                </option>
                                <option value="F"
                                    {{ old('tip_genero', $pessoa->tip_genero) == 'F' ? 'selected' : '' }}>Feminino
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ estadoCivil: '{{ old('tip_estado_civil', $pessoa->tip_estado_civil) }}' }">

                            <div>
                                <label for="tip_estado_civil"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Estado Civil <span
                                        class="text-red-600">*</span></label>
                                @php
                                    $estadosCivis = [
                                        'S' => 'Solteiro(a)',
                                        'C' => 'Casado(a)',
                                        'E' => 'Casado(a) em 2ª União',
                                        'U' => 'União Estável',
                                        'M' => 'Casado(a) somente 1 participará',
                                        'D' => 'Divorciado(a)',
                                        'V' => 'Viúvo(a)',
                                    ];
                                @endphp
                                <select id="tip_estado_civil" name="tip_estado_civil" x-model="estadoCivil"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100">
                                    <option value="">Selecione o estado civil</option>
                                    @foreach ($estadosCivis as $sigla => $descricao)
                                        <option value="{{ $sigla }}">{{ $descricao }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="['C', 'E', 'U'].includes(estadoCivil)" x-cloak>
                                <label for="idt_parceiro"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Cônjuge</label>
                                <select id="idt_parceiro" name="idt_parceiro"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100">
                                    <option value="">Selecione o(a) cônjuge</option>
                                    @foreach ($pessoasDisponiveis as $id => $nome)
                                        <option value="{{ $id }}"
                                            {{ old('idt_parceiro', $pessoa->idt_parceiro) == $id ? 'selected' : '' }}>
                                            {{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label for="med_foto"
                                        class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Foto para o
                                        Carômetro</label>
                                    <input type="file" id="med_foto" name="med_foto" accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300" />
                                </div>
                                @if ($pessoa->exists && $pessoa->foto)
                                    <div class="mb-4">
                                        <img src="{{ asset('storage/' . $pessoa->foto->med_foto) }}"
                                            alt="Foto de {{ $pessoa->nom_pessoa }}"
                                            class="w-48 h-auto rounded shadow border border-gray-300 dark:border-zinc-600">
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label for="tip_habilidade"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Habilidade
                                    Principal</label>
                                @php
                                    $habilidades = [
                                        'V' => 'Toco violão',
                                        'S' => 'Toco outro instrumento',
                                        'C' => 'Sei cantar',
                                        'M' => 'Trabalho com mídias sociais',
                                        'A' => 'Crio material audiovisual',
                                        'T' => 'Desenvolvo APPs ou Sites',
                                        'F' => 'Fotografo',
                                        'O' => 'Outra habilidade',
                                    ];
                                @endphp
                                <select id="tip_habilidade" name="tip_habilidade"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecione uma habilidade</option>
                                    @foreach ($habilidades as $sigla => $label)
                                        <option value="{{ $sigla }}"
                                            {{ old('tip_habilidade', $pessoa->tip_habilidade) == $sigla ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                <p id="dat_termino_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    São habilidades que precisamos no momento
                                </p>
                            </div>

                        </div>
                    </div>

                    <div x-data="{ mostrarRestricoes: {{ old('ind_restricao', $pessoa->ind_restricao ?? false) ? 'true' : 'false' }} }">
                        <label
                            class="flex items-start gap-4 p-4 rounded-lg border-2 cursor-pointer transition-colors border-amber-300 bg-amber-50 hover:bg-amber-100 dark:border-amber-500 dark:bg-amber-900/20">
                            <div class="pt-0.5">
                                <input type="checkbox" name="ind_restricao" value="1"
                                    x-model="mostrarRestricoes"
                                    class="w-5 h-5 rounded border-amber-400 text-amber-500 focus:ring-amber-400">
                            </div>
                            <div class="flex-1">
                                <span class="block font-semibold text-amber-800 dark:text-amber-300">Informações de
                                    saúde</span>
                                <span class="text-sm text-amber-700 dark:text-amber-400">Alergias, restrições
                                    alimentares
                                    ou necessidades especiais?</span>
                            </div>
                            <x-heroicon-o-heart class="w-6 h-6 text-amber-400 ml-auto shrink-0" />
                        </label>

                        <div x-show="mostrarRestricoes" x-transition
                            class="mt-3 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-md p-4">
                            <h3
                                class="text-base font-medium mb-4 text-gray-900 dark:text-gray-100 border-b dark:border-zinc-700 pb-2">
                                Especifique as Restrições</h3>
                            <div class="space-y-5">
                                @foreach ($restricoes as $restricao)
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="restricoes[{{ $restricao->idt_restricao }}]"
                                                id="restricao_{{ $restricao->idt_restricao }}" value="1"
                                                {{ in_array($restricao->idt_restricao, $pessoa->restricoes->pluck('idt_restricao')->toArray()) ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 rounded">
                                            <label for="restricao_{{ $restricao->idt_restricao }}"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $restricao->des_restricao }}</label>
                                        </div>
                                        <input type="text" name="complementos[{{ $restricao->idt_restricao }}]"
                                            value="{{ old("complementos.{$restricao->idt_restricao}", $pessoa->restricoes->where('idt_restricao', $restricao->idt_restricao)->first()->txt_complemento ?? '') }}"
                                            placeholder="Detalhes adicionais..."
                                            class="w-full px-3 py-2 text-sm rounded-md border dark:bg-zinc-900 dark:text-gray-100" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex gap-3 justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <x-heroicon-o-check class="w-5 h-5 mr-2" /> Salvar
                        </button>
                    </div>
            </form>
        </div>
    </section>
</x-layouts.app>
