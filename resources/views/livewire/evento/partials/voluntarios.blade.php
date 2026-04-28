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

    public function confirmarTrabalhador(int $idtPessoa, int $idtEquipe, int $idtVoluntario): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($idtPessoa, $idtEquipe, $idtVoluntario) {
            $trabalhador = \App\Models\Trabalhador::create([
                'idt_evento'  => $this->evento->idt_evento,
                'idt_pessoa'  => $idtPessoa,
                'idt_equipe'  => $idtEquipe,
            ]);

            \App\Models\Voluntario::where('idt_voluntario', $idtVoluntario)
                ->update(['idt_trabalhador' => $trabalhador->idt_trabalhador]);
        });

        $this->dispatch('notify', message: 'Voluntário confirmado como trabalhador!');
    }

    public function with(): array
    {
        return [
            'voluntarios' => \App\Models\Pessoa::query()
                ->whereHas('voluntarios', function ($query) {
                    $query->where('idt_evento', $this->evento->idt_evento)
                        ->whereNull('idt_trabalhador');
                })
                ->with(['voluntarios' => function ($query) {
                    $query->where('idt_evento', $this->evento->idt_evento)
                        ->whereNull('idt_trabalhador')
                        ->with('equipe');
                }])
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('nom_pessoa', 'like', '%' . $this->search . '%')
                            ->orWhere('nom_apelido', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('nom_pessoa')
                ->paginate(12),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="lg">Triagem de Voluntários</flux:heading>
            <flux:subheading>Analise os candidatos e aloque-os nas equipes correspondentes.</flux:subheading>
        </div>

        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Buscar por nome ou apelido..."
            class="w-full md:max-w-xs"
        />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse ($voluntarios as $pessoa)
            <div class="flex flex-col bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">

                <div class="p-5 flex flex-col items-center text-center border-b border-zinc-100 dark:border-zinc-700/50">
                    <flux:avatar
                        src="{{ $pessoa->foto?->url_foto ? asset('storage/'.$pessoa->foto->url_foto) : '' }}"
                        :initials="substr($pessoa->nom_pessoa, 0, 2)"
                        size="sm"
                    />
                    <h3 class="font-bold text-zinc-900 dark:text-white leading-tight">{{ $pessoa->nom_pessoa }}</h3>
                    @if($pessoa->nom_apelido)
                        <p class="text-sm text-zinc-500 italic">"{{ $pessoa->nom_apelido }}"</p>
                    @endif
                </div>

                <div class="p-4 space-y-3 flex-grow bg-zinc-50/50 dark:bg-zinc-900/20">
                    <div class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <flux:icon.phone variant="outline" class="size-4" />
                        <span>{{ $pessoa->tel_pessoa ?? 'Não informado' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <flux:icon.calendar variant="outline" class="size-4" />
                        <span>{{ $pessoa->dat_nascimento ? $pessoa->dat_nascimento->age . ' anos' : 'Idade não informada' }}</span>
                    </div>
                </div>

                <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
                    <p class="text-[10px] font-bold uppercase text-zinc-400 mb-2 tracking-wider">Interesses e Habilidades:</p>
                    <div class="space-y-3">
                        @foreach ($pessoa->voluntarios as $inscricao)
                            <div class="bg-white dark:bg-zinc-800 p-2 rounded border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">
                                        {{ $inscricao->equipe->des_grupo }}
                                    </span>
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="check"
                                        wire:click="confirmarTrabalhador({{ $pessoa->idt_pessoa }}, {{ $inscricao->idt_equipe }}, {{ $inscricao->idt_voluntario }})"
                                        tooltip="Confirmar nesta equipe"
                                    />
                                </div>
                                @if($inscricao->txt_habilidade)
                                    <p class="text-xs text-zinc-500 leading-relaxed italic border-t border-zinc-100 dark:border-zinc-700 mt-1 pt-1">
                                        "{{ $inscricao->txt_habilidade }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-100 dark:border-zinc-700 flex justify-between gap-2">
                    <flux:button size="sm" variant="ghost" class="w-full text-xs" icon="user">Ver Perfil</flux:button>
                    <flux:button size="sm" variant="ghost" color="red" class="w-full text-xs" icon="trash">Recusar</flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl">
                <flux:icon.users class="size-12 text-zinc-300 mb-4" />
                <p class="text-zinc-500">Nenhum voluntário pendente de triagem.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $voluntarios->links() }}
    </div>
</div>
