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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Gerenciar Eventos</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Cadastre e gerencie os eventos do sistema.</p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-6">
            @forelse ($eventos as $evento)
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700 p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $evento->des_evento }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nº {{ $evento->num_evento }}</p>
                        </div>
                        <div>
                            @php
                                $sigla = $evento->movimento->des_sigla;
                                $badgeClasses = match ($sigla) {
                                    'ECC' => 'bg-lime-400 text-green-800',
                                    'Segue-Me' => 'bg-orange-300 text-red-800',
                                    default => 'bg-sky-400 text-blue-800',
                                };
                            @endphp
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                {{ $sigla }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3 text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-semibold">Período:</span><br>
                        <time
                            datetime="{{ $evento->dat_inicio->toDateString() }}">{{ $evento->getDataInicioFormatada() }}</time>
                        <span class="text-gray-400"> até </span>
                        <time
                            datetime="{{ $evento->dat_termino->toDateString() }}">{{ $evento->getDataTerminoFormatada() }}</time>
                    </div>

                    @if (
                        !$evento->ind_pos_encontro &&
                            ($evento->fichas_count || $evento->trabalhadores_count || $evento->participantes_count))
                        <div class="flex w-full gap-x-2 mb-4 text-sm text-white">
                            {{-- Fichas --}}
                            <div
                                class="flex items-center justify-center gap-1 w-1/3 py-1 {{ $evento->fichas_count ? 'bg-green-600 rounded-l-md' : 'invisible' }}">
                                @if ($evento->fichas_count)
                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                    {{ $evento->fichas_count }}
                                @endif
                            </div>

                            {{-- Candidatos --}}
                            <div
                                class="flex items-center justify-center gap-1 w-1/3 py-1 {{ $evento->trabalhadores_count ? 'bg-green-600' : 'invisible' }}">
                                @if ($evento->trabalhadores_count)
                                    <x-heroicon-o-hand-thumb-up class="w-4 h-4" />
                                    {{ $evento->trabalhadores_count }}
                                @endif
                            </div>

                            {{-- Participantes --}}
                            <div
                                class="flex items-center justify-center gap-1 w-1/3 py-1 {{ $evento->participantes_count ? 'bg-green-600 rounded-r-md' : 'invisible' }}">
                                @if ($evento->participantes_count)
                                    <x-heroicon-o-users class="w-4 h-4" />
                                    {{ $evento->participantes_count }}
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-0 mt-4 border-t pt-3">
                        <a href="{{ route('eventos.edit', $evento) }}"
                            class="flex items-center justify-center w-1/2 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 px-3 py-2 rounded-bl-lg transition-colors">
                            <x-heroicon-o-pencil-square class="w-5 h-5 mr-1" />
                            Editar
                        </a>

                        <form method="POST" action="{{ route('eventos.destroy', $evento) }}"
                            onsubmit="return confirm('Tem certeza que deseja excluir este evento?');" class="w-1/2">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="flex items-center justify-center w-full text-sm font-semibold text-red-600 hover:text-white hover:bg-red-600 px-3 py-2 rounded-br-lg transition-colors">
                                <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 dark:text-gray-300">
                    Nenhum evento encontrado.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $eventos->links() }}
        </div>
    </section>
</x-layouts.app>
