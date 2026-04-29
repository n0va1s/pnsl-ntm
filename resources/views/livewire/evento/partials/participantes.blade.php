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

    public function atualizarTroca(int $participanteId, string $novaCor): void
    {
        $participante = \App\Models\Participante::find($participanteId);
        if ($participante) {
            $participante->update(['tip_cor_troca' => $novaCor]);
            $this->dispatch('notify', message: "Troca de {$participante->pessoa->nom_apelido} atualizada!");
        }
    }

    public function with(): array
    {
        return [
            'participantes' => \App\Models\Participante::where('idt_evento', $this->evento->idt_evento)
                ->with('pessoa.foto')
                ->when($this->search, function ($query) {
                    $query->whereHas('pessoa', function ($q) {
                        $q->where('nom_pessoa', 'like', '%' . $this->search . '%')
                            ->orWhere('nom_apelido', 'like', '%' . $this->search . '%');
                    });
                })
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-4">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="lg">Participantes Confirmados</flux:heading>
            <flux:subheading>Gerencie as cores das trocas e informações básicas.</flux:subheading>
        </div>

        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Nome ou apelido..."
            class="w-full md:max-w-xs"
        />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Pessoa</flux:table.column>
            <flux:table.column>Contato</flux:table.column>
            <flux:table.column>Cor da Troca</flux:table.column>
            <flux:table.column align="end">Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($participantes as $p)
                <flux:table.row :key="'participante-'.$p->idt_participante">
                    <flux:table.cell class="flex items-center gap-3">
                        <flux:avatar
                            src="{{ $p->pessoa->foto?->url_foto ? asset('storage/'.$p->pessoa->foto->url_foto) : '' }}"
                            :initials="substr($p->pessoa->nom_pessoa, 0, 2)"
                            size="sm"
                        />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $p->pessoa->nom_pessoa }}</div>
                            <div class="text-xs text-zinc-500">{{ $p->pessoa->nom_apelido }}</div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="text-sm">{{ $p->pessoa->tel_pessoa }}</span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:select
                            wire:change="atualizarTroca({{ $p->idt_participante }}, $event.target.value)"
                            variant="listbox"
                            size="sm"
                            class="w-32"
                        >
                            @foreach (['azul', 'amarela', 'verde', 'vermelha', 'laranja'] as $cor)
                                <option value="{{ $cor }}" @selected(strtolower($p->tip_cor_troca) === $cor)>
                                    {{ ucfirst($cor) }}
                                </option>
                            @endforeach
                        </flux:select>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="eye">Ver</flux:menu.item>
                                <flux:menu.item icon="pencil">Editar</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item variant="danger" icon="trash">Excluir</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-10 text-zinc-500">
                        Nenhum participante encontrado para este evento.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $participantes->links() }}
    </div>
</div>
