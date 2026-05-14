<x-layouts.app :title="'Minha Equipe'">
    <section class="p-6 w-full max-w-5xl mx-auto">

        <x-session-alert />

        {{-- NAVEGAÇÃO --}}
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item href="/">Início</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Minha Equipe</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        {{-- CABEÇALHO --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Minha Equipe</h1>
            @if ($evento && $equipe)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $evento->des_evento }}
                    &mdash;
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $equipe->des_grupo }}</span>
                </p>
            @endif
        </div>

        @if (! $coordenacao)
            {{-- Estado vazio: usuário não é coordenador em nenhum evento --}}
            <div class="flex flex-col items-center justify-center text-center p-12 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                <x-heroicon-o-user-group class="w-14 h-14 text-gray-400 dark:text-gray-500 mb-4" />
                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Você não é coordenador(a) de nenhuma equipe</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Quando você for confirmado(a) como coordenador(a) de uma equipe, os membros aparecerão aqui.
                </p>
            </div>
        @elseif ($membros->isEmpty())
            {{-- Coordenador confirmado, mas sem outros membros ainda --}}
            <div class="flex flex-col items-center justify-center text-center p-12 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                <x-heroicon-o-user-group class="w-14 h-14 text-gray-400 dark:text-gray-500 mb-4" />
                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum membro na equipe ainda</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Os membros confirmados na equipe <strong>{{ $equipe->des_grupo }}</strong> aparecerão aqui.
                </p>
            </div>
        @else
            {{-- Grade de cards dos membros --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($membros as $membro)
                    @php $pessoa = $membro->pessoa; @endphp
                    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-5 flex flex-col gap-3">

                        {{-- Cabeçalho do card: foto + nome --}}
                        <div class="flex items-center gap-3">
                            @if ($pessoa->foto && $pessoa->foto->url_foto)
                                <img src="{{ asset('storage/' . $pessoa->foto->url_foto) }}"
                                    alt="Foto de {{ $pessoa->nom_pessoa }}"
                                    class="w-12 h-12 rounded-full object-cover border border-gray-300 dark:border-zinc-600 shadow-sm flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-zinc-700 flex items-center justify-center flex-shrink-0">
                                    <x-heroicon-o-user class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                                </div>
                            @endif

                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                    {{ $pessoa->nom_pessoa }}
                                </p>
                                @if ($pessoa->nom_apelido)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $pessoa->nom_apelido }}
                                    </p>
                                @endif
                                @if ($membro->ind_coordenador)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100 mt-0.5">
                                        Coordenador(a)
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Dados de contato e nascimento --}}
                        <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                            @if ($pessoa->tel_pessoa)
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-phone class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                    <span>{{ $pessoa->tel_pessoa }}</span>
                                </div>
                            @endif
                            @if ($pessoa->dat_nascimento)
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-cake class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                    <span>{{ $pessoa->dat_nascimento->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Restrições --}}
                        @if ($pessoa->restricoes->isNotEmpty())
                            <div class="border-t border-gray-100 dark:border-zinc-700 pt-3">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                    Restrições
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($pessoa->restricoes as $restricao)
                                        @php
                                            $cor = match($restricao->tip_restricao) {
                                                'ALE' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                'INT' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                                'MED' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                'CUT' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                'PNE' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                                'VEG' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                'RES' => 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-zinc-700 dark:text-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cor }}"
                                            title="{{ $restricao->pivot->txt_complemento ?? '' }}">
                                            {{ $restricao->des_restricao }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Link para o cadastro completo --}}
                        <div class="mt-auto pt-3 border-t border-gray-100 dark:border-zinc-700">
                            <a href="{{ route('pessoas.show', $pessoa->idt_pessoa) }}"
                                class="inline-flex items-center gap-1.5 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                Ver cadastro completo
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
