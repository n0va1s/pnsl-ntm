{{-- Versão para Modo Claro --}}
<img
    src="{{ asset('img/logo-claro.png') }}"
    {{ $attributes->merge(['class' => 'block dark:hidden h-16 w-auto']) }}
    alt="Logo Movimento Canônico">

{{-- Versão para Modo Escuro --}}
<img
    src="{{ asset('img/logo-escuro.png') }}"
    {{ $attributes->merge(['class' => 'hidden dark:block h-16 w-auto']) }}
    alt="Logo Movimento Canônico (Modo Escuro)">
