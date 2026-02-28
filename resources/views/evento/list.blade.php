<x-layouts.app title="Gerenciar Eventos">
    <section class="p-6 w-full max-w-7xl mx-auto">
        <x-session-alert />

        {{-- Cabeçalho --}}
        <header class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Gerenciar Eventos</h1>
                <p class="text-gray-600 mt-1 dark:text-gray-400">Visualize e participe dos próximos encontros e desafios.
                </p>
            </div>

            @if (Auth::user()->isAdmin())
                {{-- Botão Novo Evento com seu padrão Azul --}}
                <a href="{{ route('eventos.create') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all shadow-md active:scale-95">
                    <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                    Novo Evento
                </a>
            @endif
        </header>

        {{-- Filtros --}}
        <nav class="bg-white dark:bg-zinc-800 p-5 rounded-xl border border-gray-200 dark:border-zinc-700 shadow-sm mb-8">
            <form method="GET" action="{{ route('eventos.index') }}"
                class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-6">
                    <input type="text" name="search" value="{{ $search }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-zinc-900 dark:border-zinc-600 dark:text-gray-200"
                        placeholder="Buscar por descrição ou número...">
                </div>

                <div class="md:col-span-3">
                    <select name="idt_movimento"
                        class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-4 py-2 dark:bg-zinc-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Todos os Movimentos</option>
                        @foreach ($movimentos as $mov)
                            <option value="{{ $mov->idt_movimento }}"
                                {{ $idt_movimento == $mov->idt_movimento ? 'selected' : '' }}>
                                {{ $mov->des_sigla }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex gap-2">
                    {{-- Botão Filtrar com seu padrão Azul --}}
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none flex justify-center items-center font-bold transition">
                        <x-heroicon-s-magnifying-glass class="w-5 h-5 mr-2" />
                        Filtrar
                    </button>

                    @if ($search || $idt_movimento)
                        <a href="{{ route('eventos.index') }}"
                            class="px-4 py-2 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 flex items-center dark:bg-zinc-700 dark:text-gray-300">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </a>
                    @endif
                </div>
            </form>
        </nav>

        {{-- Grid de Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($eventos as $evento)
                <article
                    class="flex flex-col bg-white dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">

                    <div class="px-5 pt-5 flex justify-between items-start">
                        <span
                            class="px-2 py-1 bg-gray-100 dark:bg-zinc-700 rounded text-[10px] font-black uppercase text-gray-400">
                            Nº {{ $evento->num_evento }}
                        </span>
                        <x-badge-movimento :sigla="$evento->movimento->des_sigla" />
                    </div>

                    <div class="p-5 flex-grow">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-3 line-clamp-2 min-h-[3rem]">
                            {{ $evento->des_evento }}
                        </h2>

                        <div class="space-y-3">
                            <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm">
                                <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-blue-500" />
                                <span>{{ $evento->getDataInicioFormatada() }} a
                                    {{ $evento->getDataTerminoFormatada() }}</span>
                            </div>

                            <div
                                class="flex items-center text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">
                                <x-heroicon-o-tag class="w-4 h-4 mr-2" />
                                {{ $evento->tipo_descricao }}
                            </div>
                        </div>
                    </div>

                    {{-- Admin Stats --}}
                    @if (Auth::user()?->isAdmin())
                        <div class="px-5 pb-4 grid grid-cols-2 gap-2">
                            <x-evento-card label="Inscritos" :count="$evento->participantes_count" :href="route('participantes.index', ['evento' => $evento->idt_evento])"
                                icon="heroicon-o-user-group" color="blue" />
                            <x-evento-card label="Voluntários" :count="$evento->voluntarios_count" :href="route('montagem.list', ['evento' => $evento->idt_evento])"
                                icon="heroicon-o-hand-raised" color="green" />
                            <x-evento-card label="Trabalho" :count="$evento->trabalhadores_count" :href="route('trabalhadores.index', ['evento' => $evento->idt_evento])"
                                icon="heroicon-o-briefcase" color="orange" />
                            <x-evento-card label="Quadrante" :count="$evento->trabalhadores_count" :href="route('quadrante.list', ['evento' => $evento->idt_evento])"
                                icon="heroicon-o-clipboard" color="zinc" />
                        </div>
                    @endif

                    {{-- Footer de Ações --}}
                    <footer
                        class="p-4 bg-gray-50 dark:bg-zinc-800/50 border-t border-gray-100 dark:border-zinc-700 mt-auto">
                        @if (Auth::user()?->isAdmin())
                            <div class="flex gap-2">
                                {{-- Botão Editar com seu padrão Azul --}}
                                <a href="{{ route('eventos.edit', $evento) }}"
                                    class="flex-1 py-2 bg-blue-600 text-white rounded-md font-bold text-center hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-sm">
                                    Editar
                                </a>
                                <form action="{{ route('eventos.destroy', $evento) }}" method="POST" class="flex-1"
                                    onsubmit="return confirm('Excluir este evento?')">
                                    @csrf @method('DELETE')
                                    <button
                                        class="w-full py-2 bg-red-600 text-white rounded-md font-bold hover:bg-red-700 transition">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        @else
                            @if ($evento->ja_inscrito_participante || $evento->ja_inscrito_voluntario)
                                <div
                                    class="w-full py-2 bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400 rounded-md font-bold text-center flex items-center justify-center gap-2 border border-gray-200 dark:border-zinc-600">
                                    <x-heroicon-s-check-circle class="w-5 h-5" />
                                    Inscrição Confirmada
                                </div>
                            @else
                                @php
                                    $rotaInscricao =
                                        $evento->tip_evento == 'E'
                                            ? route('trabalhadores.create', ['evento' => $evento])
                                            : route('participantes.confirm', [
                                                'evento' => $evento,
                                                'pessoa' => Auth::user()->pessoa,
                                            ]);

                                    $textoBotao = match ($evento->tip_evento) {
                                        'P' => 'Vou Participar',
                                        'D' => 'Bora pro Desafio',
                                        default => 'Quero Trabalhar',
                                    };
                                @endphp
                                <form method="POST" action="{{ $rotaInscricao }}"
                                    onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').classList.add('opacity-50', 'bg-gray-400'); this.querySelector('button').innerHTML = 'Processando...';">
                                    @csrf
                                    {{-- Botão Participar (Verde para diferenciar ação de sucesso, mas com arredondamento padrão) --}}
                                    <button
                                        class="w-full py-2 bg-green-600 text-white rounded-md font-bold hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none transition shadow-md">
                                        {{ $textoBotao }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    </footer>
                </article>
            @empty
                <div class="col-span-full">
                    <x-sem-registro icon="calendar" title="Nenhum evento encontrado" />
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $eventos->links() }}
        </div>
    </section>
</x-layouts.app>
