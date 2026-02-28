{{-- resources/views/components/session-alert.blade.php --}}
@php
    // Detectamos o tipo e a mensagem diretamente da sessão
    $type = session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : (session('info') ? 'info' : null)));
    $message = session('success') ?? session('error') ?? session('warning') ?? session('info');

    $colors = [
        'success' => 'bg-green-600',
        'error'   => 'bg-red-600',
        'warning' => 'bg-yellow-500',
        'info'    => 'bg-blue-500',
    ];
@endphp

@if ($type && $message)
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)" {{-- Aumentado para 4s para melhor leitura --}}
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-[-20px]"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-lg text-white font-bold shadow-2xl flex items-center gap-3 {{ $colors[$type] }}"
        role="alert">

        @switch($type)
            @case('success') <x-heroicon-o-check-circle class="w-6 h-6" /> @break
            @case('error')   <x-heroicon-o-x-circle class="w-6 h-6" /> @break
            @case('warning') <x-heroicon-o-exclamation-triangle class="w-6 h-6" /> @break
            @case('info')    <x-heroicon-o-information-circle class="w-6 h-6" /> @break
        @endswitch

        <span>{{ $message }}</span>
        
        {{-- Botão de fechar manual --}}
        <button @click="show = false" class="ml-auto hover:opacity-70">
            <x-heroicon-s-x-mark class="w-4 h-4" />
        </button>
    </div>
@endif