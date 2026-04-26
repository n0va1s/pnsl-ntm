<x-layouts.app>
    <section class="w-full max-w-5xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Nova venda') }}</flux:heading>
            <flux:subheading>{{ __('Registre o comprador, a equipe, os itens e se ficou pago ou em aberto.') }}</flux:subheading>
        </div>

        <form method="POST" action="{{ route('vendinha.vendas.store') }}" class="space-y-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select name="idt_pessoa" :label="__('Pessoa cadastrada')">
                    <option value="">{{ __('Selecionar pessoa') }}</option>
                    @foreach ($pessoas as $pessoa)
                        <option value="{{ $pessoa->idt_pessoa }}" @selected(old('idt_pessoa') == $pessoa->idt_pessoa)>{{ $pessoa->nom_pessoa }}</option>
                    @endforeach
                </flux:select>

                <flux:input name="nom_comprador" :label="__('Nome do comprador avulso')" value="{{ old('nom_comprador') }}" />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select name="idt_equipe" :label="__('Equipe da compra')">
                    <option value="">{{ __('Sem equipe') }}</option>
                    @foreach ($equipes as $equipe)
                        <option value="{{ $equipe->idt_equipe }}" @selected(old('idt_equipe') == $equipe->idt_equipe)>{{ $equipe->nom_equipe }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="status" :label="__('Pagamento')">
                    <option value="pago" @selected(old('status', 'pago') === 'pago')>{{ __('Pago agora') }}</option>
                    <option value="pendente" @selected(old('status') === 'pendente')>{{ __('Deixou a pagar') }}</option>
                </flux:select>
            </div>

            <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3">{{ __('Produto') }}</th>
                            <th class="px-4 py-3">{{ __('Quantidade') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @for ($i = 0; $i < 5; $i++)
                            <tr>
                                <td class="px-4 py-3">
                                    <select name="itens[{{ $i }}][produto_id]" class="w-full rounded border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                        <option value="">{{ __('Selecionar produto') }}</option>
                                        @foreach ($produtos as $produto)
                                            <option value="{{ $produto->id }}" @selected(old("itens.$i.produto_id") == $produto->id)>
                                                {{ $produto->nom_produto }} · R$ {{ number_format($produto->vlr_venda, 2, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input name="itens[{{ $i }}][quantidade]" type="number" min="1" value="{{ old("itens.$i.quantidade") }}" class="w-28 rounded border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            @error('itens')
                <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror

            <flux:textarea name="observacao" :label="__('Observação')" rows="3">{{ old('observacao') }}</flux:textarea>

            <div class="flex flex-wrap gap-3">
                <flux:button type="submit" variant="primary">{{ __('Registrar venda') }}</flux:button>
                <flux:button href="{{ route('vendinha.dashboard') }}" wire:navigate variant="ghost">{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </section>
</x-layouts.app>
