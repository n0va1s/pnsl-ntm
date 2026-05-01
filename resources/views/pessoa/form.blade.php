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

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Foto para o Carômetro
                </label>

                <div class="flex flex-col items-center justify-center mb-6">
                    <label for="med_foto" class="cursor-pointer group relative flex flex-col items-center">
                        <div class="w-32 h-32 rounded-full border-2 border-dashed
                            @error('med_foto') border-red-500 bg-red-50/10 @else border-gray-300 dark:border-zinc-600 bg-gray-50 dark:bg-zinc-800/50 @enderror
                            flex flex-col items-center justify-center overflow-hidden shadow transition hover:shadow-md hover:border-gray-400 dark:hover:border-zinc-500">

                            @if ($pessoa->exists && $pessoa->foto && $pessoa->foto->med_foto)
                                <img src="{{ asset('storage/' . $pessoa->foto->med_foto) }}"
                                    alt="Foto de {{ $pessoa->nom_pessoa }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="flex flex-col items-center justify-center p-4 text-center">
                                    <x-heroicon-o-photo class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                                    <span class="mt-1 text-[10px] font-medium text-gray-500 dark:text-zinc-400 leading-tight">
                                        Clique para selecionar
                                    </span>
                                </div>
                            @endif

                            <input type="file" id="med_foto" name="med_foto" accept="image/*" class="sr-only"
                                onchange="document.getElementById('nome-arquivo').textContent = this.files[0] ? this.files[0].name : 'Nenhum arquivo escolhido'">
                        </div>
                    </label>

                    <span id="nome-arquivo" class="mt-2 text-xs text-gray-500 dark:text-zinc-400 text-center truncate max-w-[128px]">
                        @if ($pessoa->exists && $pessoa->foto && $pessoa->foto->med_foto)
                            Imagem atual
                        @else
                            Nenhum arquivo escolhido
                        @endif
                    </span>
                </div>

                @error('med_foto')
                    <div class="flex justify-center -mt-4 mb-6">
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    </div>
                @enderror
            </div>

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
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nom_apelido') border-red-500 @enderror" />
                        @error('nom_apelido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tel_pessoa" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone</label>
                        <input type="tel" id="tel_pessoa" name="tel_pessoa" value="{{ old('tel_pessoa', $pessoa->tel_pessoa) }}" placeholder="(99) 99999-9999"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('tel_pessoa') border-red-500 @enderror" />
                        @error('tel_pessoa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="dat_nascimento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Data de Nascimento <span class="text-red-600">*</span></label>
                        <input type="date" id="dat_nascimento" name="dat_nascimento" value="{{ old('dat_nascimento', $pessoa->getDataNascimentoFormatada()) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('dat_nascimento') border-red-500 @enderror" />
                        @error('dat_nascimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="eml_pessoa" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-600">*</span></label>
                        <input type="email" id="eml_pessoa" name="eml_pessoa" value="{{ old('eml_pessoa', $pessoa->eml_pessoa) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('eml_pessoa') border-red-500 @enderror" />
                        @error('eml_pessoa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="des_endereco" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Endereço</label>
                        <input type="text" id="des_endereco" name="des_endereco" value="{{ old('des_endereco', $pessoa->des_endereco) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100
                            @error('des_endereco') border-red-500 @enderror"/>
                        @error('des_endereco')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tam_camiseta" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Tamanho da Camiseta <span class="text-red-600">*</span></label>
                        <select id="tam_camiseta" name="tam_camiseta" class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('tam_camiseta') border-red-500 @enderror">
                            <option value="">Selecione o tamanho</option>
                            @foreach(\App\Enums\TamanhoCamiseta::cases() as $tamanho)
                                <option value="{{ $tamanho->value }}"
                                    {{ old('tam_camiseta', $pessoa->tam_camiseta?->value) == $tamanho->value ? 'selected' : '' }}>
                                    {{ $tamanho->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('tam_camiseta')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tip_genero" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Sexo <span class="text-red-600">*</span></label>
                        <select id="tip_genero" name="tip_genero" class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('tip_genero') border-red-500 @enderror">
                            @foreach(\App\Enums\Genero::cases() as $genero)
                                <option value="{{ $genero->value }}"
                                    {{ old('tip_genero', $pessoa->tip_genero) == $genero->value ? 'selected' : '' }}>
                                    {{ $genero->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('tip_genero')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ estadoCivil: '{{ old('tip_estado_civil', $pessoa->tip_estado_civil) }}' }">

                        <div>
                            <label for="tip_estado_civil" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Estado Civil <span class="text-red-600">*</span></label>
                            <select id="tip_estado_civil" name="tip_estado_civil" x-model="estadoCivil" class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('tip_estado_civil') border-red-500 @enderror">
                                <option value="">Selecione o estado civil</option>
                                @foreach(\App\Enums\EstadoCivil::cases() as $estadoCivil)
                                    <option value="{{ $estadoCivil->value }}"
                                        {{ old('tip_estado_civil', $pessoa->tip_estado_civil) == $estadoCivil->value ? 'selected' : '' }}>
                                        {{ $estadoCivil->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tip_estado_civil')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="['C', 'E', 'U'].includes(estadoCivil)" x-cloak>
                            <label for="idt_parceiro" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Cônjuge</label>
                            <select id="idt_parceiro" name="idt_parceiro" class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 @error('idt_parceiro') border-red-500 @enderror">
                                <option value="">Selecione o(a) cônjuge</option>
                                @foreach ($pessoasDisponiveis as $id => $nome)
                                    <option value="{{ $id }}"
                                        {{ old('idt_parceiro', $pessoa->idt_parceiro) == $id ? 'selected' : '' }}>
                                        {{ $nome }}</option>
                                @endforeach

                            </select>
                            @error('idt_parceiro')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Foto e Habilidade --}}
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                        <div>
                            <label for="tip_habilidade" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Habilidade Principal</label>
                            <select id="tip_habilidade" name="tip_habilidade" class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 @error('tip_habilidade') border-red-500 @enderror">
                                <option value="">Selecione uma habilidade</option>
                                @foreach(\App\Enums\HabilidadePrincipal::cases() as $habilidade)
                                    <option value="{{ $habilidade->value }}"
                                        {{ old('tip_habilidade', $pessoa->tip_habilidade instanceof \BackedEnum ? $pessoa->tip_habilidade->value : $pessoa->tip_habilidade) == $habilidade->value ? 'selected' : '' }}>
                                        {{ $habilidade->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tip_habilidade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p id="dat_termino_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                São habilidades que precisamos no momento
                            </p>
                        </div>
                    </div>
                </div>

                <div x-data="{ mostrarRestricoes: {{ old('ind_restricao', $pessoa->ind_restricao ?? false) ? 'true' : 'false' }} }">
                    <label
                        class="flex items-start gap-4 p-4 rounded-lg border-2 cursor-pointer transition-colors
                        border-amber-300 bg-amber-50 hover:bg-amber-100
                        dark:border-amber-500 dark:bg-amber-900/20 dark:hover:bg-amber-900/30"
                        aria-describedby="saude-hint">
                        <div class="flex items-center pt-0.5">
                            <input type="hidden" name="ind_restricao" value="0">
                            <input type="checkbox" name="ind_restricao" value="1" x-model="mostrarRestricoes"
                                x-bind:disabled="bloqueado"
                                {{ old('ind_restricao', $pessoa->ind_restricao) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-amber-400 text-amber-500 focus:ring-amber-400">
                        </div>
                        <div>
                            <span class="block font-semibold text-amber-800 dark:text-amber-300">
                                Informações de saúde
                            </span>
                            <span id="saude-hint" class="text-sm text-amber-700 dark:text-amber-400">
                                Há alguma informação sobre sua saúde que julga importante sabermos? (alergias,
                                restrições alimentares, necessidades especiais)
                            </span>
                        </div>
                        <x-heroicon-o-heart class="w-6 h-6 text-amber-400 dark:text-amber-500 ml-auto shrink-0"
                            aria-hidden="true" />
                    </label>

                    <div x-show="mostrarRestricoes" x-transition
                        class="mt-3 bg-gray-50 dark:bg-zinc-700 rounded-md p-4" role="region"
                        aria-label="Restrições e Alergias">
                        <h3 class="text-base sm:text-lg font-medium mb-3 text-gray-900 dark:text-gray-100">
                            Restrições e Alergias
                        </h3>
                        <div class="space-y-4">
                            @php
                                $relacaoSaude = $pessoa->restricoes ?? collect();
                                $restricoesSelecionadas = $relacaoSaude->pluck('idt_restricao')->toArray();
                                $complementos = $relacaoSaude->mapWithKeys(function ($item) {
                                    return [$item->idt_restricao => $item->pivot->txt_complemento ?? ''];
                                })->toArray();
                            @endphp

                            @foreach ($restricoes as $restricao)
                                @php
                                    $checked = in_array($restricao->idt_restricao, $restricoesSelecionadas);
                                    $complemento = old(
                                        "complementos.{$restricao->idt_restricao}",
                                        $complementos[$restricao->idt_restricao] ?? '',
                                    );
                                @endphp

                                <div class="space-y-2"
                                    x-data="{
                                        selecionado: {{ $checked ? 'true' : 'false' }},
                                        texto: '{{ addslashes($complemento) }}'
                                    }">

                                    <div class="flex items-center gap-2">
                                        <input type="checkbox"
                                            name="restricoes[{{ $restricao->idt_restricao }}]"
                                            id="restricao_{{ $restricao->idt_restricao }}"
                                            value="1"
                                            x-model="selecionado"
                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />

                                        <label for="restricao_{{ $restricao->idt_restricao }}"
                                            class="text-gray-800 dark:text-gray-100 flex items-center gap-2 cursor-pointer">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-zinc-600 dark:text-gray-300">
                                                {{ $restricao->getTipo() }}
                                            </span>
                                            <span class="text-sm">{{ $restricao->des_restricao }}</span>
                                        </label>
                                    </div>

                                    <input type="text"
                                        name="complementos[{{ $restricao->idt_restricao }}]"
                                        x-model="texto"
                                        @input="if(texto.trim().length > 0) selecionado = true"
                                        placeholder="Complemento ou detalhes adicionais"
                                        maxlength="255"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-zinc-800" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <x-heroicon-o-check class="w-5 h-5 mr-2" /> Salvar
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
