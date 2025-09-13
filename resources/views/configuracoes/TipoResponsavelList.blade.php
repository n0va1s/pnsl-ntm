<x-layouts.app>
    <section class="p-6 w-full max-w-[80vw] ml-auto">

        <div>
            <x-session-alert />
        </div>
        <h1 id="titulo-pagina" class="text-2xl font-bold mb-4">Lista de Tipos de Responsável</h1>

        <div class="flex justify-end items-center mb-4">
            <a href="{{ route('responsavel.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 dark:hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Cadastrar novo tipo de responsável">
                <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                Novo
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table
                class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm"
                aria-describedby="descricao-tabela">
                <caption id="descricao-tabela" class="sr-only">
                    Tabela com tipos de responsáveis e ações disponíveis
                </caption>

                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="p-3 font-semibold  dark:text-gray-800">Responsável</th>
                        <th scope="col" class="p-3 font-semibold text-center dark:text-gray-800 w-24">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($responsavel as $responsavel)
                        <tr class="border-t hover:bg-gray-200 dark:hover:bg-gray-500">
                            <td class="p-3 text-gray-900 dark:text-gray-100">{{ $responsavel->des_responsavel }}</td>

                            <td class="p-3 flex justify-end items-center gap-2">
                                <a href="{{ route('responsavel.edit', $responsavel->idt_responsavel) }}"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 px-2 py-1 rounded-md"
                                    aria-label="Editar {{ $responsavel->des_responsavel }}">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    <span class="sr-only sm:not-sr-only">Editar</span>
                                </a>

                                <form action="{{ route('responsavel.destroy', $responsavel->idt_responsavel) }}"
                                    method="POST" class="inline"
                                    onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 px-2 py-1 rounded-md cursor-pointer"
                                        aria-label="Excluir {{ $responsavel->des_responsavel }}">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                        <span class="sr-only sm:not-sr-only">Excluir</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                Nenhum tipo de responsável cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>
</x-layouts.app>
