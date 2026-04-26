<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Volt\Component;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;
use Modules\FitnessChallenge\Services\CheckInAwardService;
use Modules\FitnessChallenge\Services\MediaSafetyService;
use Modules\FitnessChallenge\Services\ScoringService;

new class extends Component
{
    use WithFileUploads;

    public FitnessChallenge $challenge;

    public string $title = '';

    public ?string $description = null;

    public $media = null;

    public ?int $duration_minutes = null;

    public ?float $distance_km = null;

    public ?int $calories = null;

    public ?int $steps = null;

    public ?string $activity_type = null;

    public function mount(FitnessChallenge $challenge): void
    {
        $this->challenge = $challenge;
        abort_unless($challenge->participants()->where('user_id', Auth::id())->exists(), 403);
    }

    public function save(MediaSafetyService $mediaSafety, CheckInAwardService $awards, ScoringService $scoring): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media' => ['required', 'file'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'steps' => ['nullable', 'integer', 'min:0'],
            'activity_type' => ['nullable', 'string', 'max:80'],
        ]);

        $participant = $this->challenge->participants()
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $media = $mediaSafety->prepare($validated, Auth::id(), $this->media);

        $checkIn = FitnessCheckIn::create([
            ...collect($validated)->except(['media'])->all(),
            ...$media,
            'fitness_challenge_id' => $this->challenge->id,
            'user_id' => Auth::id(),
            'fitness_team_id' => $participant->fitness_team_id,
            'score' => 0,
        ]);

        if ($checkIn->moderation_status === 'approved') {
            $awards->award($checkIn, $scoring);
        }

        session()->flash('success', 'Check-in enviado para revisao.');
        $this->redirect(route('fitness.app.challenges.show', $this->challenge), navigate: true);
    }
}; ?>

<section class="w-full max-w-3xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Registrar treino') }}</flux:heading>
        <flux:subheading>{{ $challenge->name }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-5 border border-zinc-200 p-5 dark:border-zinc-700">
        <flux:input wire:model="title" label="{{ __('Titulo do treino') }}" />
        <flux:textarea wire:model="description" label="{{ __('Descricao') }}" />

        <label class="block text-sm">
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ __('Foto ou video da prova') }}</span>
            <input wire:model="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4" class="mt-2 block w-full text-sm">
            @error('media')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="activity_type" label="{{ __('Tipo de atividade') }}" />
            <flux:input wire:model="duration_minutes" type="number" min="0" label="{{ __('Duracao em minutos') }}" />
            <flux:input wire:model="distance_km" type="number" min="0" step="0.01" label="{{ __('Distancia em km') }}" />
            <flux:input wire:model="calories" type="number" min="0" label="{{ __('Calorias') }}" />
            <flux:input wire:model="steps" type="number" min="0" label="{{ __('Passos') }}" />
        </div>

        <flux:callout>{{ __('A prova fica em revisao antes de aparecer no feed ou pontuar.') }}</flux:callout>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">{{ __('Enviar check-in') }}</flux:button>
            <flux:button href="{{ route('fitness.app.challenges.show', $challenge) }}" wire:navigate variant="ghost">{{ __('Cancelar') }}</flux:button>
        </div>
    </form>
</section>
