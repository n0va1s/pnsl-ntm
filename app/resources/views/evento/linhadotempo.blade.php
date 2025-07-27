<x-layouts.app :title="'Linha do Tempo'">
    <section class="p-6 w-full max-w-[80vw] mx-auto">

        {{-- Flash messages (using your existing x-session-alert component) --}}
        <div>
            <x-session-alert />
        </div>

        {{-- Page Title and Description --}}
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Linha do Tempo</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Sua caminhada na Igreja até a santidade</p>
            @if ($pessoa?->exists)
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mt-1">
                    Pessoa: <b>{{ $pessoa->nom_pessoa }}</b> ({{ $pessoa->nom_apelido }})
                </p>
            @endif
        </div>

        <div class="flex justify-end mt-4">
            <a href="{{ route('eventos.index') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Voltar para a lista de eventos">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Eventos
            </a>
        </div>

        <div class="flex flex-col md:flex-row gap-8 mt-8 w-full items-start">
            {{-- Santímetro Box (Left Side) --}}
            <div
                class="w-full md:w-1/4 flex-shrink-0 p-6 bg-white dark:bg-zinc-800 rounded-xl shadow border border-gray-200 dark:border-zinc-700 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Santímetro</h2>

                <div class="text-blue-600 dark:text-blue-400 text-6xl font-extrabold leading-none">
                    {{ $pontuacaoTotal ?? 0 }}
                </div>
                <p class="text-gray-600 dark:text-gray-300 mt-2 text-sm">total de pontos com a participação em eventos
                </p>

                <div class="mt-6 text-gray-700 dark:text-gray-200 text-lg font-semibold">
                    Posição: <span class="text-green-600 dark:text-green-400">{{ $posicaoNoRanking ?? 'N/A' }}º</span>
                </div>
            </div>

            {{-- Timeline Content Area (Middle/Main Column) --}}
            <div class="relative wrap overflow-hidden p-10 h-full w-full md:w-3/4"> {{-- Agora ocupa 5/6 da largura --}}
                {{-- Vertical Timeline Line - MOVIDO PARA DENTRO DA CONDIÇÃO --}}
                @if (!empty($timeline))
                    <div class="border-2-2 absolute border-opacity-20 border-gray-700 dark:border-gray-300 h-full border"
                        style="left: 50%;"></div>
                @endif

                @forelse ($timeline as $decadeData)
                    {{-- Decade Section --}}
                    <div class="mb-8 flex justify-between items-center w-full right-timeline" x-data="{ openDecade: true }">
                        <div class="order-1 w-5/12"></div> {{-- Placeholder for left side --}}
                        {{-- Decade Circle in the middle --}}
                        <div class="z-20 flex items-center order-1 bg-blue-600 shadow-xl w-15 h-15 rounded-full">
                            <h1 class="mx-auto font-semibold text-lg text-white cursor-pointer"
                                @click="openDecade = !openDecade">{{ $decadeData['decade'] }}</h1>
                        </div>
                        {{-- Decade Card on the right --}}
                        <div class="order-1 bg-gray-100 dark:bg-zinc-700 rounded-lg shadow-xl w-5/12 px-6 py-4">
                            <h3 class="mb-3 font-bold text-gray-800 dark:text-white text-xl cursor-pointer"
                                @click="openDecade = !openDecade">{{ $decadeData['decade'] }}</h3>
                            {{-- Collapsible content for the decade --}}
                            <div x-show="openDecade" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform scale-90"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-90">
                                @foreach ($decadeData['years'] as $yearData)
                                    {{-- Year Section --}}
                                    <div class="mb-4" x-data="{ openYear: true }">
                                        <h4 class="font-semibold text-gray-700 dark:text-gray-200 text-lg cursor-pointer"
                                            @click="openYear = !openYear">{{ $yearData['year'] }}</h4>
                                        {{-- Collapsible content for the year --}}
                                        <div x-show="openYear" x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 transform scale-90"
                                            x-transition:enter-end="opacity-100 transform scale-100"
                                            x-transition:leave="transition ease-in duration-300"
                                            x-transition:leave-start="opacity-100 transform scale-100"
                                            x-transition:leave-end="opacity-0 transform scale-90">
                                            @foreach ($yearData['events'] as $eventEntry)
                                                {{-- Individual Event Card --}}
                                                <div
                                                    class="mt-2 p-3 bg-white dark:bg-zinc-800 rounded-md shadow-sm border border-gray-200 dark:border-zinc-600">
                                                    <p class="font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $eventEntry['event']->des_evento }}</p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                                        {{ $eventEntry['date']->format('d/m/Y') }} -
                                                        <span class="font-semibold">{{ $eventEntry['type'] }}</span>
                                                        @if ($eventEntry['event']->movimento)
                                                            <span
                                                                class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                                        @if ($eventEntry['event']->movimento->des_sigla === 'VEM') bg-purple-200 text-purple-800 dark:bg-purple-700 dark:text-purple-100
                                                        @elseif($eventEntry['event']->movimento->des_sigla === 'ECC') bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                                                        @elseif($eventEntry['event']->movimento->des_sigla === 'Segue-Me') bg-blue-200 text-blue-800 dark:bg-blue-700 dark:text-blue-100
                                                        @else bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-100 @endif">
                                                                {{ $eventEntry['event']->movimento->des_sigla }}
                                                            </span>
                                                        @endif
                                                    </p>
                                                    @if ($eventEntry['type'] === 'Trabalhador')
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            Equipe: {{ $eventEntry['details']['equipe'] }}
                                                            @if ($eventEntry['details']['coordenador'])
                                                                <span
                                                                    class="text-blue-600 font-semibold ml-1">(Coordenador)</span>
                                                            @endif
                                                            @if ($eventEntry['details']['primeira_vez'])
                                                                <span
                                                                    class="text-green-600 font-semibold ml-1">(Primeira
                                                                    Vez)</span>
                                                            @endif
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div
                            class="flex flex-col items-center justify-center text-center p-10 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                            <x-heroicon-o-calendar class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                            <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum evento encontrado</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Quando você participar de eventos, eles aparecerão aqui.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.app>
