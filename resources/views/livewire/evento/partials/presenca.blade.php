<?php

/**
 * PRESENÇA
 * Campos necessários (migration):
 *   Schema::table('participante', fn($t) => $t->boolean('ind_presente')->default(false));
 *   Schema::table('trabalhador',  fn($t) => $t->boolean('ind_presente')->default(false));
 */

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Trabalhador;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public Evento $evento;
    public string $search     = '';
    public string $filtroTipo = 'todos';

    public function mount(Evento $evento): void
    {
        $this->evento = $evento;
    }

    public function togglePresenca(int $id, string $tipo): void
    {
        if ($tipo === 'participante') {
            $registro = Participante::findOrFail($id);
        } else {
            $registro = Trabalhador::findOrFail($id);
        }
        $registro->ind_presente = ! $registro->ind_presente;
        $registro->save();
        // Invalida o cache da propriedade computada
        unset($this->lista);
    }

    // Propriedade computada — não é serializada pelo Livewire,
    // resolve apenas na hora de renderizar a view.
    #[Computed]
    public function lista(): array
    {
        $search = $this->search;
        $items  = [];

        if (in_array($this->filtroTipo, ['todos', 'participantes'])) {
            $participantes = Participante::where('idt_evento', $this->evento->idt_evento)
                ->with('pessoa')
                ->when($search, fn($q) => $q->whereHas('pessoa', fn($q2) =>
                    $q2->where('nom_pessoa', 'like', "%{$search}%")
                       ->orWhere('nom_apelido', 'like', "%{$search}%")
                ))
                ->get();

            foreach ($participantes as $p) {
                $items[] = [
                    'id'           => $p->idt_participante,
                    'tipo'         => 'participante',
                    'nome'         => $p->pessoa->nom_pessoa ?? '',
                    'apelido'      => $p->pessoa->nom_apelido ?? '',
                    'telefone'     => $p->pessoa->tel_pessoa ?? '',
                    'nascimento'   => $p->pessoa->dat_nascimento,
                    'ind_presente' => (bool) $p->ind_presente,
                    'grupo'        => null,
                ];
            }
        }

        if (in_array($this->filtroTipo, ['todos', 'trabalhadores'])) {
            $trabalhadores = Trabalhador::where('idt_evento', $this->evento->idt_evento)
                ->whereHas('pessoa')
                ->with(['pessoa', 'equipe'])
                ->when($search, fn($q) => $q->whereHas('pessoa', fn($q2) =>
                    $q2->where('nom_pessoa', 'like', "%{$search}%")
                       ->orWhere('nom_apelido', 'like', "%{$search}%")
                ))
                ->get();

            foreach ($trabalhadores as $t) {
                $items[] = [
                    'id'           => $t->idt_trabalhador,
                    'tipo'         => 'trabalhador',
                    'nome'         => $t->pessoa->nom_pessoa ?? '',
                    'apelido'      => $t->pessoa->nom_apelido ?? '',
                    'telefone'     => $t->pessoa->tel_pessoa ?? '',
                    'nascimento'   => $t->pessoa->dat_nascimento,
                    'ind_presente' => (bool) $t->ind_presente,
                    'grupo'        => $t->equipe?->des_grupo,
                ];
            }
        }

        usort($items, fn($a, $b) => strcmp($a['nome'], $b['nome']));
        return $items;
    }
}; ?>

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="lg">Controle de Presença</flux:heading>
            @php
                $lista      = $this->lista;
                $total      = count($lista);
                $presentes  = count(array_filter($lista, fn($i) => $i['ind_presente']));
            @endphp
            <flux:subheading>{{ $presentes }} presente(s) de {{ $total }} pessoa(s)</flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <flux:select wire:model.live="filtroTipo" size="sm" class="w-full sm:w-44">
                <option value="todos">Todos</option>
                <option value="participantes">Participantes</option>
                <option value="trabalhadores">Trabalhadores</option>
            </flux:select>

            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Nome ou apelido..."
                class="w-full sm:w-64"
            />
        </div>
    </div>

    {{-- Barra de progresso --}}
    @if($total > 0)
        @php $pct = round(($presentes / $total) * 100) @endphp
        <div>
            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
            </div>
            <flux:text size="sm" class="text-zinc-400 mt-1">{{ $pct }}% de presença confirmada</flux:text>
        </div>
    @endif

    {{-- Tabela --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Pessoa</flux:table.column>
            <flux:table.column>Telefone</flux:table.column>
            <flux:table.column>Tipo</flux:table.column>
            <flux:table.column>Menor de Idade</flux:table.column>
            <flux:table.column>Presente</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->lista as $item)
                @php
                    $nasc  = $item['nascimento'] ? \Carbon\Carbon::parse($item['nascimento']) : null;
                    $menor = $nasc && $nasc->age < 18;
                @endphp
                <flux:table.row :key="$item['tipo'].'-'.$item['id']">

                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar :initials="mb_substr($item['nome'] ?? '?', 0, 2)" size="sm" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $item['nome'] }}</div>
                                @if($item['apelido'])
                                    <div class="text-xs text-zinc-500">{{ $item['apelido'] }}</div>
                                @endif
                                @if($item['grupo'])
                                    <div class="text-xs text-blue-500">{{ $item['grupo'] }}</div>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:text size="sm">{{ $item['telefone'] ?: '—' }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($item['tipo'] === 'trabalhador')
                            <flux:badge size="sm" color="purple">Trabalhador</flux:badge>
                        @else
                            <flux:badge size="sm" color="blue">Participante</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($menor)
                            <flux:badge size="sm" color="yellow">Sim</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Não</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:switch
                            :checked="$item['ind_presente']"
                            wire:click="togglePresenca({{ $item['id'] }}, '{{ $item['tipo'] }}')"
                            color="green"
                        />
                    </flux:table.cell>

                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-500">
                        Nenhuma pessoa encontrada.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

</div>
