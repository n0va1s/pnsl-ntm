<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public $equipes;

    public function mount(): void
    {
        $this->authorize('viewAny', Equipe::class);
        $this->carregarEquipes();
    }

    public function carregarEquipes(): void
    {
        $this->equipes = Equipe::withTrashed()
            ->paraMovimento($this->idtMovimentoUsuario())
            ->orderBy('nom_equipe')
            ->get();
    }

    public function arquivar(int $idtEquipe): void
    {
        $equipe = Equipe::findOrFail($idtEquipe);

        $this->authorize('update', $equipe);

        $equipe->delete();
        $this->carregarEquipes();

        session()->flash('success', 'Equipe arquivada com sucesso.');
    }

    public function restaurar(int $idtEquipe): void
    {
        $equipe = Equipe::withTrashed()->findOrFail($idtEquipe);

        abort_unless($this->usuarioPodeRestaurar(), 403);

        $equipe->restore();
        $this->carregarEquipes();

        session()->flash('success', 'Equipe restaurada com sucesso.');
    }

    private function idtMovimentoUsuario(): ?int
    {
        return DB::table('equipe_usuario')
            ->join('equipes', 'equipes.idt_equipe', '=', 'equipe_usuario.idt_equipe')
            ->where('equipe_usuario.user_id', Auth::id())
            ->whereNull('equipe_usuario.deleted_at')
            ->value('equipes.idt_movimento');
    }

    private function usuarioPodeRestaurar(): bool
    {
        return DB::table('equipe_usuario')
            ->where('user_id', Auth::id())
            ->where('papel', PapelEquipe::CoordGeral->value)
            ->whereNull('deleted_at')
            ->exists();
    }
}; ?>

<section class="w-full space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Equipes') }}</flux:heading>
            <flux:subheading>{{ __('Gerencie as equipes do movimento atual.') }}</flux:subheading>
        </div>

        @can('create', App\Models\Equipe::class)
            <flux:button href="{{ route('equipes.create') }}" wire:navigate variant="primary">
                {{ __('Nova Equipe') }}
            </flux:button>
        @endcan
    </div>

    @if (session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                <tr>
                    <th class="px-4 py-3">{{ __('Nome') }}</th>
                    <th class="px-4 py-3">{{ __('Slug') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($equipes as $equipe)
                    <tr wire:key="equipe-{{ $equipe->idt_equipe }}">
                        <td class="px-4 py-3 align-top">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $equipe->nom_equipe }}</div>
                            @if ($equipe->des_descricao)
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $equipe->des_descricao }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top text-zinc-700 dark:text-zinc-300">{{ $equipe->des_slug }}</td>
                        <td class="px-4 py-3 align-top">
                            @if ($equipe->trashed())
                                <flux:badge color="zinc">{{ __('Arquivada') }}</flux:badge>
                            @elseif ($equipe->ind_ativa)
                                <flux:badge color="green">{{ __('Ativa') }}</flux:badge>
                            @else
                                <flux:badge color="amber">{{ __('Inativa') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="flex flex-wrap gap-2">
                                @can('update', $equipe)
                                    @if ($equipe->trashed())
                                        <flux:button
                                            wire:click="restaurar({{ $equipe->idt_equipe }})"
                                            size="sm"
                                            variant="filled"
                                        >
                                            {{ __('Restaurar') }}
                                        </flux:button>
                                    @else
                                        <flux:button
                                            href="{{ route('equipes.edit', $equipe->idt_equipe) }}"
                                            wire:navigate
                                            size="sm"
                                        >
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            wire:click="arquivar({{ $equipe->idt_equipe }})"
                                            wire:confirm="{{ __('Confirma o arquivamento desta equipe?') }}"
                                            variant="danger"
                                            size="sm"
                                        >
                                            {{ __('Arquivar') }}
                                        </flux:button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Nenhuma equipe encontrada.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
