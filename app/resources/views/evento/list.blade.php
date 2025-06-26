<x-layouts.app :title="'Evento'">
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
            <h1 class="text-3xl font-bold text-gray-900">Gerenciar Eventos</h1>
            <p class="text-gray-700 mt-1">Cadastre e gerencie os eventos do sistema.</p>
        </div>

        <div class="flex justify-between items-center mb-4">
            <form method="GET" action="{{ route('eventos.index') }}" class="flex items-center gap-2 w-full max-w-md">
                <label for="search" class="sr-only">Buscar</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Buscar por descrição ou número" />
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    aria-label="Cadastrar um novo evento">
                    <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                    Buscar
                </button>
                @if ($search)
                    <a href="{{ route('eventos.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none"
                        aria-label="Limpar a busca">
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2" />
                        Limpar
                    </a>
                @endif
            </form>


            <a href="{{ route('eventos.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Novo Evento">
                <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                Novo
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table
                class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">

                <caption class="sr-only">Lista de Eventos</caption>
                <thead>
                    <tr class="bg-gray-100 dark:bg-zinc-800 text-gray-800 dark:text-gray-200">

                        <th class="p-3 font-semibold">Descrição</th>
                        <th class="p-3 font-semibold">Número</th>
                        <th class="p-3 font-semibold">Data Início</th>
                        <th class="p-3 font-semibold">Data Término</th>
                        <th class="p-3 font-semibold">Movimento</th>
                        <th class="p-3 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eventos as $evento)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3 text-gray-900">{{ $evento->des_evento }}</td>
                            <td class="p-3 text-gray-700">Nº {{ $evento->num_evento }}</td>
                            <td class="p-3 text-gray-700">{{ $evento->getDataInicioFormatada() }}</td>
                            <td class="p-3 text-gray-700">{{ $evento->getDataTerminoFormatada() }}</td>
                            <td class="p-3">
                                @php
                                    $sig_movimento = $evento->movimento->des_sigla;
                                @endphp

                                @if ($sig_movimento === 'ECC')
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                        {{ $sig_movimento }}
                                    </span>
                                @elseif ($sig_movimento === 'Segue-Me')
                                    <span
                                        class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">
                                        {{ $sig_movimento }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                        {{ $sig_movimento }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 flex items-center gap-2">
                                <a href="{{ route('eventos.edit', $evento) }}"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    <span class="sr-only sm:not-sr-only">Editar</span>
                                </a>

                                <form method="POST" action="{{ route('eventos.destroy', $evento) }}"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este evento?');">
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
            {{ $eventos->links() }}
        </div>
    </section>
</x-layouts.app>
