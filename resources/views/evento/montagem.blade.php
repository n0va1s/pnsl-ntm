<x-layouts.app :title="'Montagem'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">

        {{-- Flash messages --}}
        <div>
            <x-session-alert />
        </div>

        {{-- Título --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Montar Eventos</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400">Hora de confirmar quem trabalhará onde</p>
            @if ($evento?->exists)
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mt-1">
                    Evento: <b>{{ $evento->des_evento }}</b> ({{ $evento->movimento->des_sigla }})
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

        {{-- Cards de Voluntários --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-6">
            @forelse ($voluntarios as $voluntario)
                <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 shadow border space-y-4">

                    {{-- Nome e Apelido --}}
                    <div class="flex items-center justify-center">
                        @if ($voluntario->pessoa->foto && $voluntario->pessoa->foto->med_foto)
                            <img src="{{ asset('storage/' . $voluntario->pessoa->foto->med_foto) }}"
                                alt="Foto de {{ $voluntario->pessoa->nom_pessoa }}"
                                class="w-24 h-24 rounded-full object-cover border border-gray-300 dark:border-zinc-600 shadow-sm">
                        @else
                            <div
                                class="w-24 h-24 rounded-full bg-gray-200 dark:bg-zinc-700 flex items-center justify-center text-gray-400">
                                <x-heroicon-o-user class="w-24 h-24" />
                            </div>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                            {{ $voluntario->pessoa->nom_pessoa }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $voluntario->pessoa->nom_apelido }}</p>
                    </div>

                    {{-- Equipes que a pessoa marcou --}}
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Equipes sugeridas:</p>
                        <ul class="list-none pl-0 text-sm">
                            @forelse ($voluntario->equipes as $equipe_detalhe)
                                {{-- Renomeado para maior clareza --}}
                                <li
                                    class="mb-3 p-3 bg-gray-50 dark:bg-zinc-700 rounded-md shadow-sm border border-gray-200 dark:border-zinc-600">
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $equipe_detalhe->des_grupo }} {{-- Acessa diretamente a descrição do grupo --}}
                                    </p>
                                    @if ($equipe_detalhe->txt_habilidade)
                                        {{-- Verifica a habilidade do objeto detalhado --}}
                                        <p class="mt-1 text-gray-600 dark:text-gray-300 italic">
                                            "{{ $equipe_detalhe->txt_habilidade }}"
                                        </p>
                                    @else
                                        <p class="mt-1 text-gray-500 dark:text-gray-400">
                                            Nenhuma habilidade específica informada para esta equipe.
                                        </p>
                                    @endif
                                </li>
                            @empty
                                <li class="text-gray-500 dark:text-gray-400">Nenhuma equipe de preferência informada.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Formulário para confirmação de equipe --}}
                    <form action="{{ route('montagem.confirm') }}" method="POST">
                        @csrf
                        <input type="hidden" name="idt_voluntario" value="{{ $voluntario->idt_voluntario }}">

                        {{-- Equipe --}}
                        <div class="mb-4">
                            <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Equipe para trabalhar <span class="text-red-600">*</span>
                            </label>
                            <select name="idt_equipe"
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione a equipe</option>
                                @foreach ($equipes as $equipe)
                                    <option value="{{ $equipe->idt_equipe }}">{{ $equipe->des_grupo }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Coordenador --}}
                        <div class="mb-2 flex items-center gap-2">
                            <input type="hidden" name="ind_coordenador" value="0">
                            <input type="checkbox" name="ind_coordenador" value="1" id="ind_coordenador"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="ind_coordenador" class="text-sm text-gray-700 dark:text-gray-300">Será
                                coordenador(a) da
                                equipe</label>
                        </div>

                        {{-- Primeira vez --}}
                        <div class="mb-4 flex items-center gap-2">
                            <input type="hidden" name="ind_primeira_vez" value="0">
                            <input type="checkbox" name="ind_primeira_vez" value="1" id="ind_primeira_vez"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="ind_primeira_vez" class="text-sm text-gray-700 dark:text-gray-300">É a primeira
                                vez no encontro</label>
                        </div>

                        {{-- Botão --}}
                        <button type="submit"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                            Confirmar Equipe
                        </button>
                    </form>

                </div>
            @empty
                <div class="col-span-full">
                    <div
                        class="flex flex-col items-center justify-center text-center p-10 bg-white dark:bg-zinc-800 rounded-xl shadow border border-dashed border-gray-300 dark:border-zinc-600">
                        <x-heroicon-o-hand-raised class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                        <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Nenhum voluntário(a)
                            encontrado(a)</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Quando houver voluntários cadastrados, eles aparecerão aqui.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginação --}}
        <div class="mt-6">

        </div>
    </section>
</x-layouts.app>
