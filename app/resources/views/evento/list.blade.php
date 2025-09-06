<x-layouts.app :title="'Evento'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div>
            <x-session-alert />
        </div>
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
                    class="flex flex-col justify-between h-full min-h-[200px] dark:bg-zinc-800 rounded-xl p-4 border border-gray-200 dark:border-zinc-700 shadow-sm  {{ $evento->tip_evento == 'D' ? 'bg-yellow-200' : 'bg-white' }}">

                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $evento->des_evento }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nº {{ $evento->num_evento }}</p>
                        </div>
                        <div>
                            @php
                                $tipos = match ($evento->tip_evento) {
                                    'E' => 'Encontro Anual',
                                    'P' => 'Pós-Encontro',
                                    'D' => 'Desafio',
                                };

                                if ($evento->tip_evento == 'E') {
                                    $inscrito = in_array($evento->idt_evento, $encontrosInscritos);
                                } else {
                                    $inscrito = in_array($evento->idt_evento, $eventosInscritos);
                                }

                                $sigla = $evento->movimento->des_sigla;

                                $rotaFichas = match ($sigla) {
                                    'ECC' => route('ecc.index', ['evento' => $evento->idt_evento]),
                                    'VEM' => route('vem.index', ['evento' => $evento->idt_evento]),
                                    'Segue-Me' => route('sgm.index', ['evento' => $evento->idt_evento]),
                                    default => '#',
                                };

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

                    <div class="mb-3 text-sm text-gray-700 dark:text-gray-300 flex items-center justify-between"
                        @if (!empty($evento->txt_informacao)) x-data="{ showInfo: false }" @endif>
                        <div>
                            <span class="font-semibold">Período:</span><br>
                            <time
                                datetime="{{ $evento->dat_inicio->toDateString() }}">{{ $evento->getDataInicioFormatada() }}</time>
                            <span class="text-gray-400"> até </span>
                            <time
                                datetime="{{ $evento->dat_termino->toDateString() }}">{{ $evento->getDataTerminoFormatada() }}</time>
                        </div>
                        @if (!empty($evento->txt_informacao))
                            <div class="relative flex items-center">
                                <button type="button" @click="showInfo = !showInfo" class="focus:outline-none">
                                    <x-heroicon-o-information-circle class="w-10 h-10 text-blue-500 cursor-pointer ml-2"
                                        aria-label="icone-informacao" />
                                </button>
                                <div x-show="showInfo" @click.away="showInfo = false"
                                    class="absolute right-0 z-20 mt-2 w-64 p-4 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 rounded shadow text-gray-800 dark:text-gray-100 text-sm"
                                    x-transition>
                                    <span class="font-semibold block mb-1">Informações:</span>
                                    <span>{{ $evento->txt_informacao }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3 text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-semibold">Tipo: {{ $tipos }}</span><br>
                    </div>
                    @if (Auth::user() && Auth::user()->isAdmin())
                        {{-- Pós-encontro ou desafio --}}
                        @if ($evento->tip_evento == 'P')
                            <div class="flex justify-center w-full mb-4 text-xs text-white">
                                <div class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md"
                                    title="Participantes">
                                    <x-heroicon-o-user-group class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->participantes_count ?? 0 }}</span>
                                </div>
                            </div>
                        @elseif ($evento->tip_evento == 'D')
                            <div class="flex justify-center w-full mb-4 text-xs text-white">
                                <div class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md"
                                    title="Participantes">
                                    <x-heroicon-o-user-group class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->participantes_count ?? 0 }}</span>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-nowrap justify-center gap-2 mb-4 text-white text-xs">
                                {{-- Fichas --}}
                                <a href="{{ $rotaFichas }}" title="Fichas cadastradas"
                                    class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md">
                                    <x-heroicon-o-document-text class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->fichas_count ?? 0 }}</span>
                                </a>

                                {{-- Participantes --}}
                                <a href="{{ route('participantes.index', ['evento' => $evento->idt_evento]) }}"
                                    title="Participantes"
                                    class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md">
                                    <x-heroicon-o-user-group class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->participantes_count ?? 0 }}</span>
                                </a>

                                {{-- Voluntários --}}
                                <a href="{{ route('montagem.list', ['evento' => $evento->idt_evento]) }}"
                                    title="Voluntários"
                                    class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md">
                                    <x-heroicon-o-hand-raised class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->voluntarios_count ?? 0 }}</span>
                                </a>

                                {{-- Trabalhadores --}}
                                <a href="{{ route('trabalhadores.index', ['evento' => $evento->idt_evento]) }}"
                                    title="Trabalhadores"
                                    class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md">
                                    <x-heroicon-o-briefcase class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->trabalhadores_count ?? 0 }}</span>
                                </a>

                                {{-- Foto Oficial --}}
                                <a href="{{ route('eventos.edit', ['evento' => $evento->idt_evento]) }}"
                                    title="Foto oficial"
                                    class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md">
                                    <x-heroicon-o-camera class="w-4 h-4 mb-0.5" />
                                    <span>{{ $evento->foto ? '1' : '0' }}</span>
                                </a>

                                {{-- Quadrante --}}
                                @if ($evento->trabalhadores_count)
                                    <a href="{{ route('quadrante.list', ['evento' => $evento->idt_evento]) }}"
                                        title="Quadrante"
                                        class="flex flex-col items-center justify-center w-14 py-1 bg-green-600 rounded-md">
                                        <x-heroicon-o-clipboard class="w-4 h-4 mb-0.5" />
                                        <span>{{ $evento->trabalhadores_count }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                        <div class="flex gap-0 mt-4 border-t pt-3">
                            <a href="{{ route('eventos.edit', $evento) }}"
                                class="flex items-center justify-center w-1/2 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 px-3 py-2 rounded-bl-lg transition-colors">
                                <x-heroicon-o-pencil-square class="w-5 h-5 mr-1" />
                                Editar
                            </a>

                            <form method="POST" action="{{ route('eventos.destroy', $evento) }}"
                                onsubmit="return confirm('Tem certeza que deseja excluir este evento?');"
                                class="w-1/2">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex items-center justify-center w-full text-sm font-semibold text-red-600 hover:text-white hover:bg-red-600 px-3 py-2 rounded-br-lg transition-colors">
                                    <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                                    Excluir
                                </button>
                            </form>
                        </div>
                    @else
                        @if ($evento->tip_evento == 'P')
                            <div class="mt-4">
                                @if ($inscrito)
                                    <div
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-gray-300 text-gray-700 text-sm font-semibold cursor-default">
                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                        Confirmado
                                    </div>
                                @else
                                    <form method="POST"
                                        action="{{ route('participantes.confirm', ['evento' => $evento->idt_evento, 'pessoa' => $pessoa->idt_pessoa]) }}"
                                        onsubmit="return confirm('Confirmação a participação no nosso evento?');"
                                        class="w-full">
                                        @csrf

                                        <button type="submit" title="Tô dentro!"
                                            class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150">
                                            <x-heroicon-o-check-circle class="w-5 h-5" />
                                            Eu vou
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @elseif ($evento->tip_evento == 'D')
                            <div class="mt-4">
                                @if ($inscrito)
                                    <div
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-gray-300 text-gray-700 text-sm font-semibold cursor-default">
                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                        Pontuado
                                    </div>
                                @else
                                    <form method="POST"
                                        action="{{ route('participantes.confirm', ['evento' => $evento->idt_evento, 'pessoa' => $pessoa->idt_pessoa]) }}"
                                        onsubmit="return confirm('Confirmação a participação no nosso desafio?');"
                                        class="w-full">
                                        @csrf

                                        <button type="submit" title="Quero Participar"
                                            class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150">
                                            <x-heroicon-o-check-circle class="w-5 h-5" />
                                            Tô dentro
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <div class="mt-4">
                                @if ($inscrito)
                                    <div
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-gray-300 text-gray-700 text-sm font-semibold cursor-default">
                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                        Feito
                                    </div>
                                @else
                                    <a href="{{ route('trabalhadores.create', ['evento' => $evento->idt_evento]) }}"
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150"
                                        title="Quero trabalhar neste evento">
                                        <x-heroicon-o-hand-raised class="w-5 h-5" />
                                        Quero trabalhar
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            @empty
                <div class="col-span-full">
                    <div
                        class="flex flex-col items-center justify-center text-center p-10 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                        <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum evento)
                            encontrado</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Quando houver eventos cadastrados, eles aparecerão aqui.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="mt-6">
            {{ $eventos->links() }}
        </div>
    </section>
</x-layouts.app>
