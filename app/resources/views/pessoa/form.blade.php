<x-layouts.app :title="'Pessoa'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ $pessoa->exists ? 'Editar Pessoa' : 'Nova Pessoa' }}</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">
                {{ $pessoa->exists ? 'Edite os dados da pessoa' : 'Cadastre uma nova pessoa com dados básicos e restrições de saúde' }}
            </p>
        </div>

        <div class="flex justify-end mt-4">
            <a href="{{ route('pessoas.index') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Voltar para a lista de pessoas">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Pessoas
            </a>
        </div>

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
                    <!-- Nome -->
                    <div>
                        <label for="nom_pessoa" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nome <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nom_pessoa" name="nom_pessoa" maxlength="255"
                            value="{{ old('nom_pessoa', $pessoa->nom_pessoa) }}" placeholder="Digite o nome completo"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('nom_pessoa') border-red-500 @enderror" />
                        @error('nom_pessoa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nome completo da pessoa</p>
                    </div>

                    <!-- Apelido -->
                    <div>
                        <label for="nom_apelido" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Apelido
                        </label>
                        <input type="text" id="nom_apelido" name="nom_apelido" maxlength="255"
                            value="{{ old('nom_apelido', $pessoa->nom_apelido) }}"
                            placeholder="Como prefere ser chamado"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('nom_apelido') border-red-500 @enderror" />
                        @error('nom_apelido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Apelido ou nome de preferência
                            (opcional)</p>
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="tel_pessoa" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Telefone
                        </label>
                        <input type="tel" id="tel_pessoa" name="tel_pessoa"
                            value="{{ old('tel_pessoa', $pessoa->tel_pessoa) }}" placeholder="(99) 99999-9999"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('tel_pessoa') border-red-500 @enderror" />
                        @error('tel_pessoa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Número de telefone com DDD</p>
                    </div>

                    <!-- Data de Nascimento -->
                    <div>
                        <label for="dat_nascimento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Data de Nascimento <span class="text-red-600">*</span>
                        </label>
                        <input type="date" id="dat_nascimento" name="dat_nascimento"
                            value="{{ old('dat_nascimento', $pessoa->getDataNascimentoFormatada()) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('dat_nascimento') border-red-500 @enderror" />
                        @error('dat_nascimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data de nascimento da pessoa</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="eml_pessoa" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input type="email" id="eml_pessoa" name="eml_pessoa" maxlength="255"
                            value="{{ old('eml_pessoa', $pessoa->eml_pessoa) }}" placeholder="email@exemplo.com"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('eml_pessoa') border-red-500 @enderror" />
                        @error('eml_pessoa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Endereço de e-mail válido</p>
                    </div>

                    <!-- Endereço -->
                    <div>
                        <label for="des_endereco" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Endereço
                        </label>
                        <input type="text" id="des_endereco" name="des_endereco" maxlength="255"
                            value="{{ old('des_endereco', $pessoa->des_endereco) }}"
                            placeholder="Rua, número, bairro, cidade"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('des_endereco') border-red-500 @enderror" />
                        @error('des_endereco')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Endereço completo (opcional)</p>
                    </div>

                    <!-- Tamanho da Camiseta -->
                    <div>
                        <label for="tam_camiseta" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tamanho da Camiseta <span class="text-red-600">*</span>
                        </label>
                        <select id="tam_camiseta" name="tam_camiseta"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('tam_camiseta') border-red-500 @enderror">
                            <option value="">Selecione o tamanho</option>
                            @foreach (['PP', 'P', 'M', 'G', 'GG', 'EG'] as $tamanho)
                                <option value="{{ $tamanho }}"
                                    {{ old('tam_camiseta', $pessoa->tam_camiseta) == $tamanho ? 'selected' : '' }}>
                                    {{ $tamanho }}
                                </option>
                            @endforeach
                        </select>
                        @error('tam_camiseta')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tamanho da camiseta para eventos</p>
                    </div>

                    <!-- Gênero -->
                    <div>
                        <label for="tip_genero" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Gênero <span class="text-red-600">*</span>
                        </label>
                        <select id="tip_genero" name="tip_genero"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 
                            @error('tip_genero') border-red-500 @enderror">
                            <option value="">Selecione o gênero</option>
                            <option value="M"
                                {{ old('tip_genero', $pessoa->tip_genero) == 'M' ? 'selected' : '' }}>Masculino
                            </option>
                            <option value="F"
                                {{ old('tip_genero', $pessoa->tip_genero) == 'F' ? 'selected' : '' }}>Feminino</option>
                            <option value="O"
                                {{ old('tip_genero', $pessoa->tip_genero) == 'O' ? 'selected' : '' }}>Não informado
                            </option>
                        </select>
                        @error('tip_genero')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sexo da pessoa</p>
                    </div>

                    <!-- Parceiro -->
                    <div>
                        <label for="idt_parceiro" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Parceiro(a) <span class="text-red-600">*</span>
                        </label>
                        <select id="idt_parceiro" name="idt_parceiro"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 
        @error('idt_parceiro') border-red-500 @enderror">
                            <option value="">Selecione o parceiro(a)</option>
                            @foreach ($pessoasDisponiveis as $disponivel)
                                <option value="{{ $disponivel->idt_pessoa }}"
                                    {{ old('idt_parceiro', $pessoa->idt_parceiro) == $disponivel->idt_pessoa ? 'selected' : '' }}>
                                    {{ $disponivel->nom_pessoa }}
                                </option>
                            @endforeach
                        </select>
                        @error('idt_parceiro')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">A pessoa da sua vida</p>
                    </div>
                </div>

                <!-- Foto -->
                <div class="flex items-start gap-6">
                    <div class="flex-1">
                        <label for="med_foto" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Foto para o Carômetro
                        </label>
                        <input type="file" id="med_foto" name="med_foto" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300 dark:hover:file:bg-blue-800
                            @error('med_foto') border-red-500 @enderror" />
                        @error('med_foto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Imagem até 2MB nos formatos JPG, JPEG,
                            PNG</p>
                    </div>

                    @if (isset($pessoa) && $pessoa->foto)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $pessoa->foto->med_foto) }}"
                                alt="Foto de {{ $pessoa->nom_pessoa }}"
                                class="w-48 h-auto rounded shadow border border-gray-300 dark:border-zinc-600">
                        </div>
                    @endif
                </div>

                <!-- Checkboxes -->
                <div class="space-y-3">
                    <!-- Toca Violão -->
                    <div class="flex items-center space-x-3">
                        <input type="hidden" name="ind_toca_violao" value="0">
                        <input type="checkbox" id="ind_toca_violao" name="ind_toca_violao" value="1"
                            {{ old('ind_toca_violao', $pessoa->ind_toca_violao) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />
                        <label for="ind_toca_violao"
                            class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                            Toca Violão
                        </label>
                    </div>
                    @error('ind_toca_violao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- Consentimento -->
                    <div class="flex items-center space-x-3">
                        <input type="hidden" name="ind_consentimento" value="0">
                        <input type="checkbox" id="ind_consentimento" name="ind_consentimento" value="1"
                            {{ old('ind_consentimento', $pessoa->ind_consentimento) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />
                        <label for="ind_consentimento"
                            class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                            Termo de Consentimento
                        </label>
                    </div>
                    @error('ind_consentimento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- Restrições Alimentares -->
                    <div x-data="{ mostrarRestricoes: {{ old('ind_restricao', $pessoa->ind_restricao ?? false) ? 'true' : 'false' }} }">
                        <div class="flex items-center space-x-3">
                            <input type="hidden" name="ind_restricao" value="0">
                            <input type="checkbox" id="ind_restricao" name="ind_restricao" value="1"
                                x-model="mostrarRestricoes"
                                {{ old('ind_restricao', $pessoa->ind_restricao) ? 'checked' : '' }}
                                class="w-5 h-5 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />
                            <label for="ind_restricao"
                                class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                Possui Restrição Alimentar
                            </label>
                        </div>
                        @error('ind_restricao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Marque se a pessoa possui alguma
                            restrição alimentar</p>

                        <!-- Seção de Restrições -->
                        <div x-show="mostrarRestricoes" x-transition
                            class="mt-4 bg-gray-50 dark:bg-zinc-700 rounded-md p-4">
                            <h3 class="text-lg font-medium mb-3 text-gray-900 dark:text-gray-100">Restrições
                                Alimentares</h3>
                            <div class="space-y-4">
                                @php
                                    $restricoesSelecionadas = $pessoa->restricoes->pluck('idt_restricao')->toArray();
                                    $complementos = $pessoa->restricoes
                                        ->pluck('txt_complemento', 'idt_restricao')
                                        ->toArray();
                                @endphp

                                @foreach ($restricoes as $tipo)
                                    @php
                                        $checked = in_array($tipo->idt_restricao, $restricoesSelecionadas);
                                        $complemento = old(
                                            "complementos.{$tipo->idt_restricao}",
                                            $complementos[$tipo->idt_restricao] ?? '',
                                        );
                                    @endphp

                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" name="restricoes[{{ $tipo->idt_restricao }}]"
                                                id="restricao_{{ $tipo->idt_restricao }}" value="1"
                                                {{ $checked ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />
                                            <label for="restricao_{{ $tipo->idt_restricao }}"
                                                class="text-gray-800 dark:text-gray-100 flex items-center space-x-2">
                                                <span
                                                    class="text-sm font-semibold px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-zinc-600 dark:text-gray-300">
                                                    {{ $tipo->tip_restricao }}
                                                </span>
                                                <span>{{ $tipo->des_restricao }}</span>
                                            </label>
                                        </div>
                                        <input type="text" name="complementos[{{ $tipo->idt_restricao }}]"
                                            value="{{ $complemento }}"
                                            placeholder="Complemento (detalhes adicionais)" maxlength="255"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-zinc-800" />
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Botões -->
                <div class="flex gap-3 justify-end">
                    <button type="submit" x-bind:disabled="bloqueado"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
        <script>
            // Validação de data de nascimento
            document.getElementById('dat_nascimento').addEventListener('change', function() {
                const dataNascimento = new Date(this.value);
                const hoje = new Date();
                const idade = hoje.getFullYear() - dataNascimento.getFullYear();

                if (idade < 0 || idade > 120) {
                    alert('Por favor, verifique a data de nascimento informada.');
                    this.value = '';
                }
            });

            // Formatação do telefone
            document.getElementById('tel_pessoa').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
                    value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
                }
                e.target.value = value;
            });
        </script>
    @endpush
</x-layouts.app>
