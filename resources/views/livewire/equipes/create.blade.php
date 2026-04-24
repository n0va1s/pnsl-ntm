<?php

use App\Models\Equipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $nom_equipe = '';

    public string $des_slug = '';

    public string $des_descricao = '';

    public function mount(): void
    {
        $this->authorize('create', Equipe::class);
    }

    public function salvar(): void
    {
        $idtMovimento = $this->idtMovimentoUsuario();

        abort_if($idtMovimento === null, 403);

        $validated = $this->validate([
            'nom_equipe' => ['required', 'string', 'max:60'],
            'des_slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('equipes', 'des_slug')
                    ->where('idt_movimento', $idtMovimento),
            ],
            'des_descricao' => ['nullable', 'string', 'max:500'],
        ]);

        if (blank($validated['des_slug'] ?? null)) {
            unset($validated['des_slug']);
        }

        Equipe::create(array_merge($validated, [
            'idt_movimento' => $idtMovimento,
        ]));

        $this->redirect(route('equipes.index'), navigate: true);
    }

    private function idtMovimentoUsuario(): ?int
    {
        return DB::table('equipe_usuario')
            ->join('equipes', 'equipes.idt_equipe', '=', 'equipe_usuario.idt_equipe')
            ->where('equipe_usuario.user_id', Auth::id())
            ->whereNull('equipe_usuario.deleted_at')
            ->value('equipes.idt_movimento');
    }
}; ?>

<section class="w-full max-w-3xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Nova Equipe') }}</flux:heading>
        <flux:subheading>{{ __('Cadastre uma equipe para o movimento atual.') }}</flux:subheading>
    </div>

    <form wire:submit="salvar" class="space-y-6">
        <flux:input
            wire:model="nom_equipe"
            :label="__('Nome')"
            type="text"
            required
            :placeholder="__('Ex.: Oração, Recepção...')"
        />
        <flux:error name="nom_equipe" />

        <flux:textarea
            wire:model="des_descricao"
            :label="__('Descrição')"
            rows="3"
        />
        <flux:error name="des_descricao" />

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
