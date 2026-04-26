<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public Equipe $equipe;

    public ?int $userId = null;

    public string $papel = 'membro_equipe';

    public function mount(Equipe $equipe): void
    {
        $this->authorize('assignMembers', $equipe);
        $this->equipe = $equipe;
    }

    public function usuariosElegiveis(): Collection
    {
        $papel = PapelEquipe::tryFrom($this->papel) ?? PapelEquipe::MembroEquipe;
        $sexo = $papel->requerSexo();

        return User::query()
            ->with('pessoa')
            ->whereHas('pessoa', function ($query) use ($sexo) {
                if ($sexo) {
                    $query->where('tip_genero', $sexo);
                }
            })
            ->whereDoesntHave('equipes', fn ($query) => $query->where('equipes.idt_equipe', $this->equipe->idt_equipe))
            ->orderBy('name')
            ->get();
    }

    public function vinculosAtivos(): Collection
    {
        return EquipeUsuario::query()
            ->with('usuario.pessoa')
            ->where('idt_equipe', $this->equipe->idt_equipe)
            ->orderBy('papel')
            ->get();
    }

    public function atribuir(): void
    {
        $this->authorize('assignMembers', $this->equipe);

        $validated = $this->validate([
            'userId' => ['required', 'integer', 'exists:users,id'],
            'papel' => ['required', 'string', 'in:'.implode(',', array_keys(PapelEquipe::opcoes()))],
        ], [
            'userId.required' => 'Selecione uma pessoa para atribuir.',
            'papel.in' => 'Selecione um papel valido.',
        ]);

        $papel = PapelEquipe::from($validated['papel']);
        $this->validarSexoDoUsuario((int) $validated['userId'], $papel);
        $this->validarLimiteCoordenador($papel);

        $vinculo = EquipeUsuario::withTrashed()
            ->where('idt_equipe', $this->equipe->idt_equipe)
            ->where('user_id', $validated['userId'])
            ->first();

        if ($vinculo) {
            $vinculo->papel = $papel;
            $vinculo->deleted_at = null;
            $vinculo->save();
        } else {
            EquipeUsuario::create([
                'idt_equipe' => $this->equipe->idt_equipe,
                'user_id' => $validated['userId'],
                'papel' => $papel,
            ]);
        }

        $this->reset(['userId']);
    }

    public function alterarPapel(int $vinculoId, string $papel): void
    {
        $this->authorize('assignMembers', $this->equipe);

        $novoPapel = PapelEquipe::tryFrom($papel);
        if (! $novoPapel) {
            throw ValidationException::withMessages(['papel' => 'Selecione um papel valido.']);
        }

        $vinculo = EquipeUsuario::where('idt_equipe', $this->equipe->idt_equipe)
            ->findOrFail($vinculoId);

        $this->validarSexoDoUsuario($vinculo->user_id, $novoPapel);
        $this->validarLimiteCoordenador($novoPapel, $vinculo->idt_equipe_usuario);

        $vinculo->papel = $novoPapel;
        $vinculo->save();
    }

    public function remover(int $vinculoId): void
    {
        $this->authorize('assignMembers', $this->equipe);

        EquipeUsuario::where('idt_equipe', $this->equipe->idt_equipe)
            ->findOrFail($vinculoId)
            ->delete();
    }

    private function validarSexoDoUsuario(int $userId, PapelEquipe $papel): void
    {
        $sexo = $papel->requerSexo();

        if (! $sexo) {
            return;
        }

        $genero = User::with('pessoa')->findOrFail($userId)->pessoa?->tip_genero;

        if ($genero !== $sexo) {
            throw ValidationException::withMessages([
                'papel' => $papel === PapelEquipe::CoordEquipeH
                    ? 'Coordenador de Equipe H exige pessoa do sexo masculino.'
                    : 'Coordenador de Equipe M exige pessoa do sexo feminino.',
            ]);
        }
    }

    private function validarLimiteCoordenador(PapelEquipe $papel, ?int $ignorarVinculoId = null): void
    {
        if (! in_array($papel, [PapelEquipe::CoordEquipeH, PapelEquipe::CoordEquipeM], true)) {
            return;
        }

        $existe = EquipeUsuario::query()
            ->where('idt_equipe', $this->equipe->idt_equipe)
            ->where('papel', $papel->value)
            ->when($ignorarVinculoId, fn ($query) => $query->where('idt_equipe_usuario', '!=', $ignorarVinculoId))
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'papel' => $papel === PapelEquipe::CoordEquipeH
                    ? 'Esta equipe ja possui coordenador H.'
                    : 'Esta equipe ja possui coordenador M.',
            ]);
        }
    }
}; ?>

<section class="mx-auto w-full max-w-6xl space-y-6 p-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">Gerenciar membros</flux:heading>
            <flux:text>{{ $equipe->nom_equipe }}</flux:text>
        </div>

        <flux:button :href="route('equipes.index')" wire:navigate variant="ghost">
            Voltar
        </flux:button>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.8fr)]">
        <section class="space-y-4 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">Vinculos atuais</flux:heading>

            <div class="space-y-3">
                @forelse ($this->vinculosAtivos() as $vinculo)
                    <div class="grid gap-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-700 md:grid-cols-[1fr_220px_auto] md:items-center">
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $vinculo->usuario->pessoa?->nom_pessoa ?? $vinculo->usuario->name }}
                            </div>
                            <div class="text-sm text-zinc-500">{{ $vinculo->papel->label() }}</div>
                        </div>

                        <flux:select
                            :value="$vinculo->papel->value"
                            wire:change="alterarPapel({{ $vinculo->idt_equipe_usuario }}, $event.target.value)"
                        >
                            @foreach (PapelEquipe::opcoes() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:button wire:click="remover({{ $vinculo->idt_equipe_usuario }})" variant="danger">
                            Remover
                        </flux:button>
                    </div>
                @empty
                    <flux:text>Nenhum membro atribuido.</flux:text>
                @endforelse
            </div>
        </section>

        <section class="space-y-4 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">Atribuir pessoa</flux:heading>

            <flux:select wire:model.live="papel" label="Papel">
                @foreach (PapelEquipe::opcoes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="userId" label="Pessoa">
                <option value="">Selecione</option>
                @foreach ($this->usuariosElegiveis() as $usuario)
                    <option value="{{ $usuario->id }}">{{ $usuario->pessoa?->nom_pessoa ?? $usuario->name }}</option>
                @endforeach
            </flux:select>

            @error('papel')
                <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror

            @error('userId')
                <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror

            <flux:button wire:click="atribuir" variant="primary" class="w-full">
                Atribuir
            </flux:button>
        </section>
    </div>
</section>
