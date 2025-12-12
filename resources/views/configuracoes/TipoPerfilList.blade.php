<x-layouts.app :title="'Perfil Usuario'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Perfil</h1>
            <p class="text-gray-600 dark:text-gray-300 text-sm">
                Defina as permissões de acesso do usuário selecionando o perfil desejado para cada pessoa na lista
                abaixo.
            </p>
        </div>
        <div class="mb-6">
            <div>
                <x-session-alert />
            </div>
            <div>
                <x-botao-navegar href="{{ route('configuracoes.index') }}" aria-label="Voltar para Configurações">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                    Voltar
                </x-botao-navegar>
            </div>
        </div>
        <form method="POST" action="{{ route('role.change') }}">
            @csrf
            <table
                class="w-full text-left border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-700">
                    <tr>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Nome</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Apelido</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Email</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Telefone</th>
                        <th class="p-3 font-semibold text-gray-900 dark:text-gray-100">Perfil</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perfis as $pessoa)
                        <tr>
                            <td class="p-3 text-gray-700 dark:text-gray-300">{{ $pessoa->name ?? '-' }}</td>
                            <td class="p-3 text-gray-700 dark:text-gray-300">{{ $pessoa->nom_apelido ?? '-' }}</td>
                            <td class="p-3 text-gray-700 dark:text-gray-300">{{ $pessoa->email ?? '-' }}</td>
                            <td class="p-3 text-gray-700 dark:text-gray-300">{{ $pessoa->tel_pessoa ?? '-' }}</td>
                            <td>
                                <select name='role[{{ $pessoa->id ?? '' }}]'
                                    class="w-full px-2 py-1 rounded-md border border-gray-300 dark:border-zinc-600 dark:bg-zinc-800 text-gray-900 dark:text-gray-100">>
                                    @foreach (['user', 'admin', 'coord'] as $role)
                                        <option value="{{ $role }}" @selected(strtolower($pessoa->role ?? '') == $role)>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="flex gap-3 justify-end mt-4">
                <button type="submit" x-bind:disabled="bloqueado"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Salvar
                </button>
            </div>

            <div class="mt-6">
                {{ $perfis->links() }}
            </div>
    </section>
</x-layouts.app>
