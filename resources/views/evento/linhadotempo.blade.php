<x-layouts.app :title="'Linha do Tempo'">
    {{-- Container com padding responsivo: p-4 em mobile, p-6 em desktop --}}
    <section class="p-4 md:p-6 w-full max-w-7xl mx-auto">
        <x-session-alert />

        {{-- Cabeçalho Responsivo --}}
        <div class="mb-6 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100">Minha Caminhada</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sua trajetória rumo à santidade
                        <b>{{ $pessoa->nom_pessoa }}</b>
                    </p>
                </div>

                <a href="{{ route('eventos.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-white rounded-lg hover:bg-gray-200 transition text-sm font-semibold border border-gray-200 dark:border-zinc-700">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    Voltar
                </a>
            </div>
        </div>

        {{-- Grid Principal: 1 coluna no mobile, Layout original no desktop --}}
        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Santímetro --}}
            <div class="w-full lg:w-1/4 lg:sticky lg:top-6 h-fit">
                <div
                    class="p-6 bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700 text-center">
                    <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Santímetro</h2>
                    <div class="text-5xl font-black text-blue-600 dark:text-blue-400 mb-1">
                        {{ $pessoa->qtd_pontos_total ?? 0 }}
                    </div>
                    <p class="text-[10px] text-gray-500 uppercase font-bold">Pontos Acumulados</p>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-700">
                        <span class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            {{ $posicaoNoRanking ?? 'N/A' }}º <small class="font-normal text-gray-500">no
                                ranking</small>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="w-full lg:w-3/4 space-y-8 relative">
                {{-- Linha da Timeline: Ajustada para a esquerda no mobile para ganhar espaço --}}
                <div class="absolute left-4 md:left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-zinc-800"></div>

                @forelse ($timeline as $yearData)
                    <div class="relative pl-10 md:pl-20">
                        {{-- Marcador de Ano --}}
                        <div
                            class="absolute left-1.5 md:left-5 z-10 w-6 h-6 md:w-8 md:h-8 bg-blue-600 rounded-full border-4 border-gray-50 dark:border-zinc-900 flex items-center justify-center text-white text-[10px] md:text-xs font-bold shadow-sm">
                            {{ substr($yearData['year'], 2) }}
                        </div>

                        <div class="mb-4">
                            <h3 class="text-lg md:text-xl font-black text-gray-800 dark:text-white mb-4">
                                {{ $yearData['year'] }}</h3>

                            <div class="space-y-4">
                                @foreach ($yearData['events'] as $eventEntry)
                                    <div
                                        class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-700">
                                        <div class="flex items-start gap-3">
                                            {{-- Ícone fixo para não quebrar layout --}}
                                            <div class="hidden sm:block p-2 bg-gray-50 dark:bg-zinc-900 rounded-lg">
                                                <x-heroicon-o-calendar class="w-5 h-5 text-gray-400" />
                                            </div>

                                            <div class="flex-grow">
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                    <h4 class="font-bold text-gray-900 dark:text-white leading-tight">
                                                        {{ $eventEntry['event']->des_evento }}
                                                    </h4>
                                                    {{-- Badge de Movimento --}}
                                                    @if ($eventEntry['event']->movimento)
                                                        <div class="w-fit">
                                                            <x-badge-movimento :sigla="$eventEntry['event']->movimento->des_sigla" />
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2">
                                                    <div
                                                        class="flex items-center text-[11px] font-bold text-gray-500 uppercase">
                                                        <x-heroicon-o-clock class="w-3 h-3 mr-1 sm:hidden" />
                                                        {{ \Carbon\Carbon::parse($eventEntry['date'])->format('d M') }}
                                                    </div>
                                                    <span class="hidden sm:inline text-gray-300">•</span>
                                                    <span
                                                        class="text-[10px] font-black px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400 uppercase tracking-tighter">
                                                        {{ $eventEntry['type'] }}
                                                    </span>
                                                </div>

                                                @if ($eventEntry['type'] === 'Trabalhador')
                                                    <div
                                                        class="mt-3 pt-3 border-t border-gray-50 dark:border-zinc-700/50 flex flex-col sm:flex-row sm:justify-between gap-2">
                                                        <div
                                                            class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                                            <span class="font-medium mr-1">Equipe:</span>
                                                            <span class="font-bold text-gray-800 dark:text-gray-200">
                                                                {{ $eventEntry['details']['equipe'] }}
                                                            </span>
                                                        </div>
                                                        @if ($eventEntry['details']['coordenador'] ?? false)
                                                            <span
                                                                class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase italic">
                                                                ⭐ Coordenador
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <x-sem-registro icon="academic-cap" title="Nenhuma atividade"
                        description="Sua caminhada começa aqui. Inscreva-se em um evento!" />
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.app>
