@props(['label', 'count', 'href', 'icon', 'color' => 'blue'])

@php
    // Mapeamento de cores suaves para os mini-cards de estatística
    $colors = [
        'blue' =>
            'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30',
        'green' =>
            'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800/30',
        'orange' =>
            'bg-orange-50 text-orange-700 border-orange-100 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30',
        'zinc' =>
            'bg-gray-50 text-gray-700 border-gray-100 dark:bg-zinc-700/50 dark:text-zinc-300 dark:border-zinc-600',
    ];

    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<a href="{{ $href }}"
    class="flex items-center p-2 rounded-lg border {{ $colorClass }} hover:opacity-80 transition-all group min-w-0 shadow-sm">

    {{-- Ícone fixo --}}
    <div class="flex-shrink-0 mr-2">
        <x-dynamic-component :component="$icon" class="w-4 h-4" />
    </div>

    {{-- Container de texto com min-w-0 para permitir truncamento --}}
    <div class="flex-1 min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-tighter opacity-70 truncate">
            {{ $label }}
        </p>
        <p class="text-sm font-black leading-none tabular-nums truncate">
            {{ $count }}
        </p>
    </div>

    {{-- Setinha indicando que é um link (opcional, aparece no hover) --}}
    <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity ml-1">
        <x-heroicon-s-chevron-right class="w-3 h-3" />
    </div>
</a>
