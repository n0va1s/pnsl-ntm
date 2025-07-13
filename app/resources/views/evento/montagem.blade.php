<x-layouts.app :title="'Montagem'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">

        {{-- Flash messages --}}
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

        {{-- Cards de Voluntários --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-6">
            @forelse ($voluntarios as $voluntario)
                <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 shadow border space-y-4">

                    {{-- Nome e Apelido --}}
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                            {{ $voluntario->pessoa->nom_pessoa }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $voluntario->pessoa->nom_apelido }}</p>
                    </div>

                    {{-- Equipes que a pessoa marcou --}}
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Equipes que a pessoa gostaria de
                            trabalhar:</p>
                        <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-400">
                            @foreach ($voluntario->equipes as $equipe)
                                <li>{{ $equipe->des_grupo }}</li>
                            @endforeach
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
                                vez no encontroe</label>
                        </div>

                        {{-- Botão --}}
                        <button type="submit"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                            Confirmar Equipe
                        </button>
                    </form>

                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 dark:text-gray-300">
                    Nenhum voluntário encontrado.
                </div>
            @endforelse
        </div>

        {{-- Paginação --}}
        <div class="mt-6">

        </div>
    </section>
</x-layouts.app>
