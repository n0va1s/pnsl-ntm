<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Services\LeaderboardService;

new class extends Component
{
    public FitnessChallenge $challenge;

    public $individual;

    public $teams;

    public function mount(FitnessChallenge $challenge, LeaderboardService $leaderboard): void
    {
        $this->challenge = $challenge;
        abort_unless($challenge->participants()->where('user_id', Auth::id())->exists(), 403);

        $this->individual = $leaderboard->individual($challenge);
        $this->teams = $leaderboard->teams($challenge);
    }
}; ?>

<section class="w-full space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Ranking') }}</flux:heading>
            <flux:subheading>{{ $challenge->name }}</flux:subheading>
        </div>
        <flux:button href="{{ route('fitness.app.challenges.show', $challenge) }}" wire:navigate variant="ghost">{{ __('Voltar') }}</flux:button>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Individual') }}</flux:heading>
            <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($individual as $row)
                    <div class="flex items-center justify-between py-3 {{ $row['user_id'] === Auth::id() ? 'bg-zinc-100 px-2 dark:bg-zinc-800' : '' }}">
                        <span>{{ $row['position'] }}. {{ $row['name'] }}</span>
                        <strong>{{ $row['total_score'] }} pts</strong>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Times') }}</flux:heading>
            <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($teams as $row)
                    <div class="flex items-center justify-between py-3">
                        <span>{{ $row['position'] }}. {{ $row['name'] }}</span>
                        <strong>{{ $row['total_score'] }} pts</strong>
                    </div>
                @empty
                    <p class="py-3 text-sm text-zinc-500">{{ __('Nenhum time criado.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
