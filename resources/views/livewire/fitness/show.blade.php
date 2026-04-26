<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Modules\FitnessChallenge\Enums\ModerationStatus;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;
use Modules\FitnessChallenge\Models\FitnessComment;
use Modules\FitnessChallenge\Services\LeaderboardService;

new class extends Component
{
    public FitnessChallenge $challenge;

    public $feed;

    public $leaders;

    public array $comments = [];

    public function mount(FitnessChallenge $challenge, LeaderboardService $leaderboard): void
    {
        $this->challenge = $challenge;
        $this->ensureParticipant();
        $this->loadData($leaderboard);
    }

    public function like(int $checkInId): void
    {
        $checkIn = FitnessCheckIn::where('fitness_challenge_id', $this->challenge->id)
            ->where('moderation_status', ModerationStatus::Approved->value)
            ->findOrFail($checkInId);

        $exists = $checkIn->likes()->where('user_id', Auth::id())->exists();

        $exists
            ? $checkIn->likes()->detach(Auth::id())
            : $checkIn->likes()->attach(Auth::id());

        $this->loadFeed();
    }

    public function comment(int $checkInId): void
    {
        $body = trim($this->comments[$checkInId] ?? '');

        if ($body === '') {
            return;
        }

        $checkIn = FitnessCheckIn::where('fitness_challenge_id', $this->challenge->id)
            ->where('moderation_status', ModerationStatus::Approved->value)
            ->findOrFail($checkInId);

        FitnessComment::create([
            'fitness_check_in_id' => $checkIn->id,
            'user_id' => Auth::id(),
            'body' => $body,
        ]);

        $this->comments[$checkInId] = '';
        $this->loadFeed();
    }

    private function loadData(LeaderboardService $leaderboard): void
    {
        $this->loadFeed();
        $this->leaders = $leaderboard->individual($this->challenge)->take(5);
    }

    private function loadFeed(): void
    {
        $this->feed = $this->challenge->checkIns()
            ->where('moderation_status', ModerationStatus::Approved->value)
            ->with(['user:id,name', 'comments.user:id,name'])
            ->withCount('likes')
            ->latest()
            ->limit(20)
            ->get();
    }

    private function ensureParticipant(): void
    {
        abort_unless(
            $this->challenge->participants()->where('user_id', Auth::id())->exists(),
            403
        );
    }
}; ?>

<section class="w-full space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">{{ $challenge->name }}</flux:heading>
            <flux:subheading>{{ $challenge->description ?: __('Desafio fitness') }}</flux:subheading>
            <div class="mt-3 flex flex-wrap gap-2">
                <flux:badge color="zinc">{{ $challenge->invite_code }}</flux:badge>
                <flux:badge color="{{ $challenge->is_team_challenge ? 'blue' : 'zinc' }}">
                    {{ $challenge->is_team_challenge ? __('Times') : __('Individual') }}
                </flux:badge>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button href="{{ route('fitness.app.check-ins.create', $challenge) }}" wire:navigate variant="primary">{{ __('Registrar treino') }}</flux:button>
            <flux:button href="{{ route('fitness.app.ranking', $challenge) }}" wire:navigate variant="ghost">{{ __('Ranking') }}</flux:button>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            @forelse ($feed as $checkIn)
                <article class="border border-zinc-200 p-5 dark:border-zinc-700" wire:key="fitness-feed-{{ $checkIn->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $checkIn->title }}</h3>
                            <p class="text-sm text-zinc-500">{{ $checkIn->user?->name }} · {{ $checkIn->created_at->diffForHumans() }}</p>
                        </div>
                        <flux:badge color="green">{{ (float) $checkIn->score }} pts</flux:badge>
                    </div>

                    @if ($checkIn->description)
                        <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300">{{ $checkIn->description }}</p>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                        <span>{{ __('Min') }}: {{ $checkIn->duration_minutes ?? '-' }}</span>
                        <span>{{ __('Km') }}: {{ $checkIn->distance_km ?? '-' }}</span>
                        <span>{{ __('Cal') }}: {{ $checkIn->calories ?? '-' }}</span>
                        <span>{{ __('Passos') }}: {{ $checkIn->steps ?? '-' }}</span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <flux:button wire:click="like({{ $checkIn->id }})" size="sm" variant="filled">{{ __('Cheer') }} · {{ $checkIn->likes_count }}</flux:button>
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach ($checkIn->comments as $comment)
                            <p class="text-sm text-zinc-600 dark:text-zinc-400"><strong>{{ $comment->user?->name }}:</strong> {{ $comment->body }}</p>
                        @endforeach

                        <form wire:submit="comment({{ $checkIn->id }})" class="flex gap-2">
                            <flux:input wire:model="comments.{{ $checkIn->id }}" placeholder="{{ __('Comentar') }}" />
                            <flux:button type="submit" size="sm">{{ __('Enviar') }}</flux:button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="border border-zinc-200 p-6 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                    {{ __('Nenhum check-in aprovado ainda.') }}
                </div>
            @endforelse
        </div>

        <aside class="space-y-4">
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Top ranking') }}</flux:heading>
                <div class="mt-3 space-y-2">
                    @foreach ($leaders as $leader)
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ $leader['position'] }}. {{ $leader['name'] }}</span>
                            <strong>{{ $leader['total_score'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</section>
