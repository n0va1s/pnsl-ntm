<x-layouts.app :title="'Tipo de Movimento'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        @if (session('success') || session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            class="fixed top-6 left-1/2 z-50 px-4 py-3 rounded-md text-white font-semibold shadow-lg flex items-center gap-2
        {{ session('success') ? 'bg-green-600' : 'bg-red-600' }}"
            role="alert">
            @if (session('success'))
            <x-heroicon-o-check-circle class="w-6 h-6 text-white" />
            <span>{{ session('success') }}</span>
            @else
            <x-heroicon-o-x-circle class="w-6 h-6 text-white" />
            <span>{{ session('error') }}</span>
            @endif
        </div>
        @endif
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Gerenciar Movimentos</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Cadastre e gerencie os tipos de movimentos do sistema.</p>
        </div>

        <div class="flex justify-end items-center mb-4">
            <a href="{{ route('tiposmovimentos.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Novo Evento">
                <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                Novo
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">

                <caption class="sr-only">Tipos de Movimentos</caption>
                <thead>
                    <tr class="bg-gray-100 dark:bg-zinc-800 text-gray-800 dark:text-gray-200">

                        <th class="p-3 font-semibold">Descrição</th>
                        <th class="p-3 font-semibold">Sigla</th>
                        <th class="p-3 font-semibold">Data de Início</th>
                        <th class="p-3 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tipos as $tipo)
                    <tr class="border-t hover:bg-gray-500">
                        <td class="p-3 text-gray-900 dark:text-gray-300">{{ $tipo->nom_movimento }}</td>
                        <td class="p-3 text-gray-900 dark:text-gray-300">{{ $tipo->des_sigla }}</td>
                        <td class="p-3 text-gray-900 dark:text-gray-300">{{ $tipo->getDataInicioFormatada() }}</td>

                        <td class="p-3 flex items-center gap-2">
                            <a href="{{ route('tiposmovimentos.edit', $tipo) }}"
                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md">
                                <x-heroicon-o-pencil-square class="w-5 h-5" />
                                <span class="sr-only sm:not-sr-only">Editar</span>
                            </a>

                            <form method="POST" action="{{ route('tiposmovimentos.destroy', $tipo) }}"
                                onsubmit="return confirm('Tem certeza que deseja excluir este tipo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 px-2 py-1 rounded-md">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                    <span class="sr-only sm:not-sr-only">Excluir</span>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">Nenhum evento encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $tipos->links() }}
        </div>
    </section>
</x-layouts.app>
