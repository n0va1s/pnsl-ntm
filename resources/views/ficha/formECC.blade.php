<x-layouts.app :title="'Ficha do ECC'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Ficha do ECC</h1>
            <p class="text-gray-700 mt-1">Cadastre uma nova ficha para um dos nossos eventos próximos</p>
        </div>
        <div class="flex justify-end mt-4">
            <a href="{{ route('ecc.index') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Voltar para a lista de fichas">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Fichas
            </a>
        </div>
        @if ($eventos->count() > 0)
            <form method="POST" x-data="{ bloqueado: {{ $ficha->ind_aprovado ? 'true' : 'false' }} }"
                action="{{ $ficha->exists ? route('ecc.update', $ficha) : route('ecc.store') }}" class="space-y-8">
                @csrf
                @if ($ficha->exists)
                    @method('PUT')
                @endif

                <!-- Dados Básicos -->
                <div class="bg-white dark:bg-zinc-800 rounded-md shadow p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Dados Básicos</h2>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Movimento -->
                            <div>
                                <label for="idt_movimento"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Movimento
                                </label>
                                <select id="idt_movimento" name="idt_movimento" disabled
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('idt_movimento') border-red-500 @enderror">
                                    <option value="">Selecione um movimento</option>
                                    @foreach ($movimentos as $movimento)
                                        <option value="{{ $movimento->idt_movimento }}"
                                            {{ old('idt_movimento', $movimentopadrao) == $movimento->idt_movimento ? 'selected' : '' }}>
                                            {{ $movimento->nom_movimento }} ({{ $movimento->des_sigla }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('idt_movimento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Escolha o movimento para
                                    filtrar
                                    mais as opções de evento.</p>
                            </div>

                            <!-- Evento -->
                            <div>
                                <label for="idt_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Evento <span class="text-red-600">*</span>
                                </label>
                                <select name="idt_evento" id="idt_evento" required x-bind:disabled="bloqueado"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('idt_evento') border-red-500 @enderror">
                                    <option value="">Selecione um evento</option>
                                    @foreach ($eventos as $evento)
                                        <option value="{{ $evento->idt_evento }}"
                                            {{ old('idt_evento', $ficha->idt_evento) == $evento->idt_evento ? 'selected' : '' }}>
                                            {{ $evento->des_evento }} - {{ $evento->dat_inicio->format('d/m/Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('idt_evento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Gênero -->
                            <div>
                                <label for="tip_genero" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Gênero <span class="text-red-600">*</span>
                                </label>
                                <select name="tip_genero" id="tip_genero" required x-bind:disabled="bloqueado"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('tip_genero') border-red-500 @enderror">
                                    <option value="">Selecione o gênero</option>
                                    <option value="M"
                                        {{ old('tip_genero', $ficha->tip_genero) == 'M' ? 'selected' : '' }}>Masculino
                                    </option>
                                    <option value="F"
                                        {{ old('tip_genero', $ficha->tip_genero) == 'F' ? 'selected' : '' }}>Feminino
                                    </option>
                                    <option value="O"
                                        {{ old('tip_genero', $ficha->tip_genero) == 'O' ? 'selected' : '' }}>Outro
                                    </option>
                                </select>
                                @error('tip_genero')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nome completo -->
                            <div>
                                <label for="nom_candidato"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nome completo <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="nom_candidato" id="nom_candidato"
                                    x-bind:disabled="bloqueado"
                                    value="{{ old('nom_candidato', $ficha->nom_candidato) }}" required maxlength="255"
                                    placeholder="Digite o nome completo"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('nom_candidato') border-red-500 @enderror" />
                                @error('nom_candidato')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Apelido -->
                            <div>
                                <label for="nom_apelido"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Apelido <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="nom_apelido" id="nom_apelido" x-bind:disabled="bloqueado"
                                    value="{{ old('nom_apelido', $ficha->nom_apelido) }}" required maxlength="100"
                                    placeholder="Como gosta de ser chamado"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('nom_apelido') border-red-500 @enderror" />
                                @error('nom_apelido')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data de Nascimento -->
                            <div>
                                <label for="dat_nascimento"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Data de Nascimento <span class="text-red-600">*</span>
                                </label>
                                <input type="date" name="dat_nascimento" id="dat_nascimento"
                                    x-bind:disabled="bloqueado"
                                    value="{{ old('dat_nascimento', $ficha->getDataNascimentoFormatada()) }}" required
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('dat_nascimento') border-red-500 @enderror" />
                                @error('dat_nascimento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label for="tel_candidato"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Telefone
                                </label>
                                <input type="text" name="tel_candidato" id="tel_candidato"
                                    x-bind:disabled="bloqueado"
                                    value="{{ old('tel_candidato', $ficha->tel_candidato) }}" maxlength="20"
                                    placeholder="(00) 00000-0000"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('tel_candidato') border-red-500 @enderror" />
                                @error('tel_candidato')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="eml_candidato"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Email <span class="text-red-600">*</span>
                                </label>
                                <input type="email" name="eml_candidato" id="eml_candidato"
                                    x-bind:disabled="bloqueado"
                                    value="{{ old('eml_candidato', $ficha->eml_candidato) }}" required maxlength="255"
                                    placeholder="exemplo@email.com"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('eml_candidato') border-red-500 @enderror" />
                                @error('eml_candidato')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Endereço -->
                            <div>
                                <label for="des_endereco"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Endereço
                                </label>
                                <input type="text" name="des_endereco" id="des_endereco"
                                    x-bind:disabled="bloqueado"
                                    value="{{ old('des_endereco', $ficha->des_endereco) }}" maxlength="500"
                                    placeholder="Rua, número, bairro, cidade"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('des_endereco') border-red-500 @enderror" />
                                @error('des_endereco')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tamanho da Camiseta -->
                            <div>
                                <label for="tam_camiseta"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tamanho da Camiseta <span class="text-red-600">*</span>
                                </label>
                                <select name="tam_camiseta" id="tam_camiseta" required x-bind:disabled="bloqueado"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('tam_camiseta') border-red-500 @enderror">
                                    <option value="">Selecione o tamanho</option>
                                    @foreach (['PP', 'P', 'M', 'G', 'GG', 'EG'] as $tamanho)
                                        <option value="{{ $tamanho }}"
                                            {{ old('tam_camiseta', $ficha->tam_camiseta) == $tamanho ? 'selected' : '' }}>
                                            {{ $tamanho }}</option>
                                    @endforeach
                                </select>
                                @error('tam_camiseta')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Como soube do evento -->
                            <div>
                                <label for="tip_como_soube"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Como soube do evento
                                </label>
                                <select name="tip_como_soube" id="tip_como_soube" x-bind:disabled="bloqueado"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('tip_como_soube') border-red-500 @enderror">
                                    <option value="">Selecione uma opção</option>
                                    <option value="IND"
                                        {{ old('tip_como_soube', $ficha->tip_como_soube) == 'IND' ? 'selected' : '' }}>
                                        Indicação</option>
                                    <option value="PAD"
                                        {{ old('tip_como_soube', $ficha->tip_como_soube) == 'PAD' ? 'selected' : '' }}>
                                        Padre</option>
                                    <option value="OUT"
                                        {{ old('tip_como_soube', $ficha->tip_como_soube) == 'OUT' ? 'selected' : '' }}>
                                        Outro</option>
                                </select>
                                @error('tip_como_soube')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="eml_candidato"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Confirmações
                                </label>
                                <label class="flex items-center space-x-2">
                                    <!-- hidden para sempre enviar o ind_consentimento para a controller
                             se estiver desmarcado o checkbox nao envia nada, entao o hidden passa 0
                             caso seja marcado o checkbox sera enviado no request -->
                                    <input type="hidden" name="ind_consentimento" value="0">
                                    <input type="checkbox" name="ind_catolico" value="1"
                                        x-bind:disabled="bloqueado"
                                        {{ old('ind_catolico', $ficha->ind_catolico) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-gray-800 dark:text-gray-100">O candidato é católico?</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="hidden" name="ind_toca_instrumento" value="0">
                                    <input type="checkbox" name="ind_toca_instrumento" value="1"
                                        x-bind:disabled="bloqueado"
                                        {{ old('ind_toca_instrumento', $ficha->ind_toca_instrumento) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-gray-800 dark:text-gray-100">Toca algum instrumento?</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Dados do ECC -->
                <div class="bg-white dark:bg-zinc-800 rounded-md shadow p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Cônjuge</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome do Cônjuge -->
                        <div>
                            <label for="nom_conjuge" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nome do Cônjuge
                            </label>
                            <input type="text" name="nom_conjuge" id="nom_conjuge" x-bind:disabled="bloqueado"
                                value="{{ old('nom_conjuge', optional($ficha->fichaEcc)->nom_conjuge) }}"
                                maxlength="150" placeholder="Nome completo do cônjuge"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                @error('nom_conjuge') border-red-500 @enderror" />
                            @error('nom_conjuge')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Apelido do Cônjuge -->
                        <div>
                            <label for="nom_apelido_conjuge"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Apelido do Cônjuge (opcional)
                            </label>
                            <input type="text" name="nom_apelido_conjuge" id="nom_apelido_conjuge"
                                x-bind:disabled="bloqueado"
                                value="{{ old('nom_apelido_conjuge', optional($ficha->fichaEcc)->nom_apelido_conjuge) }}"
                                maxlength="50"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <!-- Telefone do Cônjuge -->
                        <div>
                            <label for="tel_conjuge" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Telefone do Cônjuge
                            </label>
                            <input type="text" name="tel_conjuge" id="tel_conjuge" x-bind:disabled="bloqueado"
                                value="{{ old('tel_conjuge', optional($ficha->fichaEcc)->tel_conjuge) }}"
                                maxlength="15" placeholder="(00) 00000-0000"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                @error('tel_conjuge') border-red-500 @enderror" />
                            @error('tel_conjuge')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data de Nascimento do Cônjuge -->
                        <div>
                            <label for="dat_nascimento_conjuge"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Data de Nascimento do Cônjuge
                            </label>
                            <input type="date" name="dat_nascimento_conjuge" id="dat_nascimento_conjuge"
                                x-bind:disabled="bloqueado"
                                value="{{ old('dat_nascimento_conjuge', optional($ficha->fichaEcc)->dat_nascimento_conjuge?->format('Y-m-d')) }}"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                @error('dat_nascimento_conjuge') border-red-500 @enderror" />
                            @error('dat_nascimento_conjuge')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tamanho da Camiseta do Cônjuge -->
                        <div>
                            <label for="tam_camiseta_conjuge"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tamanho da Camiseta do Cônjuge
                            </label>
                            <select name="tam_camiseta_conjuge" id="tam_camiseta_conjuge"
                                x-bind:disabled="bloqueado"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach (['PP', 'P', 'M', 'G', 'GG', 'XG'] as $tamanho)
                                    <option value="{{ $tamanho }}"
                                        {{ old('tam_camiseta_conjuge', optional($ficha->fichaEcc)->tam_camiseta_conjuge) == $tamanho ? 'selected' : '' }}>
                                        {{ $tamanho }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tam_camiseta_conjuge')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Saúde e Restrições -->
                <div x-data="{ mostrarRestricoes: {{ old('ind_restricao', $ficha->ind_restricao ?? false) ? 'true' : 'false' }} }">
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="ind_restricao" value="0">
                        <input type="checkbox" name="ind_restricao" value="1" x-model="mostrarRestricoes"
                            x-bind:disabled="bloqueado"
                            {{ old('ind_restricao', $ficha->ind_restricao) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-800 dark:text-gray-100">Possui restrição alimentar?</span>
                    </label>

                    <div x-show="mostrarRestricoes" x-transition
                        class="mt-4 bg-gray-50 dark:bg-zinc-700 rounded-md p-4">
                        <h3 class="text-lg font-medium mb-3 text-gray-900 dark:text-gray-100">Restrições
                            Alimentares</h3>
                        <div class="space-y-4">
                            @php
                                $restricoesSelecionadas = $ficha->fichaSaude->pluck('idt_restricao')->toArray();
                                $complementos = $ficha->fichaSaude
                                    ->pluck('txt_complemento', 'idt_restricao')
                                    ->toArray();
                            @endphp

                            @foreach ($restricoes as $restricao)
                                @php
                                    $checked = in_array($restricao->idt_restricao, $restricoesSelecionadas);
                                    $complemento = old(
                                        "complementos.{$restricao->idt_restricao}",
                                        $complementos[$restricao->idt_restricao] ?? '',
                                    );
                                @endphp

                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" name="restricoes[{{ $restricao->idt_restricao }}]"
                                            id="restricao_{{ $restricao->idt_restricao }}" value="1"
                                            {{ $checked ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />
                                        <label for="restricao_{{ $restricao->idt_restricao }}"
                                            class="text-gray-800 dark:text-gray-100 flex items-center space-x-2">
                                            <span
                                                class="text-sm font-semibold px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-zinc-600 dark:text-gray-300">
                                                {{ $restricao->tip_restricao }}
                                            </span>
                                            <span>{{ $restricao->des_restricao }}</span>
                                        </label>
                                    </div>
                                    <input type="text" name="complementos[{{ $restricao->idt_restricao }}]"
                                        value="{{ $complemento }}" placeholder="Complemento ou detalhes adicionais"
                                        maxlength="255"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-zinc-800" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Consentimentos -->
                <div class="bg-white dark:bg-zinc-800 rounded-md shadow p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Consentimento</h2>
                    <div class="space-y-3">
                        <label class="flex items-center space-x-2">
                            <input type="hidden" name="ind_consentimento" value="0">
                            <input type="checkbox" name="ind_consentimento" value="1" required
                                x-bind:disabled="bloqueado"
                                {{ old('ind_consentimento', $ficha->ind_consentimento) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500
                        @error('ind_consentimento') border-red-500 @enderror">
                            <span class="text-gray-800 dark:text-gray-100">
                                Concorda com os termos do encontro? <span class="text-red-600">*</span>
                            </span>
                        </label>
                        @error('ind_consentimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6">
                        <label for="txt_observacao" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Observações
                        </label>
                        <textarea name="txt_observacao" id="txt_observacao" rows="4" maxlength="1000" x-bind:disabled="bloqueado"
                            placeholder="Inclua observações como remédios contínuos ou outros pontos de atenção"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                    @error('txt_observacao') border-red-500 @enderror">{{ old('txt_observacao', $ficha->txt_observacao) }}</textarea>
                        @error('txt_observacao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máximo de 1000 caracteres</p>
                    </div>
                </div>
                @if (Auth::user() && Auth::user()->isAdmin())
                    <div class="bg-white dark:bg-zinc-800 rounded-md shadow p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Análise</h2>
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Select da Situação -->
                                <div>
                                    <label for="idt_situacao"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Situação
                                    </label>
                                    <select name="idt_situacao" id="idt_situacao" required
                                        x-bind:disabled="bloqueado"
                                        class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                                <option value="">Selecione
                                        a situação</option>
                                        @foreach ($situacoes as $situacao)
                                            <option value="{{ $situacao->idt_situacao }}"
                                                {{ old('idt_situacao', $ultimaSituacao->idt_situacao ?? null) == $situacao->idt_situacao ? 'selected' : '' }}>
                                                {{ $situacao->des_situacao }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Textarea da análise -->
                                <div>
                                    <label for="txt_analise"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Análise
                                    </label>
                                    <textarea name="txt_analise" id="txt_analise" rows="3" x-bind:disabled="bloqueado"
                                        class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:outline-none"
                                        placeholder="Descreva a análise realizada">{{ old('txt_analise', $ultimaAnalise->txt_analise ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ações -->
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
                    @if ($ficha->exists)
                        <a href="{{ route('ecc.approve', $ficha->idt_ficha) }}"
                            class="inline-flex items-center px-4 py-2 bg-{{ $ficha->ind_aprovado ? 'red-500 hover:bg-red-600' : 'green-500 hover:bg-green-600' }} text-white font-medium rounded-md shadow-sm transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                @if ($ficha->ind_aprovado)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                @endif
                            </svg>

                            {{ $ficha->ind_aprovado ? 'Desfazer aprovação' : 'Aprovar' }}
                        </a>
                    @endif
                </div>
            </form>
        @else
            <div class="col-span-full">
                <div
                    class="flex flex-col items-center justify-center text-center p-10 mt-4 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                    <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                    <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum evento disponível no momento
                        encontrado</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Em breve abriremos novas inscrições
                    </p>
                </div>
            </div>
        @endif
    </section>
</x-layouts.app>
