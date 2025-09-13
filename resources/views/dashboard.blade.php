<x-layouts.app :title="__('Dashboard')">
    <div class="flex flex-col gap-4 w-full max-w-7xl mx-auto px-4 py-6">

        {{-- Linha 1 - 4 colunas --}}
        <div class="grid gap-4 md:grid-cols-4">
            <div
                class="flex items-center justify-center rounded-xl bg-white p-4 shadow-sm border border-neutral-200 dark:border-neutral-700 dark:bg-zinc-600">
                <div class="text-center space-y-3">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">Total de Eventos</h3>
                        <x-heroicon-o-calendar class="w-5 h-5 text-blue-600" />
                    </div>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $qtdEventosAtivos }}</p>
                    <div class="flex items-center justify-center text-sm text-green-600 dark:text-green-400 gap-1">
                        <span class="text-gray-500 dark:text-gray-400">eventos próximos</span>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-center rounded-xl bg-white p-4 shadow-sm border border-neutral-200 dark:border-neutral-700 dark:bg-zinc-600">
                <div class="text-center space-y-3">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">Total de Fichas</h3>
                        <x-heroicon-o-clipboard-document class="w-5 h-5 text-yellow-500" />
                    </div>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $qtdFichasCadastradas }}</p>
                    <div class="flex items-center justify-center text-sm text-green-600 dark:text-green-400 gap-1">
                        <span class="text-gray-500 dark:text-gray-400">fichas cadastradas</span>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-center rounded-xl bg-white p-4 shadow-sm border border-neutral-200 dark:border-neutral-700 dark:bg-zinc-600">
                <div class="text-center space-y-3">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">Total de Participantes</h3>
                        <x-heroicon-o-users class="w-5 h-5 text-green-600" />
                    </div>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $qtdParticipantesCadastrados }}</p>
                    <div class="flex items-center justify-center text-sm text-green-600 dark:text-green-400 gap-1">
                        <span class="text-gray-500 dark:text-gray-400">participantes cadastrados</span>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-center rounded-xl bg-white p-4 shadow-sm border border-neutral-200 dark:border-neutral-700 dark:bg-zinc-600">
                <div class="text-center space-y-3">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">Total de Trabalhadores</h3>
                        <x-heroicon-o-briefcase class="w-5 h-5 text-purple-600" />
                    </div>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $qtdTrabalhadoresCadastrados }}</p>
                    <div class="flex items-center justify-center text-sm text-green-600 dark:text-green-400 gap-1">
                        <span class="text-gray-500 dark:text-gray-400">trabalhadores cadastrados</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Linha 2 - 2 colunas --}}
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-white p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 dark:bg-zinc-600">
                {{-- Cabeçalho --}}
                <header class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-calendar class="w-6 h-6 text-blue-600" />
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Próximos Eventos</h2>
                    </div>
                </header>

                {{-- Lista de eventos --}}
                <ul class="space-y-4">
                    @forelse ($proximoseventos as $evento)
                        @php
                            $sigla = $evento->movimento?->des_sigla;
                            $badgeClasses = match ($sigla) {
                                'VEM' => 'bg-blue-100 text-blue-700',
                                'Segue-Me' => 'bg-orange-100 text-orange-700',
                                'ECC' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-600 ',
                            };
                        @endphp
                        <li
                            class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                            <x-heroicon-o-calendar class="w-6 h-6 text-blue-500 mt-1" />
                            <div class="flex-1 flex flex-col w-full">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-base font-medium text-gray-900 dark:text-white">
                                        {{ $evento->des_evento }}
                                    </h3>

                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClasses }}">
                                        {{ $sigla }}
                                    </span>
                                </div>

                                {{-- Informações adicionais --}}
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($evento->dat_inicio)->translatedFormat('d/m/Y') }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-600 dark:text-gray-300">Nenhum evento encontrado.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 dark:bg-zinc-600">
                {{-- Cabeçalho --}}
                <header class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-newspaper class="w-6 h-6 text-blue-600" />
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Fichas Recentes</h2>
                    </div>
                </header>

                {{-- Lista de eventos --}}
                <ul class="space-y-4">
                    @forelse ($fichasrecentes as $ficha)
                        @php
                            $sigla = $ficha->evento->movimento?->des_sigla;
                            $badgeClasses = match ($sigla) {
                                'VEM' => 'bg-blue-100 text-blue-700',
                                'Segue-Me' => 'bg-orange-100 text-orange-700',
                                'ECC' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <li
                            class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">

                            {{-- Ícone de novidade --}}
                            <x-heroicon-o-sparkles class="w-6 h-6 text-blue-500 mt-1" />

                            <div class="flex-1 flex flex-col w-full">
                                {{-- Cabeçalho com nome e badge --}}
                                <div class="flex justify-between items-start">
                                    <h3 class="text-base font-medium text-gray-900 dark:text-white">
                                        {{ $ficha->nom_candidato }}
                                    </h3>

                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClasses }}">
                                        {{ $sigla }}
                                    </span>
                                </div>

                                {{-- Informações adicionais --}}
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($ficha->dat_nascimento)->translatedFormat('d/m/Y') }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-600 dark:text-gray-300">Nenhuma ficha encontrada.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-layouts.app>
