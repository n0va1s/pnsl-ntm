<x-layouts.app :title="'Trabalhador'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        {{-- Flash messages --}}
        <div>
            <x-session-alert />
        </div>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Quero trabalhar</h1>
            @if ($evento?->exists)
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mt-1">
                    Evento: <b>{{ $evento->des_evento }}</b>
                </p>
            @endif
        </div>

        <div class="flex justify-end mt-4">
            <a href="{{ route('eventos.index') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Eventos
            </a>
        </div>

        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-md shadow p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                <i class="bi bi-plus-circle text-blue-600 text-2xl"></i> Equipes
            </h2>

            <form method="POST" action="{{ route('trabalhadores.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="idt_evento" value="{{ $evento?->idt_evento }}">

                <div class="bg-gray-50 dark:bg-zinc-700 rounded-md p-4">
                    <h3 class="text-lg font-medium mb-3 text-gray-900 dark:text-gray-100">Escolha até 3 equipes</h3>
                    @error('equipes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($equipes as $equipe)
                            <div class="space-y-2">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="equipes[{{ $equipe->idt_equipe }}][selecionado]"
                                        value="1" id="equipe_{{ $equipe->idt_equipe }}"
                                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="equipe_{{ $equipe->idt_equipe }}"
                                        class="block text-sm font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $equipe->des_grupo }}
                                    </label>
                                </div>
                                <textarea name="equipes[{{ $equipe->idt_equipe }}][habilidade]"
                                    class="w-full text-sm p-2 border border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-100"
                                    rows="2" placeholder="Descreva suas habilidades para esta equipe (opcional)"></textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
