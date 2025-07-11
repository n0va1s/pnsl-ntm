<x-layouts.app :title="'Detalhes do Trabalhador'">
    <section class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Detalhes do Trabalhador</h1>
            <a href="{{ route('trabalhadores.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-md focus:ring-2 focus:ring-gray-500 focus:outline-none text-gray-800">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Voltar à lista
            </a>
        </div>

        {{-- Dados Pessoais --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Pessoais</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome Completo</label>
                    <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $trabalhador->nom_pessoa }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apelido</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $trabalhador->nom_apelido ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $trabalhador->tel_pessoa }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Endereço</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $trabalhador->des_endereco ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Dados do Trabalho --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados do Trabalho</h2>

            @if($trabalhador->trabalhador && $trabalhador->trabalhador->count() > 0)
                <div class="space-y-4">
                    @foreach ($trabalhador->trabalhador as $trabalho)
                        <div class="border border-gray-200 dark:border-zinc-600 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evento</label>
                                    <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $trabalho->evento->des_evento ?? 'Sem evento' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Equipe</label>
                                    <p class="text-gray-900 dark:text-gray-100">{{ $trabalho->equipe->des_grupo ?? 'Sem equipe' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Habilidades</label>
                                    <p class="text-gray-900 dark:text-gray-100">{{ $trabalho->des_habilidades ?? '—' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primeira vez?</label>
                                    <p class="text-gray-900 dark:text-gray-100">{{ $trabalhador->bol_primeira_vez ? 'Sim' : 'Não' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 italic">Nenhum trabalho registrado.</p>
            @endif
        </div>
    </section>
</x-layouts.app>
