<x-layouts.app :title="'Evento'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Novo Evento</h1>
            <p class="text-gray-700 mt-1">Cadastre um novo evento ou atividade de pós-encontro</p>
        </div>
        <div class="flex justify-end mt-4">
            <div class="flex justify-end mt-4">
                <x-botao-navegar href="{{ route('eventos.index') }}" aria-label="Voltar para a lista de eventos">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                    Eventos
                </x-botao-navegar>
            </div>
        </div>
        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-md shadow p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                <i class="bi bi-plus-circle text-blue-600 text-2xl"></i> Dados do Evento
            </h2>

            <form method="POST"
                action="{{ $evento->exists ? route('eventos.update', $evento) : route('eventos.store') }}"
                class="space-y-6" enctype="multipart/form-data">
                @csrf

                @if ($evento->exists)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Movimento -->
                    <div>
                        <label for="idt_movimento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Movimento <span class="text-red-600">*</span>
                        </label>
                        <select id="idt_movimento" name="idt_movimento"
                            aria-describedby="idt_movimento_help idt_movimento_error"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('idt_movimento') border-red-500 @enderror">
                            <option value="">Selecione um movimento</option>
                            @foreach ($movimentos as $movimento)
                                <option value="{{ $movimento->idt_movimento }}"
                                    {{ old('idt_movimento', $evento->idt_movimento ?? null) == $movimento->idt_movimento ? 'selected' : '' }}>
                                    {{ $movimento->nom_movimento }} ({{ $movimento->des_sigla }})
                                </option>
                            @endforeach
                        </select>
                        @error('idt_movimento')
                            <p id="idt_movimento_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p id="idt_movimento_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Escolha o movimento relacionado ao evento.
                        </p>
                    </div>

                    <!-- Foto Oficial -->
                    <div class="mb-6">
                        <label for="med_foto" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Foto Oficial
                        </label>

                        {{-- Exibe a foto existente se houver e estiver no modo de edição --}}
                        @if (isset($evento) && $evento->foto)
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . $evento->foto->med_foto) }}" alt="Foto do evento"
                                    class="w-48 h-auto rounded shadow border border-gray-300 dark:border-zinc-600">
                            </div>
                        @endif

                        {{-- Campo de upload --}}
                        <input type="file" id="med_foto" name="med_foto" maxlength="255"
                            aria-describedby="med_foto_help med_foto_error"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
        @error('med_foto') border-red-500 @enderror" />

                        @error('med_foto')
                            <p id="med_foto_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <p id="med_foto_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Tamanho máximo: 2 Mb. Formatos aceitos: JPG, PNG.
                        </p>
                    </div>

                    <!-- Descrição do Evento -->
                    <div>
                        <label for="des_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descrição do Evento <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="des_evento" name="des_evento" maxlength="255"
                            value="{{ old('des_evento', $evento->des_evento ?? '') }}"
                            placeholder="Digite a descrição completa do evento"
                            aria-describedby="des_evento_help des_evento_error"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('des_evento') border-red-500 @enderror" />
                        @error('des_evento')
                            <p id="des_evento_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p id="des_evento_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Máximo de 255 caracteres
                        </p>
                    </div>

                    <!-- Número do Evento -->
                    <div>
                        <label for="num_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Número do Evento
                        </label>
                        <input type="text" id="num_evento" name="num_evento" maxlength="5"
                            value="{{ old('num_evento', $evento->num_evento ?? '') }}" placeholder="Ex: 001, 002"
                            aria-describedby="num_evento_help num_evento_error"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('num_evento') border-red-500 @enderror" />
                        @error('num_evento')
                            <p id="num_evento_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p id="num_evento_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Código ou número identificador do evento (opcional)
                        </p>
                    </div>

                    <!-- Data de Início -->
                    <div>
                        <label for="dat_inicio" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Data de Início
                        </label>
                        <input type="date" id="dat_inicio" name="dat_inicio"
                            value="{{ old('dat_inicio', optional($evento->dat_inicio)->format('Y-m-d')) }}"
                            aria-describedby="dat_inicio_error"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('dat_inicio') border-red-500 @enderror" />
                        @error('dat_inicio')
                            <p id="dat_inicio_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data de Término -->
                    <div>
                        <label for="dat_termino" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Data de Término
                        </label>
                        <input type="date" id="dat_termino" name="dat_termino"
                            value="{{ old('dat_termino', optional($evento->dat_termino)->format('Y-m-d')) }}"
                            aria-describedby="dat_termino_help dat_termino_error"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('dat_termino') border-red-500 @enderror" />
                        @error('dat_termino')
                            <p id="dat_termino_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p id="dat_termino_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Deve ser igual ou posterior à data de início
                        </p>
                    </div>
                    <div class="mt-6">
                        <label for="tip_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo <span class="text-red-600">*</span>
                        </label>
                        <select name="tip_evento" id="tip_evento" required x-bind:disabled="bloqueado"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('tip_evento') border-red-500 @enderror">
                            <option value="">Selecione o tipo</option>
                            <option value="E"
                                {{ old('tip_evento', $evento->tip_evento) == 'E' ? 'selected' : '' }}>Encontro Anual
                            </option>
                            <option value="P"
                                {{ old('tip_evento', $evento->tip_evento) == 'P' ? 'selected' : '' }}>Pós-Encontro
                            </option>
                            <option value="D"
                                {{ old('tip_evento', $evento->tip_evento) == 'D' ? 'selected' : '' }}>Desafio</option>
                        </select>
                        @error('tip_evento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($evento->tip_evento == 'P')
                        <div class="mb-6">
                            <label for="val_entrada" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                R$ Entrada
                            </label>
                            <input type="text" id="val_entrada" name="val_entrada" maxlength="10"
                                value="{{ old('val_entrada', $evento->val_entrada) }}" placeholder="Ex: 30.00"
                                aria-describedby="val_entrada_help val_entrada_error"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
@error('val_entrada') border-red-500 @enderror" />
                            @error('val_entrada')
                                <p id="val_entrada_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p id="val_entrada_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Valor da entrada no evento
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Trabalhador -->
                            <div class="flex flex-col justify-end h-full">
                                <label for="val_trabalhador"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    R$ Trabalhador
                                </label>
                                <input type="text" id="val_trabalhador" name="val_trabalhador" maxlength="10"
                                    value="{{ old('val_trabalhador', $evento->val_trabalhador) }}"
                                    placeholder="Ex: 50.00"
                                    aria-describedby="val_trabalhador_help val_trabalhador_error"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
@error('val_trabalhador') border-red-500 @enderror" />
                                @error('val_trabalhador')
                                    <p id="val_trabalhador_error" class="mt-1 text-sm text-red-600">{{ $message }}
                                    </p>
                                @enderror
                                <p id="val_trabalhador_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Contribuição dos trabalhadores
                                </p>
                            </div>

                            <!-- Venista -->
                            <div class="flex flex-col justify-end h-full">
                                <label for="val_venista"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    R$ Participante
                                </label>
                                <input type="text" id="val_venista" name="val_venista" maxlength="10"
                                    value="{{ old('val_venista', $evento->val_venista) }}" placeholder="Ex: 80.00"
                                    aria-describedby="val_venista_help val_venista_error"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
@error('val_venista') border-red-500 @enderror" />
                                @error('val_venista')
                                    <p id="val_venista_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p id="val_venista_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Contribuição do participante
                                </p>
                            </div>

                            <!-- Camiseta -->
                            <div class="flex flex-col justify-end h-full">
                                <label for="val_camiseta"
                                    class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    R$ Camiseta
                                </label>
                                <input type="text" id="val_camiseta" name="val_camiseta" maxlength="10"
                                    value="{{ old('val_camiseta', $evento->val_camiseta) }}" placeholder="Ex: 30.00"
                                    aria-describedby="val_camiseta_help val_camiseta_error"
                                    class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
@error('val_camiseta') border-red-500 @enderror" />
                                @error('val_camiseta')
                                    <p id="val_camiseta_error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p id="val_camiseta_help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Valor da camiseta (opcional)
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex flex-wrap justify-end gap-4">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        aria-label="Salvar o evento">
                        <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                        Salvar
                    </button>
                    <a href="{{ route('eventos.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md focus:ring-2 focus:ring-gray-500 focus:outline-none"
                        aria-label="Cancelar a operação">
                        <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
        <script>
            // Validação de data no cliente
            document.getElementById('dat_inicio').addEventListener('change', function() {
                const dataInicio = this.value;
                const dataTermino = document.getElementById('dat_termino');

                if (dataInicio && dataTermino.value && dataTermino.value < dataInicio) {
                    dataTermino.value = dataInicio;
                }

                dataTermino.min = dataInicio;
            });
        </script>
    @endpush
</x-layouts.app>
