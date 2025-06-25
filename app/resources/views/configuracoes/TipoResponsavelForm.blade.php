<x-layouts.app>
    <h2 id="titulo-pagina" class="text-2xl font-bold mb-6">Cadastrar Tipo de Responsável</h2>

    <form method="POST"
                action="{{ $responsavel->exists ? route('tiporesponsavel.update', $responsavel) : route('tiporesponsavel.store') }}"
                class="space-y-6">
                @csrf
                @if ($responsavel->exists)
                    @method('PUT')
                @endif

        <div>
            <label for="des_responsavel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Descrição do Responsável <span class="text-red-600">*</span>
            </label>
            <input type="text"
                   id="des_responsavel"
                   name="des_responsavel"
                   required
                   value="{{ old('des_responsavel', $responsavel->des_responsavel) }}"
                   aria-required="true"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400"
                   placeholder="Ex: Pai, Mãe, Padrinho...">
            @error('des_responsavel')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    aria-label="Salvar tipo de responsável">
                    <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                Salvar
            </button>

            <a href="{{ route('tiporesponsavel.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-md focus:ring-2 focus:ring-gray-500 focus:outline-none text-gray-800"
               aria-label="Voltar para a lista de responsáveis">
               <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />
                Cancelar
            </a>
        </div>
    </form>
</x-layouts.app>
