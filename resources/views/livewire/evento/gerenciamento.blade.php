<?php

use App\Models\Evento;
use Livewire\Volt\Component;

new class extends Component {
    public Evento $evento;
    public string $activeTab = 'resumo';

    public function mount(Evento $evento): void
    {
        $this->evento = $evento->loadCount([
            'fichas',
            'participantes',
            'trabalhadores',
            // Conta pessoas DISTINTAS, não linhas — 1 pessoa em 3 equipes = 1 voluntário
            'voluntarios as voluntarios_count' => fn($q) => $q->whereNull('idt_trabalhador')->distinct('idt_pessoa'),
        ]);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
}; ?>

<section class="w-full">
    {{-- Cabeçalho do Evento --}}
    <header class="mb-8">
        <flux:heading size="xl">{{ $evento->des_evento }}</flux:heading>
        <flux:heading size="lg">{{ $evento->movimento->des_sigla }}</flux:heading>
        <flux:subheading>Painel de Controle</flux:subheading>
    </header>

    <div class="flex flex-col md:flex-row gap-8">
        {{-- Sidebar de Navegação --}}
        <aside class="w-full md:w-64 space-y-1">
            <nav class="flex flex-col gap-1">
                <flux:navlist>
                    @foreach ([
                        'resumo'       => ['icon' => 'chart-bar',    'label' => 'Resumo'],
                        'fichas'       => ['icon' => 'document-text','label' => 'Fichas'],
                        'participantes'=> ['icon' => 'user-group',   'label' => 'Participantes'],
                        'voluntarios'  => ['icon' => 'hand-raised',  'label' => 'Voluntários'],
                        'trabalhadores'=> ['icon' => 'briefcase',    'label' => 'Trabalhadores'],
                        'presenca'     => ['icon' => 'finger-print', 'label' => 'Presença'],
                        'quadrante'    => ['icon' => 'table-cells',  'label' => 'Quadrante'],
                        'crachas'      => ['icon' => 'identification', 'label' => 'Crachás'],
                        'contas'       => ['icon' => 'banknotes',    'label' => 'Prestação de Contas'],
                    ] as $tab => $meta)
                        <flux:navlist.item
                            wire:click="setTab('{{ $tab }}')"
                            :variant="$activeTab === '{{ $tab }}' ? 'bullet' : 'ghost'"
                            icon="{{ $meta['icon'] }}"
                            class="cursor-pointer"
                        >
                            {{ $meta['label'] }}
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist>
            </nav>
        </aside>

        {{-- Conteúdo Dinâmico --}}
        <main class="flex-1 bg-white dark:bg-zinc-800 p-6 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative">

            {{-- Overlay "Processando..." exibido enquanto o Livewire carrega a aba --}}
            <div
                wire:loading
                wire:target="setTab"
                class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm"
            >
                <div class="flex items-center gap-3 text-zinc-500 dark:text-zinc-400">
                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="text-sm font-medium">Processando...</span>
                </div>
            </div>

            @if($activeTab === 'resumo')
                <livewire:evento.partials.resumo :evento="$evento" />
            @endif

            @if($activeTab === 'fichas')
                <livewire:evento.partials.fichas :evento="$evento" />
            @endif

            @if($activeTab === 'participantes')
                <livewire:evento.partials.participantes :evento="$evento" />
            @endif

            @if($activeTab === 'voluntarios')
                <livewire:evento.partials.voluntarios :evento="$evento" />
            @endif

            @if($activeTab === 'trabalhadores')
                <livewire:evento.partials.trabalhadores :evento="$evento" />
            @endif

            @if($activeTab === 'presenca')
                <livewire:evento.partials.presenca :evento="$evento" />
            @endif

            @if($activeTab === 'quadrante')
                <livewire:evento.partials.quadrante :evento="$evento" />
            @endif

            @if($activeTab === 'crachas')
                <livewire:evento.partials.crachas :evento="$evento" />
            @endif

            @if($activeTab === 'contas')
                <livewire:evento.partials.contas :evento="$evento" />
            @endif

        </main>
    </div>
</section>
