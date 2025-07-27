<x-layouts.app :title="'Tipo de Movimento'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-200">Novo Movimento</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Cadastre um novo movimento</p>
        </div>

        <div class="flex justify-end mt-4">
            <a href="{{ route('movimento.index') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Voltar para a lista de movimentos">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Voltar
            </a>
        </div>

        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-md shadow p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                <i class="bi bi-plus-circle text-blue-600 text-2xl"></i> Configuração
            </h2>

            <form method="POST"
                action="{{ $tipo->exists ? route('movimento.update', $tipo) : route('movimento.store') }}"
                class="space-y-6">
                @csrf

                @if ($tipo->exists)
                    @method('PUT')
                @endif

                <div>
                    <label for="nom_movimento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nome <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="nom_movimento" name="nom_movimento" maxlength="255"
                        value="{{ $tipo->nom_movimento ? $tipo->nom_movimento : old('nom_movimento') }}"
                        placeholder="Digite o nome completo do movimento"
                        class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nom_movimento') border-red-500 @enderror" />
                    @error('nom_movimento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máximo de 255 caracteres</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="des_sigla" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sigla
                        </label>
                        <input type="text" id="des_sigla" name="des_sigla" maxlength="5"
                            value="{{ $tipo->des_sigla ? $tipo->des_sigla : old('des_sigla') }}"
                            placeholder="Ex: VEM, Segue-Me"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('des_sigla') border-red-500 @enderror" />
                        @error('des_sigla')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sigla que identifica o movimento</p>
                    </div>

                    <div>
                        <label for="dat_inicio" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Data de
                            Início</label>
                        <input type="date" id="dat_inicio" name="dat_inicio"
                            value="{{ old('dat_inicio', optional($tipo->dat_inicio)->format('Y-m-d')) }}"
                            class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dat_inicio') border-red-500 @enderror" />
                        @error('dat_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        aria-label="Salvar a configuração">
                        <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                        Salvar
                    </button>

                    <a href="{{ route('movimento.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 focus:ring-2 rounded-md focus:ring-gray-500 focus:outline-none text-gray-800"
                        aria-label="Cancelar a operação">
                        <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
