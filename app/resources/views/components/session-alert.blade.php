@props([
    'type' => session('success') ? 'success' : (session('error')? 'error' : null),
    'message' => session('success') ?? session('error')
])

@php
    $colors= [
        'success' => 'bg-green-600',
        'error' => 'bg-red-600',
        'warning' => 'bg-yellow-500',
        'info' => 'bg-blue-500',
    ];
@endphp


@if ($type && $message)
    <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            class="fixed top-6 left-1/2 z-50 px-4 py-3 rounded-md text-white font-semibold shadow-lg flex items-center gap-2 {{ $colors[$type] ?? 'bg-gray-600'}}"
            role="alert">

            @if ($type === 'success')
                <x-heroicon-o-check-circle class="w-6 h-6 text-white" />
            @elseif ($type === 'error')
                <x-heroicon-o-x-circle class="w-6 h-6 text-white" />
            @elseif ($type === 'warning')
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
            @elseif ($type === 'info')
                <x-heroicon-o-information-circle class="w-6 h-6 text-white" />
            @endif
            <span>{{ $message }}</span
    </div>
@endif
