<x-layouts.app>
    <section class="w-full max-w-3xl space-y-6">
        <div>
            <flux:heading size="xl">{{ $produto->exists ? __('Editar produto') : __('Novo produto') }}</flux:heading>
            <flux:subheading>{{ __('Defina custo real, valor de venda e estoque.') }}</flux:subheading>
        </div>

        <form method="POST" action="{{ $produto->exists ? route('vendinha.produtos.update', $produto) : route('vendinha.produtos.store') }}" class="space-y-5">
            @csrf
            @if ($produto->exists)
                @method('PUT')
            @endif

            <flux:input name="nom_produto" :label="__('Produto')" value="{{ old('nom_produto', $produto->nom_produto) }}" required />
            <flux:error name="nom_produto" />

            <flux:textarea name="des_produto" :label="__('Descrição')" rows="3">{{ old('des_produto', $produto->des_produto) }}</flux:textarea>
            <flux:error name="des_produto" />

            <div class="grid gap-4 sm:grid-cols-3">
                <flux:input name="vlr_custo" :label="__('Custo real')" type="number" step="0.01" min="0" value="{{ old('vlr_custo', $produto->vlr_custo) }}" required />
                <flux:input name="vlr_venda" :label="__('Valor de venda')" type="number" step="0.01" min="0" value="{{ old('vlr_venda', $produto->vlr_venda) }}" required />
                <flux:input name="qtd_estoque" :label="__('Estoque')" type="number" min="0" value="{{ old('qtd_estoque', $produto->qtd_estoque) }}" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="ind_ativo" value="1" @checked(old('ind_ativo', $produto->ind_ativo))>
                <span>{{ __('Produto ativo para novas vendas') }}</span>
            </label>

            <div class="flex flex-wrap gap-3">
                <flux:button type="submit" variant="primary">{{ __('Salvar') }}</flux:button>
                <flux:button href="{{ route('vendinha.dashboard') }}" wire:navigate variant="ghost">{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </section>
</x-layouts.app>
