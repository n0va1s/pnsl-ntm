<?php

use App\Models\Evento;
use Livewire\Volt\Component;

new class extends Component {
    public Evento $evento;
    public string $search = '';

    public function mount(Evento $evento): void
    {
        $this->evento = $evento;
    }

    public function atualizarAprovacao(int $fichaId): void
    {
        $ficha = \App\Models\Ficha::find($fichaId);
        if ($ficha) {
            $ficha->ind_aprovado = ! $ficha->ind_aprovado;
            $ficha->save();

            $status = $ficha->ind_aprovado ? 'aprovada' : 'pendente';
            $this->dispatch('notify', message: "Ficha de {$ficha->nom_apelido} foi {$status}.");
        }
    }

    public function with(): array
    {
        return [
            'fichas' => \App\Models\Ficha::where('idt_evento', $this->evento->idt_evento)
                ->when($this->search, function ($query) {
                    $query->where('nom_candidato', 'like', '%' . $this->search . '%')
                        ->orWhere('nom_apelido', 'like', '%' . $this->search . '%');
                })
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-4">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="lg">Fichas de Inscrição</flux:heading>
            <flux:subheading>Analise e aprove os candidatos para este evento.</flux:subheading>
        </div>

        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar ficha..."
            class="w-full md:max-w-xs" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Candidato</flux:table.column>
            <flux:table.column>Data Nasc</flux:table.column>
            <flux:table.column>Aprovado</flux:table.column>
            <flux:table.column align="end">Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($fichas as $ficha)
            <flux:table.row :key="'ficha-'.$ficha->idt_ficha">
                <flux:table.cell>
                    <div class="font-medium text-zinc-900 dark:text-white">{{ $ficha->nom_candidato }}</div>
                    <div class="text-xs text-zinc-500">{{ $ficha->nom_apelido }}</div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="text-sm">
                        {{ \Carbon\Carbon::parse($ficha->dat_nascimento)->format('d/m/Y') }}
                        <span class="text-zinc-400 text-xs ml-1">
                            ({{ \Carbon\Carbon::parse($ficha->dat_nascimento)->age }} anos)
                        </span>
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <flux:switch wire:click="atualizarAprovacao({{ $ficha->idt_ficha }})"
                        :checked="$ficha->ind_aprovado" color="green" />
                </flux:table.cell>

                <flux:table.cell align="end">
                    <div class="flex justify-end gap-2">
                        <flux:button variant="ghost" size="sm" icon="pencil-square"
                            href="{{ route('vem.edit', $ficha) }}" title="Editar Ficha" />

                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                            <flux:menu>
                                <flux:menu.item icon="eye">Ver Detalhes</flux:menu.item>
                                <flux:menu.item icon="printer">Imprimir</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item variant="danger" icon="trash"
                                    wire:click="confirmDelete({{ $ficha->idt_ficha }})">
                                    Excluir
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="4" class="text-center py-12">
                    <div class="flex flex-col items-center">
                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-zinc-300 mb-2" />
                        <flux:text>Nenhuma ficha encontrada para este critério.</flux:text>
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $fichas->links() }}
    </div>
</div>
