<?php

use App\Models\Evento;
use Livewire\Volt\Component;

new class extends Component {
    public Evento $evento;

    public function mount(Evento $evento): void
    {
        $this->evento = $evento;
    }

    public function saveFinanceiro(): void
    {
        // TODO: implementar persistência
        $this->dispatch('notify', message: 'Prestação de contas salva!');
    }
}; ?>

<form wire:submit="saveFinanceiro" class="space-y-6">
    <div>
        <flux:heading size="lg">Financeiro e Prestação de Contas</flux:heading>
        <flux:subheading>Registre receita total, despesa total e observações relevantes para outras equipes no futuro.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:input type="number" label="Receita (R$)" placeholder="0,00" />
        <flux:input type="number" label="Despesa (R$)" placeholder="0,00" />
    </div>

    <flux:textarea label="Relatório sobre o evento" placeholder="X pessoas participaram
    x kg de carne comprados - x kg de carne sobraram
    x livros em consignação - tema XXX não agradou" />

    <flux:button variant="primary" type="submit">Salvar Prestação</flux:button>
</form>
