<x-layouts.app>
    <section class="p-6 w-full max-w-[80vw] ml-auto">

        @if (session('success') || session('error'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3000)"
                x-show="show"
                class="fixed top-6 left-1/2 z-50 px-4 py-3 rounded-md text-white font-semibold shadow-lg flex items-center gap-2
                {{ session('success') ? 'bg-green-600' : 'bg-red-600' }}"
                role="alert">
                @if (session('success'))
                    <x-heroicon-o-check-circle class="w-6 h-6 text-white" />
                    <span>{{ session('success') }}</span>
                @else
                    <x-heroicon-o-x-circle class="w-6 h-6 text-white" />
                    <span>{{ session('error') }}</span>
                @endif
            </div>
        @endif

        <h1 id="titulo-pagina" class="text-2xl font-bold mb-4">Lista de Trabalhadores</h1>

        <div class="flex justify-end items-center mb-4">
            <a href="{{ route('trabalhadores.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none"
               aria-label="Cadastrar novo trabalhador">
                <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                Cadastrar novo
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 font-semibold dark:text-gray-800">Nome</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Apelido</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Gênero</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Telefone</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Equipe</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Evento</th>
                        <th class="p-3 font-semibold dark:text-gray-800">Toca violão?</th>
                        <th class="p-3 font-semibold text-center dark:text-gray-800 w-24">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trabalhadores as $trabalhador)
                        <tr class="border-t hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $trabalhador->pessoa->nom_pessoa }}</td>
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $trabalhador->pessoa->nom_apelido }}</td>
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $trabalhador->pessoa->tip_genero }}</td>
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $trabalhador->pessoa->tel_pessoa }}</td>
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $trabalhador->equipe->des_grupo }}</td>
                            <td class="p-3 text-gray-900 dark:text-gray-100">
                                {{ $trabalhador->evento ? $trabalhador->evento->des_evento : '-' }}
                            </td>
                            <td class="p-3 text-gray-900 dark:text-gray-100 text-center">
                                {{ $trabalhador->pessoa->ind_toca_violao ? 'Sim' : 'Não' }}
                            </td>
                            <td class="p-3 flex justify-end items-center gap-2">
                                <a href="{{ route('trabalhadores.show', $trabalhador->idt_pessoa) }}"
                                class="inline-flex items-center gap-1 text-blue-400 hover:text-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md"
                                aria-label="Ver detalhes de {{ $trabalhador->nom_pessoa }}">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                    <span class="sr-only sm:not-sr-only"></span>
                                </a>

                                <a href="{{ route('trabalhadores.edit', $trabalhador->idt_pessoa) }}"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md"
                                   aria-label="Editar {{ $trabalhador->pessoa->nom_completo }}">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </a>

                                <form action="{{ route('trabalhadores.destroy', $trabalhador->idt_pessoa) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este trabalhador?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 px-2 py-1 rounded-md cursor-pointer"
                                            aria-label="Excluir {{ $trabalhador->pessoa->nom_completo }}">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">Nenhum trabalhador cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
