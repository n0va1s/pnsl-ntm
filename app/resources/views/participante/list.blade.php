<x-layouts.app :title="'Participante'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Lista de Participantes</h1>
            @if ($evento->exists)
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mt-1">
                    Evento: <b>{{ $evento->des_evento }}</b>
                </p>
            @endif
        </div>
        <div class="flex justify-between items-center mb-4">
            <form method="GET" action="{{ route('participantes.index') }}"
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
                    <a href="{{ route('participantes.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400">
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2" />
                        Limpar
                    </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto mt-4">
            <table
                class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-700">
                    <tr>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Foto</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Nome</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Apelido</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Telefone</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participantes as $participante)
                        <tr class="border-t dark:border-zinc-600 dark:hover:bg-zinc-800">
                            <td class="p-3">
                                @if ($participante->pessoa->foto && $participante->pessoa->foto->url_foto)
                                    <img src="{{ asset('storage/' . $participante->pessoa->foto->url_foto) }}"
                                        alt="Foto de {{ $participante->pessoa->nom_pessoa }}"
                                        class="w-10 h-10 rounded-full object-cover border border-gray-300 dark:border-zinc-600 shadow-sm">
                                @else
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 dark:bg-zinc-700 flex items-center justify-center text-gray-400">
                                        <x-heroicon-o-user class="w-5 h-5" />
                                    </div>
                                @endif
                            </td>

                            <td class="p-3 text-gray-900 dark:text-gray-200">{{ $participante->pessoa->nom_pessoa }}
                            </td>
                            <td class="p-3 text-gray-900 dark:text-gray-200">{{ $participante->pessoa->nom_apelido }}
                            </td>
                            <td class="p-3 text-gray-700 dark:text-gray-300">{{ $participante->pessoa->tel_pessoa }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Nenhuma pessoa encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $participantes->links() }}
        </div>
    </section>
</x-layouts.app>
