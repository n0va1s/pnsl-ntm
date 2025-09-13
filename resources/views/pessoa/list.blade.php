<x-layouts.app :title="'Pessoa'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div>
            <x-session-alert />
        </div>

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Lista de Pessoas</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Visualize e gerencie os dados básicos dos participantes ou
                trabalhadores.</p>
        </div>
        <div class="flex justify-between items-center mb-4">
            <form method="GET" action="{{ route('pessoas.index') }}" class="flex items-center gap-2 w-full max-w-md">
                <input type="text" name="search" id="search" value="{{ $search }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Buscar por nome ou apelido" />
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                    Buscar
                </button>
                @if ($search)
                    <a href="{{ route('pessoas.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400">
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2" />
                        Limpar
                    </a>
                @endif
            </form>
            <div class="flex justify-end mb-4">
                <a href="{{ route('pessoas.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none">
                    <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                    Nova Pessoa
                </a>
            </div>
        </div>
        <div class="overflow-x-auto mt-4">
            @if ($pessoas->isNotEmpty())
                <table
                    class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">
                    <thead class="bg-gray-100 dark:bg-zinc-700">
                        <tr>
                            <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Foto</th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Nome</th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Apelido</th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Telefone</th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Casal</th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-gray-100 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pessoas as $pessoa)
                            <tr class="border-t dark:border-zinc-600 dark:hover:bg-zinc-800">
                                <td class="p-3">
                                    @if ($pessoa->foto && $pessoa->foto->med_foto)
                                        <img src="{{ asset('storage/' . $pessoa->foto->med_foto) }}"
                                            alt="Foto de {{ $pessoa->nom_pessoa }}"
                                            class="w-10 h-10 rounded-full object-cover border border-gray-300 dark:border-zinc-600 shadow-sm">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-200 dark:bg-zinc-700 flex items-center justify-center text-gray-400">
                                            <x-heroicon-o-user class="w-5 h-5" />
                                        </div>
                                    @endif
                                </td>

                                <td class="p-3 text-gray-900 dark:text-gray-200">{{ $pessoa->nom_pessoa }}</td>
                                <td class="p-3 text-gray-900 dark:text-gray-200">{{ $pessoa->nom_apelido }}</td>
                                <td class="p-3 text-gray-700 dark:text-gray-300">{{ $pessoa->tel_pessoa }}</td>

                                <td class="p-3 text-gray-700 dark:text-gray-300">
                                    {{ $pessoa->parceiro ? 'Sim' : 'Não' }}
                                </td>
                                <td class="p-3 flex items-center gap-2 justify-center">
                                    <a href="{{ route('pessoas.edit', $pessoa) }}"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md">
                                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        <span class="sr-only sm:not-sr-only">Editar</span>
                                    </a>
                                    <form method="POST" action="{{ route('pessoas.destroy', $pessoa) }}"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta pessoa?');">
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
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="col-span-full">
                    <div
                        class="flex flex-col items-center justify-center text-center p-10 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                        <x-heroicon-o-briefcase class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                        <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum(a) pessoa
                            encontrado(a)</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Quando houver pessoas cadastradas, elas aparecerão aqui.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6">
            {{ $pessoas->links() }}
        </div>
    </section>
</x-layouts.app>
