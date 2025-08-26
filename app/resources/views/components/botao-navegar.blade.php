<div class="flex justify-end mt-4">
    <a href="{{ $href ?? '#' }}"
        class="inline-flex items-center px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 dark:hover:bg-green-600 focus:ring-2 focus:ring-green-500 focus:outline-none"
        aria-label="{{ $ariaLabel ?? 'Botão' }}">
        {{ $slot->isEmpty() ? 'Botão' : $slot }}
    </a>
</div>
