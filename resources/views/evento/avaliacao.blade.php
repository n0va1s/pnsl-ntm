<x-layouts.app :title="'Avaliação'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">

        {{-- Flash messages --}}
        <div>
            <x-session-alert />
        </div>

        {{-- Título --}}
        <div class="mb-6">
            {{-- Título da Página --}}
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Avaliação do Trabalhador</h1>
            <p class="text-gray-700 mt-1 dark:text-gray-400 text-lg">Hora de dar aquele feedback</p>
        </div>

        @if ($trabalhador)
            {{-- Informações do Trabalhador --}}
            <div
                class="mb-8 p-4 rounded-lg border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2">
                    Trabalhador(a):
                </h2>
                <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    <li><strong>Pessoa:</strong> {{ $trabalhador->pessoa->nom_pessoa }}
                        ({{ $trabalhador->pessoa->nom_apelido }})</li>
                    <li><strong>Evento:</strong> {{ $trabalhador->evento->des_evento }}
                        ({{ $trabalhador->evento->movimento->des_sigla }})</li>
                    <li><strong>Equipe:</strong> {{ $trabalhador->equipe->des_grupo }}</li>
                </ul>
            </div>
        @else
            <div class="col-span-full text-center text-gray-500 dark:text-gray-300">
                Nenhum trabalhador encontrado.
            </div>
        @endif

        {{-- Formulário --}}
        <form action="{{ route('avaliacao.send') }}" method="POST" class="space-y-6">
            @csrf

            <input type="hidden" name="idt_trabalhador" value="{{ $trabalhador?->idt_trabalhador }}">

            <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 gap-4">
                @php
                    $checkboxes = [
                        'ind_recomendado' => 'Recomendado(a) para futuros encontros',
                        'ind_lideranca' => 'Demonstra liderança',
                        'ind_destaque' => 'Se destacou neste evento',
                        'ind_camiseta_pediu' => 'Solicitou camiseta',
                        'ind_camiseta_pagou' => 'Pagou pela camiseta',
                    ];
                @endphp

                @foreach ($checkboxes as $name => $label)
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="{{ $name }}" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>

            {{-- Botão --}}
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <x-heroicon-o-paper-airplane class="w-5 h-5 mr-2" />
                    Enviar Avaliação
                </button>
            </div>
        </form>
    </section>
</x-layouts.app>
