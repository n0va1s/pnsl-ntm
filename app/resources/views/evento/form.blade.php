<x-layouts.app :title="'Evento'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Novo Evento</h1>
            <p class="text-gray-700 mt-1">Cadastre um novo evento ou atividade de pós-encontro</p>
        </div>
        <div class="flex justify-end mt-4">
            <a href="{{ route('eventos.index') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Voltar para a lista de eventos">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Eventos
            </a>
        </div>
        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-md shadow p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                <i class="bi bi-plus-circle text-blue-600 text-2xl"></i> Dados do Evento
            </h2>

            <form method="POST" action="{{ $evento->exists ? route('eventos.update', $evento) : route('eventos.store') }}" class="space-y-6">
                @csrf

                @if ($evento->exists)
                @method('PUT')
                @endif
                <div>
                    <label for="idt_movimento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Movimento <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="idt_movimento"
                        name="idt_movimento"
                        class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
               @error('idt_movimento') border-red-500 @enderror">
                        <option value="">Selecione um movimento</option>
                        @foreach ($movimentos as $movimento)
                        <option value="{{ $movimento->idt_movimento }}"
                            {{ (old('idt_movimento', $evento->idt_movimento ?? null) == $movimento->idt_movimento) ? 'selected' : '' }}>
                            {{ $movimento->nom_movimento }} ({{ $movimento->des_sigla }})
                        </option>
                        @endforeach
                    </select>
                    @error('idt_movimento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Escolha o movimento relacionado ao evento.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Descrição do Evento -->
                    <div>
                        <label for="des_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descrição do Evento <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="des_evento"
                            name="des_evento"
                            maxlength="255"
                            value="{{ $evento->des_evento ? $evento->des_evento : old('des_evento') }}"
                            placeholder="Digite a descrição completa do evento"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('des_evento') border-red-500 @enderror" />
                        @error('des_evento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máximo de 255 caracteres</p>
                    </div>

                    <!-- Número do Evento -->
                    <div>
                        <label for="num_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Número do Evento
                        </label>
                        <input
                            type="text"
                            id="num_evento"
                            name="num_evento"
                            maxlength="5"
                            value="{{ $evento->num_evento ? $evento->num_evento : old('num_evento') }}"
                            placeholder="Ex: 001, 002"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('num_evento') border-red-500 @enderror" />
                        @error('num_evento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Código ou número identificador do evento (opcional)</p>
                    </div>

                    <!-- Datas -->

                    <div>
                        <label for="dat_inicio" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Data de Início</label>
                        <input
                            type="date"
                            id="dat_inicio"
                            name="dat_inicio"
                            value="{{ old('dat_inicio', optional($evento->dat_inicio)->format('Y-m-d')) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('dat_inicio') border-red-500 @enderror" />
                        @error('dat_inicio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="dat_termino" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Data de Término</label>
                        <input
                            type="date"
                            id="dat_termino"
                            name="dat_termino"
                            value="{{ old('dat_termino', optional($evento->dat_termino)->format('Y-m-d')) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500
                            @error('dat_termino') border-red-500 @enderror" />
                        @error('dat_termino')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Deve ser igual ou posterior à data de início</p>
                    </div>
                </div>

                <!-- Pós Encontro -->
                <div class="flex items-center space-x-3">
                    <input
                        type="checkbox"
                        id="ind_pos_encontro"
                        name="ind_pos_encontro"
                        value="{{ $evento->ind_pos_encontro ? $evento->ind_pos_encontro : old('ind_pos_encontro') }}"
                        {{ $evento->ind_pos_encontro ? 'checked' : '' }}
                        class="w-5 h-5 text-blue-600 rounded border-gray-300 dark:border-zinc-600 focus:ring-blue-500 focus:ring-2" />
                    <label for="ind_pos_encontro" class="font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                        Pós Encontro
                    </label>
                </div>
                @error('ind_pos_encontro')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Marque se este evento é um pós encontro</p>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        aria-label="Salvar o evento">
                        <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                        Salvar
                    </button>
                    <a href="{{ route('eventos.index') }}"
                        class="inline-flex items-center px-4 bg-gray-300 hover:bg-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none text-gray-800 "
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