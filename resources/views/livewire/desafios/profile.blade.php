<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Modules\FitnessChallenge\Models\FitnessCheckIn;

new class extends Component
{
    public $checkIns;

    public float $totalScore = 0;

    public int $totalCheckIns = 0;

    public function mount(): void
    {
        $this->checkIns = FitnessCheckIn::query()
            ->where('user_id', Auth::id())
            ->with('challenge:id,name')
            ->latest()
            ->limit(40)
            ->get();

        $this->totalScore = (float) $this->checkIns->sum('score');
        $this->totalCheckIns = $this->checkIns->count();
    }
}; ?>

<section class="w-full space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Historico GraceRats') }}</flux:heading>
            <flux:subheading>{{ __('Seus registros enviados para desafios.') }}</flux:subheading>
        </div>
        <flux:button href="{{ route('desafios.index') }}" wire:navigate variant="ghost">{{ __('GraceRats') }}</flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="border border-zinc-200 p-4 dark:border-zinc-700">
            <span class="text-sm text-zinc-500">{{ __('Registros') }}</span>
            <strong class="block text-2xl">{{ $totalCheckIns }}</strong>
        </div>
        <div class="border border-zinc-200 p-4 dark:border-zinc-700">
            <span class="text-sm text-zinc-500">{{ __('Pontos aprovados') }}</span>
            <strong class="block text-2xl">{{ $totalScore }}</strong>
        </div>
    </div>

    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                <tr>
                    <th class="px-4 py-3">{{ __('Registro') }}</th>
                    <th class="px-4 py-3">{{ __('Desafio') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Pontos') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($checkIns as $checkIn)
                    <tr>
                        <td class="px-4 py-3">{{ $checkIn->title }}</td>
                        <td class="px-4 py-3">{{ $checkIn->challenge?->name }}</td>
                        <td class="px-4 py-3"><flux:badge color="{{ $checkIn->moderation_status === 'approved' ? 'green' : 'amber' }}">{{ $checkIn->moderation_status }}</flux:badge></td>
                        <td class="px-4 py-3">{{ (float) $checkIn->score }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-zinc-500">{{ __('Nenhum registro enviado.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
