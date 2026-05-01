<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        <x-session-alert />
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
