<x-layouts.public.header :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.public.header>
