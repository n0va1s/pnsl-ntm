<x-layouts.app :title="'Ficha do ECC'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        @if (session('success') || session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="mb-4 px-4 py-3 rounded-md text-white font-semibold flex items-center gap-2
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Gerenciar Fichas</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Cadastre e gerencie as fichas do ECC.</p>
        </div>

        <div class="flex justify-between items-center mb-4">
            <form method="GET" action="{{ route('fichas-ecc.index') }}"
                class="flex items-center gap-2 w-full max-w-md">
                <input type="text" name="search" id="search" value="{{ $search }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Buscar por nome ou apelido" />
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                    Buscar
                </button>
                @if ($search)
                    <a href="{{ route('fichas-ecc.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400">
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2" />
                        Limpar
                    </a>
                @endif
            </form>

            <a href="{{ route('fichas-ecc.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                Nova Ficha
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left border border-gray-200 rounded-md overflow-hidden text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 font-semibold dark:text-gray-800">Nome</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Apelido</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Nascimento</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Evento</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Aprovado</th>
                        <th class="p-3 font-semibold text-center dark:text-gray-800 w-24">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fichas as $ficha)
                        <tr class="border-t hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $ficha->nom_candidato }}</td>
                            <td class="p-3 text-gray-700 dark:text-gray-100">{{ $ficha->nom_apelido }}</td>
                            <td class="p-3 text-gray-700 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($ficha->dat_nascimento)->format('d/m/Y') }}</td>

                            <td class="p-3 text-gray-700 dark:text-gray-100">{{ $ficha->evento->des_evento ?? '—' }}</td>
                            <td class="p-3 dark:text-gray-100">
                                @if ($ficha->ind_aprovado)
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Sim</span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Não</span>
                                @endif
                            </td>
                            <td class="p-3 flex justify-end items-center gap-2">
                                <a href="{{ route('fichas-ecc.edit', $ficha) }}"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    <span class="sr-only sm:not-sr-only">Editar</span>
                                </a>
                                <form method="POST" action="{{ route('fichas-ecc.destroy', $ficha) }}"
                                    onsubmit="return confirm('Tem certeza que deseja excluir esta ficha?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 px-2 py-1 rounded-md cursor-pointer">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                        <span class="sr-only sm:not-sr-only">Excluir</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">Nenhuma ficha encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $fichas->links() }}
        </div>
    </section>
</x-layouts.app>
