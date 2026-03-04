@props(['sigla'])

@php
    $siglaUper = strtoupper($sigla ?? '');

    $classes = match ($siglaUper) {
        'ECC' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 border-green-200',
        'SEGUE-ME' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 border-orange-200',
        'VEM' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border-blue-200', // Adicionado VEM
        default => 'bg-gray-100 text-gray-800 dark:bg-zinc-700 dark:text-zinc-300 border-gray-200',
    };
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border $classes"]) }}>
    {{ $sigla ?? 'N/A' }}
</span>
