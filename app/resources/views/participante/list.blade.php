<x-layouts.app :title="'Participante'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
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

            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Lista de Participantes</h1>
            @if ($evento?->exists)
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

            <div class="flex justify-end mt-4">
                <a href="{{ route('eventos.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                    aria-label="Voltar para a lista de eventos">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                    Eventos
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('participantes.change') }}">
            @csrf

            @if ($participantes->isNotEmpty())
                <div class="overflow-x-auto mt-4">
                    <table
                        class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">
                        <thead class="bg-gray-100 dark:bg-zinc-700">
                            <tr>
                                <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Foto</th>
                                <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Nome</th>
                                <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Apelido</th>
                                <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Telefone</th>
                                <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Troca</th>
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
                                    <td class="p-3 text-gray-900 dark:text-gray-200">
                                        {{ $participante->pessoa->nom_pessoa }}
                                    </td>
                                    <td class="p-3 text-gray-900 dark:text-gray-200">
                                        {{ $participante->pessoa->nom_apelido }}
                                    </td>
                                    <td class="p-3 text-gray-700 dark:text-gray-300">
                                        {{ $participante->pessoa->tel_pessoa }}
                                    </td>
                                    <td class="p-3">
                                        <select name="trocas[{{ $participante->idt_participante }}]"
                                            class="w-full px-2 py-1 rounded-md border border-gray-300 dark:border-zinc-600 dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            @foreach (['azul', 'amarela', 'verde', 'vermelha', 'laranja'] as $cor)
                                                <option value="{{ $cor }}" @selected(strtolower($participante->tip_cor_troca) === $cor)>
                                                    {{ ucfirst($cor) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div
                                            class="flex flex-col items-center justify-center text-center p-10 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600 mt-6">
                                            <x-heroicon-o-user-group
                                                class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                                            <p class="text-lg font-semibold text-gray-600 dark:text-gray-300">Nenhum
                                                participante encontrado</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                Cadastre novos participantes para que apareçam aqui.
                                            </p>
                                            <a href="{{ route('eventos.index') }}"
                                                class="mt-4 inline-flex items-center px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                                Ir para eventos
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3 justify-end mt-4">
                    <button type="submit" x-bind:disabled="bloqueado"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Salvar
                    </button>
                </div>

                <div class="mt-6">
                    {{ $participantes->links() }}
                </div>
            @else
                <div class="col-span-full">
                    <div
                        class="flex flex-col items-center justify-center text-center p-10 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                        <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum(a) participante
                            encontrado(a)</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Quando houver participantes cadastrados, eles aparecerão aqui.
                        </p>
                    </div>
                </div>
            @endif
        </form>
    </section>
</x-layouts.app>
