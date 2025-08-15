<x-layouts.app :title="'Informações do Evento'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">

        <div class=" flex mb-6 gap-4 items-center ">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Informações do Evento</h1>
            <div>
                @php
                    $sigla = $evento->movimento->des_sigla;

                    $confirmado = in_array($evento->idt_evento, $posEncontrosInscritos);

                    $feito = in_array($evento->idt_evento, $eventosInscritos);

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
                class="inline-flex items-center rounded-full px-4 py-1.5 text-xs font-medium {{ $badgeClasses }}">
                {{ $sigla }}
            </span>
            </div>
        </div>
        <div class="flex justify-end mt-4 mb-6">
            <x-botao-navegar href="{{ route('eventos.index') }}" aria-label="Voltar para a lista de eventos">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Eventos
            </x-botao-navegar>
        </div>
    

        <!-- Botão para voltar à lista de eventos -->

        <div class="mb-6 bg-white dark:bg-zinc-700 rounded-md shadow p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                <i class="bi bi-info-circle text-blue-600 text-2xl"></i> Detalhes do Evento
            </h2>
            <div>
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label for="des_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nome do Evento
                        </label>
                        <div
                            class="w-full rounded-md dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 text-2xl">
                            {{ $evento->des_evento }}
                        </div>
                </div>
                <div class="w-1/2">
                    <label for="des_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Número do Evento
                    </label>

                    <div
                        class="w-full rounded-md  dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 text-2xl">
                        {{ $evento->num_evento }}
                    </div>
                </div>


        </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-4">


                    <div class="flex justify-between items-start mb-2">


                    </div>
                </div>
            <div class="flex gap-4 mt-4">
                <div class="w-1/2">
                    <label for="dat_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Data de Início do Evento
                    </label>
                    <div class="w-full rounded-md dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 text-2xl">
                        {{ \Carbon\Carbon::parse($evento->dat_inicio)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="w-1/2">
                    <label for="dat_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Data de Término do Evento
                    </label>
                    <div class="w-full rounded-md  dark:border-zinc-400 px-3 py-2 text-gray-900 dark:text-gray-100 text-2xl">
                        {{ \Carbon\Carbon::parse($evento->dat_termino)->format('d/m/Y') }}
                    </div>
                </div>
            </div>



            <div class="gap-4 mt-4">
                <label for="inf_evento" class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Informações do Evento
                </label>
                <div class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100">
                    {{ $evento->inf_evento ?? 'Nenhuma informação adicional.' }}
                </div>
            </div>
        </div>
    </section>
    @if ($evento->ind_pos_encontro)
        <div class="flex justify-center mt-4">
            <div class="w-full flex justify-center">
                @if ($confirmado)
                    <div
                        class="w-full max-w-80 inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-gray-300 text-gray-700 text-sm font-semibold cursor-default">
                        <x-heroicon-o-check-circle class="w-8 h-8" />
                        Confirmado
                    </div>
                @else
                    <form method="POST"
                        action="{{ route('participantes.confirm', ['evento' => $evento->idt_evento, 'pessoa' => $pessoa->idt_pessoa]) }}"
                        onsubmit="return confirm('Confirmação a participação no nosso evento?');"
                        class="w-full max-w-80">
                        @csrf

                        <button type="submit" title="Tô dentro!"
                            class="mx-auto w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-green-700 text-white text-sm font-semibold hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-150">
                            <x-heroicon-o-check-circle class="w-8 h-8" />
                            Eu vou
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @else
        <div class="flex justify-center mt-4">
            <div class="w-full flex justify-center">
                @if ($feito)
                    <div
                        class="w-full max-w-80 inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-gray-300 text-gray-700 text-sm font-semibold cursor-default">
                        <x-heroicon-o-check-circle class="w-8 h-8" />
                        Feito
                    </div>
                @else
                    <a href="{{ route('trabalhadores.create', ['evento' => $evento->idt_evento]) }}"
                        class="mx-auto w-full max-w-80 inline-flex justify-center items-center gap-2 px-4 py-2 rounded-md bg-green-700 text-white text-sm font-semibold hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-150"
                        title="Quero trabalhar neste evento">
                        <x-heroicon-o-hand-raised class="w-8 h-8" />
                        Quero trabalhar
                    </a>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
