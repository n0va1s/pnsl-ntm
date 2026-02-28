@props(['title', 'value', 'icon', 'color' => 'blue'])

@php
    $colors = [
        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'icon' => 'text-blue-600'],
        'green' => ['bg' => 'bg-green-50 dark:bg-green-900/20', 'icon' => 'text-green-600'],
        'yellow' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'icon' => 'text-amber-600'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20', 'icon' => 'text-purple-600'],
    ];

    $selectedColor = $colors[$color] ?? $colors['blue'];
@endphp

<div
    class="flex flex-col p-5 bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 shadow-sm transition-all hover:shadow-md min-w-0 overflow-hidden">
    {{-- Linha 1: Ícone à esquerda e Título à direita --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-xl {{ $selectedColor['bg'] }}">
            <x-dynamic-component :component="$icon" class="h-5 w-5 {{ $selectedColor['icon'] }}" />
        </div>
        <h3 class="text-[11px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-widest truncate">
            {{ $title }}
        </h3>
    </div>

    {{-- Linha 2: Número Centralizado --}}
    <div class="flex justify-center items-center pb-2">
        <span class="text-4xl font-black text-gray-900 dark:text-white tabular-nums leading-none">
            {{ $value }}
        </span>
    </div>
</div>
