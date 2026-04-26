<?php

use App\Models\Equipe;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public Equipe $equipe;

    public string $nom_equipe = '';

    public string $des_slug = '';

    public string $des_descricao = '';

    public bool $ind_ativa = true;

    public function mount(Equipe $equipe): void
    {
        $this->authorize('update', $equipe);

        $this->equipe = $equipe;
        $this->nom_equipe = $equipe->nom_equipe;
        $this->des_slug = $equipe->des_slug ?? '';
        $this->des_descricao = $equipe->des_descricao ?? '';
        $this->ind_ativa = $equipe->ind_ativa;
    }

    public function salvar(): void
    {
        $validated = $this->validate([
            'nom_equipe' => ['required', 'string', 'max:60'],
            'des_slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('equipes', 'des_slug')
                    ->where('idt_movimento', $this->equipe->idt_movimento)
                    ->ignore($this->equipe->idt_equipe, 'idt_equipe'),
            ],
            'des_descricao' => ['nullable', 'string', 'max:500'],
            'ind_ativa' => ['boolean'],
        ]);

        $this->equipe->update($validated);

        $this->redirect(route('equipes.index'), navigate: true);
    }
}; ?>

<section class="w-full max-w-3xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Editar Equipe') }}</flux:heading>
        <flux:subheading>{{ __('Atualize os dados e a disponibilidade da equipe.') }}</flux:subheading>
    </div>

    <form wire:submit="salvar" class="space-y-6">
        <flux:input
            wire:model="nom_equipe"
            :label="__('Nome')"
            type="text"
            required
        />
        <flux:error name="nom_equipe" />

        <flux:input
            wire:model="des_slug"
            :label="__('Slug')"
            type="text"
        />
        <flux:error name="des_slug" />

        <flux:textarea
            wire:model="des_descricao"
            :label="__('Descrição')"
            rows="3"
        />
        <flux:error name="des_descricao" />

        <flux:switch
            wire:model.live="ind_ativa"
            :label="__('Equipe ativa')"
        />

        <div class="flex flex-wrap items-center gap-3">
            <flux:button variant="primary" type="submit">
                {{ __('Salvar') }}
            </flux:button>

            <flux:button href="{{ route('equipes.index') }}" wire:navigate variant="ghost">
                {{ __('Cancelar') }}
            </flux:button>
        </div>
    </form>
</section>
