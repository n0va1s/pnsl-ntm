<x-layouts.app>
    <section class="w-full space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vendinha') }}</flux:heading>
                <flux:subheading>{{ __('Controle de produtos, vendas, lucro e contas em aberto.') }}</flux:subheading>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ route('vendinha.vendas.create') }}" wire:navigate variant="primary">
                    {{ __('Nova venda') }}
                </flux:button>
                <flux:button href="{{ route('vendinha.produtos.create') }}" wire:navigate>
                    {{ __('Novo produto') }}
                </flux:button>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success">{{ session('success') }}</flux:callout>
        @endif

        <div class="grid gap-4 md:grid-cols-5">
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-sm text-zinc-500">{{ __('Faturamento') }}</div>
                <div class="text-2xl font-semibold">R$ {{ number_format($totais['faturamento'], 2, ',', '.') }}</div>
            </div>
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-sm text-zinc-500">{{ __('Custo real') }}</div>
                <div class="text-2xl font-semibold">R$ {{ number_format($totais['custo'], 2, ',', '.') }}</div>
            </div>
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-sm text-zinc-500">{{ __('Lucro') }}</div>
                <div class="text-2xl font-semibold">R$ {{ number_format($totais['lucro'], 2, ',', '.') }}</div>
            </div>
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-sm text-zinc-500">{{ __('Em aberto') }}</div>
                <div class="text-2xl font-semibold">R$ {{ number_format($totais['aberto'], 2, ',', '.') }}</div>
            </div>
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-sm text-zinc-500">{{ __('Itens vendidos') }}</div>
                <div class="text-2xl font-semibold">{{ $totais['quantidade'] }}</div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
            <div class="space-y-6">
                <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Vendas recentes') }}</flux:heading>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                            <tr>
                                <th class="px-4 py-3">{{ __('Comprador') }}</th>
                                <th class="px-4 py-3">{{ __('Equipe') }}</th>
                                <th class="px-4 py-3">{{ __('Itens') }}</th>
                                <th class="px-4 py-3">{{ __('Total') }}</th>
                                <th class="px-4 py-3">{{ __('Lucro') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($vendas as $venda)
                                <tr>
                                    <td class="px-4 py-3">{{ $venda->pessoa?->nom_pessoa ?? $venda->nom_comprador }}</td>
                                    <td class="px-4 py-3">{{ $venda->equipe?->nom_equipe ?? 'Sem equipe' }}</td>
                                    <td class="px-4 py-3">
                                        @foreach ($venda->itens as $item)
                                            <div>{{ $item->qtd_item }}x {{ $item->nom_produto }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3">R$ {{ number_format($venda->vlr_total, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">R$ {{ number_format($venda->vlr_lucro_total, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($venda->estaPendente())
                                            <form method="POST" action="{{ route('vendinha.vendas.pagar', $venda) }}">
                                                @csrf
                                                <flux:button size="sm" type="submit">{{ __('Baixar') }}</flux:button>
                                            </form>
                                        @else
                                            <flux:badge color="green">{{ __('Pago') }}</flux:badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-zinc-500">{{ __('Nenhuma venda registrada.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Produtos') }}</flux:heading>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                            <tr>
                                <th class="px-4 py-3">{{ __('Produto') }}</th>
                                <th class="px-4 py-3">{{ __('Custo') }}</th>
                                <th class="px-4 py-3">{{ __('Venda') }}</th>
                                <th class="px-4 py-3">{{ __('Estoque') }}</th>
                                <th class="px-4 py-3">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($produtos as $produto)
                                <tr>
                                    <td class="px-4 py-3">{{ $produto->nom_produto }}</td>
                                    <td class="px-4 py-3">R$ {{ number_format($produto->vlr_custo, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">R$ {{ number_format($produto->vlr_venda, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ $produto->qtd_estoque ?? 'Livre' }}</td>
                                    <td class="px-4 py-3">
                                        <flux:button size="sm" href="{{ route('vendinha.produtos.edit', $produto) }}" wire:navigate>
                                            {{ __('Editar') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('Nenhum produto cadastrado.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Contas em aberto') }}</flux:heading>
                    <div class="mt-4 space-y-3">
                        @forelse ($pendentes as $pendente)
                            <div class="border-b border-zinc-200 pb-3 last:border-0 dark:border-zinc-700">
                                <div class="font-medium">{{ $pendente->pessoa?->nom_pessoa ?? $pendente->nom_comprador }}</div>
                                <div class="text-sm text-zinc-500">
                                    {{ $pendente->equipe?->nom_equipe ?? 'Sem equipe' }} · R$ {{ number_format($pendente->vlr_total, 2, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-zinc-500">{{ __('Nenhuma conta em aberto.') }}</div>
                        @endforelse
                    </div>
                </div>

                <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Vendas por equipe') }}</flux:heading>
                    <div class="mt-4 space-y-3">
                        @forelse ($porEquipe as $linha)
                            <div class="flex justify-between gap-3 text-sm">
                                <span>{{ $linha->nom_equipe }}</span>
                                <span class="font-medium">R$ {{ number_format($linha->total_vendido, 2, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="text-sm text-zinc-500">{{ __('Sem vendas vinculadas a equipes.') }}</div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
