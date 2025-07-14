<x-layouts.app :title="'Quadrante'">
    <section class="p-6 w-full max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Quadrante</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Visualizar as pessoas de cada equipe do evento</p>
            @if ($evento?->exists)
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mt-1">
                    Evento: <b>{{ $evento->des_evento }}</b> ({{ $evento->movimento->des_sigla }})
                </p>
            @endif
        </div>

        @forelse ($trabalhadoresPorEquipe as $nomeEquipe => $trabalhadores)
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-blue-700 dark:text-blue-300 border-b pb-1 mb-4">
                    {{ $nomeEquipe }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($trabalhadores as $trabalhador)
                        <div
                            class="flex flex-col justify-between h-full bg-white dark:bg-zinc-800 rounded-xl p-4 border border-gray-200 dark:border-zinc-700 shadow-sm">

                            {{-- Cabeçalho com duas colunas --}}
                            <div class="flex justify-between gap-4">
                                {{-- Coluna da esquerda: nome + apelido + coordenador --}}
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ $trabalhador->pessoa->nom_pessoa }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $trabalhador->pessoa->nom_apelido }}
                                    </p>

                                    @if ($trabalhador->ind_coordenador)
                                        <span
                                            class="inline-block mt-2 px-2 py-0.5 text-sm font-medium bg-purple-100 text-purple-800 rounded dark:bg-purple-800 dark:text-white">
                                            Coordenador
                                        </span>
                                    @endif
                                </div>

                                {{-- Coluna da direita: ícones de avaliação --}}
                                @if ($trabalhador->ind_avaliacao)
                                    <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300 min-w-[120px]">
                                        @if ($trabalhador->ind_lideranca)
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500" />
                                                <span>Liderança</span>
                                            </div>
                                        @endif
                                        @if ($trabalhador->ind_destaque)
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-o-star class="w-5 h-5 text-amber-500" />
                                                <span>Destaque</span>
                                            </div>
                                        @endif
                                        @if ($trabalhador->ind_recomendado)
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-o-hand-thumb-up class="w-5 h-5 text-green-500" />
                                                <span>Recomendado</span>
                                            </div>
                                        @endif
                                        @if ($trabalhador->ind_primeira_vez)
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-o-sparkles class="w-5 h-5 text-indigo-500" />
                                                <span>Primeira Vez</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Botão Avaliar (no rodapé do card) --}}
                            @if (!$trabalhador->ind_avaliacao)
                                <div class="mt-4">
                                    <a href="{{ route('trabalhadores.review', ['evento' => $trabalhador->evento->idt_evento, 'equipe' => $trabalhador->equipe->idt_equipe, 'pessoa' => $trabalhador->pessoa->idt_pessoa]) }}"
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150"
                                        title="Quero trabalhar neste evento">
                                        <x-heroicon-o-heart class="w-5 h-5" />
                                        Avaliar
                                    </a>
                                </div>
                            @endif

                        </div>
                    @endforeach

                </div>
            </div>
        @empty
            <div class="text-center text-gray-600 dark:text-gray-300">
                Nenhum trabalhador encontrado.
            </div>
        @endforelse
    </section>
</x-layouts.app>
