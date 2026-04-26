<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;

new class extends Component
{
    public string $name = '';

    public ?string $description = null;

    public string $starts_at = '';

    public string $ends_at = '';

    public string $scoring_type = 'total_workouts';

    public bool $is_team_challenge = false;

    public ?int $max_participants = null;

    public function mount(): void
    {
        $this->starts_at = now()->toDateString();
        $this->ends_at = now()->addMonth()->toDateString();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'scoring_type' => ['required', Rule::in(array_map(fn (ScoringType $type) => $type->value, ScoringType::cases()))],
            'is_team_challenge' => ['boolean'],
            'max_participants' => ['nullable', 'integer', 'min:2'],
        ]);

        $challenge = FitnessChallenge::create([
            ...$validated,
            'created_by' => Auth::id(),
            'invite_code' => $this->inviteCode(),
            'status' => now()->toDateString() < $validated['starts_at'] ? 'upcoming' : 'active',
        ]);

        $challenge->participants()->create([
            'user_id' => Auth::id(),
            'joined_at' => now(),
        ]);

        $this->redirect(route('fitness.app.challenges.show', $challenge), navigate: true);
    }

    private function inviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (FitnessChallenge::where('invite_code', $code)->exists());

        return $code;
    }
}; ?>

<section class="w-full max-w-3xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Novo desafio fitness') }}</flux:heading>
        <flux:subheading>{{ __('Configure como as pessoas vao farmar pontuacao.') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-5 border border-zinc-200 p-5 dark:border-zinc-700">
        <flux:input wire:model="name" label="{{ __('Nome') }}" />
        <flux:textarea wire:model="description" label="{{ __('Descricao') }}" />

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="starts_at" type="date" label="{{ __('Inicio') }}" />
            <flux:input wire:model="ends_at" type="date" label="{{ __('Fim') }}" />
        </div>

        <flux:select wire:model="scoring_type" label="{{ __('Pontuacao') }}">
            <option value="total_workouts">{{ __('1 ponto por treino') }}</option>
            <option value="total_minutes">{{ __('Minutos treinados') }}</option>
            <option value="total_calories">{{ __('Calorias') }}</option>
            <option value="total_distance">{{ __('Distancia') }}</option>
            <option value="total_steps">{{ __('Passos') }}</option>
            <option value="hustle_points">{{ __('Hustle points') }}</option>
        </flux:select>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="max_participants" type="number" min="2" label="{{ __('Maximo de participantes') }}" />
            <label class="flex items-center gap-3 self-end text-sm">
                <input wire:model="is_team_challenge" type="checkbox" class="rounded border-zinc-300">
                <span>{{ __('Desafio por times') }}</span>
            </label>
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">{{ __('Criar desafio') }}</flux:button>
            <flux:button href="{{ route('fitness.index') }}" wire:navigate variant="ghost">{{ __('Cancelar') }}</flux:button>
        </div>
    </form>
</section>
