<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Modules\FitnessChallenge\Models\FitnessChallenge;

new class extends Component
{
    public string $inviteCode = '';

    public $challenges;

    public function mount(): void
    {
        $this->loadChallenges();
    }

    public function joinByInvite(): void
    {
        $validated = $this->validate([
            'inviteCode' => ['required', 'string', 'max:16'],
        ]);

        $challenge = FitnessChallenge::where('invite_code', strtoupper($validated['inviteCode']))->first();

        if (! $challenge) {
            $this->addError('inviteCode', 'Convite nao encontrado.');

            return;
        }

        $challenge->participants()->firstOrCreate(
            ['user_id' => Auth::id()],
            ['joined_at' => now()]
        );

        $this->inviteCode = '';
        $this->loadChallenges();
        session()->flash('success', 'Voce entrou no desafio.');
    }

    public function loadChallenges(): void
    {
        $this->challenges = FitnessChallenge::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', Auth::id()))
            ->withCount(['participants', 'checkIns'])
            ->latest()
            ->get();
    }
}; ?>

<section class="w-full space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Desafios') }}</flux:heading>
            <flux:subheading>{{ __('Registre cumprimentos, acompanhe desafios e dispute pontuacao com o grupo.') }}</flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:button href="{{ route('desafios.app.profile') }}" wire:navigate variant="ghost">{{ __('Historico') }}</flux:button>
            <flux:button href="{{ route('desafios.app.challenges.create') }}" wire:navigate variant="primary">{{ __('Novo desafio') }}</flux:button>
        </div>
    </div>

    @if (session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    <form wire:submit="joinByInvite" class="grid gap-3 border border-zinc-200 p-4 dark:border-zinc-700 sm:grid-cols-[1fr_auto]">
        <flux:input wire:model="inviteCode" label="{{ __('Codigo de convite') }}" placeholder="RUN12345" />
        <div class="self-end">
            <flux:button type="submit" variant="filled">{{ __('Entrar') }}</flux:button>
        </div>
    </form>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($challenges as $challenge)
            <article class="border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">{{ $challenge->name }}</flux:heading>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $challenge->description ?: __('Sem descricao.') }}</p>
                    </div>
                    <flux:badge color="{{ $challenge->status === 'active' ? 'green' : 'zinc' }}">{{ $challenge->status }}</flux:badge>
                </div>

                <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">{{ __('Pontos') }}</dt>
                        <dd class="font-semibold">{{ $challenge->participants->firstWhere('user_id', Auth::id())?->total_score ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Pessoas') }}</dt>
                        <dd class="font-semibold">{{ $challenge->participants_count }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Posts') }}</dt>
                        <dd class="font-semibold">{{ $challenge->check_ins_count }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <flux:button href="{{ route('desafios.app.challenges.show', $challenge) }}" wire:navigate>{{ __('Abrir') }}</flux:button>
                    <flux:button href="{{ route('desafios.app.check-ins.create', $challenge) }}" wire:navigate variant="filled">{{ __('Registro') }}</flux:button>
                    <flux:button href="{{ route('desafios.app.ranking', $challenge) }}" wire:navigate variant="ghost">{{ __('Ranking') }}</flux:button>
                </div>
            </article>
        @empty
            <div class="border border-zinc-200 p-6 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400 lg:col-span-2">
                {{ __('Voce ainda nao participa de Desafios.') }}
            </div>
        @endforelse
    </div>
</section>
