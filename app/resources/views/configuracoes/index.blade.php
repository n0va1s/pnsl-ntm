<x-layouts.app :title="'Configurações do Sistema'">
    <section class="p-6 w-full max-w-[80vw] ml-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configurações</h1>
            <p class="text-gray-700 dark:text-gray-400 mt-1">Gerencie os tipos e classificações do sistema</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1 -->
            <a href="{{ route('tiposmovimentos.index') }}" class="block bg-white dark:bg-zinc-600 rounded-xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="flex flex-col items-center justify-center h-full">
                    <x-heroicon-o-flag class="w-12 h-12 text-blue-600 mb-4" />
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tipos de Movimentos</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Gerencie os tipos de movimentos e siglas como ECC, VEM, Segue-Me, etc.</p>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="{{ route('tiporesponsavel.index') }}" class="block bg-white dark:bg-zinc-600 rounded-xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="flex flex-col items-center justify-center h-full">
                    <x-heroicon-o-user-group class="w-12 h-12 text-green-600 mb-4" />
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tipos de Responsáveis</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Configure os vínculos familiares como pai, mãe, padrinho e outros.</p>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="" class="block bg-white dark:bg-zinc-600 rounded-xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="flex flex-col items-center justify-center h-full">
                    <x-heroicon-o-cog-8-tooth class="w-12 h-12 text-purple-600 mb-4" />
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tipos de Equipes</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Organize as equipes como cozinha, oração, comunicação e outras.</p>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="" class="block bg-white dark:bg-zinc-600 rounded-xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="flex flex-col items-center justify-center h-full">
                    <x-heroicon-o-exclamation-circle class="w-12 h-12 text-red-600 mb-4" />
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tipos de Restrições</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Adicione restrições alimentares ou de saúde como alergias ou PNE.</p>
                </div>
            </a>

            <!-- Card 5 -->
            <a href="" class="block bg-white dark:bg-zinc-600 rounded-xl shadow hover:shadow-lg transition p-6 text-center">
                <div class="flex flex-col items-center justify-center h-full">
                    <x-heroicon-o-clipboard-document-check class="w-12 h-12 text-yellow-600 mb-4" />
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tipos de Situações</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Defina as situações da ficha: cadastrada, aprovada, rejeitada, etc.</p>
                </div>
            </a>
        </div>
    </section>
</x-layouts.app>
