<x-layouts.app>
    <section class="min-h-screen p-4 md:p-8">
        <header class="max-w-7xl mx-auto mb-8">
            <div class="flex items-center gap-3 mb-1">
                <div class="bg-blue-500 p-2 rounded-lg shadow-lg shadow-blue-500/20">
                    <i data-lucide="sparkles" class="w-6 h-6 text-white"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Gerador de Crachá</h1>
            </div>
            <p class="text-slate-400 text-sm md:text-base ml-12">
                Carregue seu modelo, defina a área de preenchimento e gere crachás personalizados
            </p>
        </header>

        <main class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4 space-y-6">

                    {{-- ! Card Definir pontos --}}
                    <div class="card-bg p-6 rounded-2xl shadow-2xl dark:bg-zinc-900">
                        <div class="flex items-center gap-3 mb-6">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold">1</span>
                            <h2 class="font-semibold text-lg">Definir Pontos</h2>
                        </div>

                        <div class="space-y-3">
                            <div class="flex gap-4">
                                <div class="point-card flex items-center gap-4 p-4 rounded-xl border border-rose-500/30 bg-rose-500/5 cursor-pointer"
                                    data-point-key="topRight">
                                    <span
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-500 text-white text-sm font-bold">1</span>
                                    <div>
                                        <p class="text-sm font-medium dark:text-slate-200 ">Superior Direito</p>
                                    </div>
                                </div>

                                <div class="point-card flex items-center gap-4 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5 cursor-pointer"
                                    data-point-key="topLeft">
                                    <span
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold">2</span>
                                    <div>
                                        <p class="text-sm font-medium dark:text-slate-200">Superior Esquerdo</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="point-card flex items-center gap-4 p-4 rounded-xl border border-blue-500/30 bg-blue-500/5 cursor-pointer"
                                    data-point-key="bottomRight">
                                    <span
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm font-bold">3</span>
                                    <div>
                                        <p class="text-sm font-medium dark:text-slate-200">Inferior Direito</p>
                                    </div>
                                </div>

                                <div class="point-card flex items-center gap-4 p-4 rounded-xl border border-purple-500/30 bg-purple-500/5 cursor-pointer"
                                    data-point-key="bottomLeft">
                                    <span
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-500 text-white text-sm font-bold">4</span>
                                    <div>
                                        <p class="text-sm font-medium dark:text-slate-200">Inferior Esquerdo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ! Card Inserir Dados --}}
                    <div id="dataCard" class="card-bg p-6 rounded-2xl shadow-2xl dark:bg-zinc-900">
                        <div class="flex items-center gap-3 mb-6">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold">2</span>
                            <h2 class="font-semibold text-lg">Inserir Dados</h2>
                        </div>


                        <div class="relative w-64 bg-zinc-800 rounded-xl p-1 w-full flex mb-2 gap-2">


                            <button id="tabIndividualBtn" type="button"
                                class="flex-1 py-2 font-semibold bg-blue-500 rounded-xl text-white transition-all duration-500 ease-in-out cursor-pointer">
                                Individual
                            </button>
                            <button id="tabBulkBtn" type="button"
                                class="flex-1 peer/tab2 py-2 font-semibold rounded-xl border-transparent text-gray-500 transition-all duration-500 ease-in-out cursor-pointer">
                                Em massa
                            </button>
                        </div>


                        {{-- ? Card Individual --}}
                        <div id="individualTab" class="space-y-4">
                            <input id="nameInput" type="text" placeholder="Digite o nome completo"
                                class="w-full dark:bg-[#0a1120] border border-slate-700 rounded-xl px-4 py-3 text-sm">
                            <input id="teamInput" type="text" placeholder="Digite a equipe"
                                class="w-full dark:bg-[#0a1120] border border-slate-700 rounded-xl px-4 py-3 text-sm transition-opacity duration-200"
                                disabled>
                            <label class="flex items-center gap-2 text-sm text-slate-300">
                                <input id="includeTeamCheckbox" type="checkbox"
                                    class="h-5 w-5 rounded-full accent-blue-600 border-slate-500 focus:ring-blue-500 cursor-pointer"
                                    checked>
                                Incluir equipe no crachá
                            </label>

                            <button id="generateBadgeBtn" type="button"
                                class="w-full bg-blue-600 hover:bg-blue-500 disabled:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                Gerar Crachá
                            </button>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button id="exportPngBtn" type="button"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                    Exportar PNG
                                </button>
                                <button id="exportPdfBtn" type="button"
                                    class="w-full bg-red-600 hover:bg-red-900 disabled:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                    Exportar PDF
                                </button>
                            </div>
                            <button id="resetBtnIndividual" type="button"
                                class="w-full border border-slate-600 hover:bg-slate-300 dark:hover:bg-slate-800 disabled:hover:bg-transparent dark:disabled:hover:bg-transparent disabled:opacity-50 disabled:cursor-not-allowed text-zinc-900 dark:text-slate-200 font-medium rounded-xl px-4 py-3 cursor-pointer">
                                Recomeçar do Zero
                            </button>
                        </div>

                        {{-- ? Card em Massa --}}
                        <div id="bulkTab" class="space-y-4 hidden">
                            {{-- Form --}}
                            <div class=" mx-auto space-y-4">
                                <div class="mx-auto space-y-4">
                                    <input id="nameInputBulk" type="text" placeholder="Digite o nome completo"
                                        class="w-full dark:bg-[#0a1120] border border-slate-700 rounded-xl px-4 py-3 text-sm">
                                    <input id="teamInputBulk" type="text" placeholder="Digite a equipe"
                                        class="w-full dark:bg-[#0a1120] border border-slate-700 rounded-xl px-4 py-3 text-sm transition-opacity duration-200"
                                        disabled>
                                    <label class="flex items-center gap-2 text-sm text-slate-300">
                                        <input id="includeTeamCheckboxBulk" type="checkbox"
                                            class="h-5 w-5 rounded-full accent-blue-600 border-slate-500 focus:ring-blue-500 cursor-pointer"
                                            checked>
                                        Incluir equipe no crachá
                                    </label>
                                </div>

                                <button id="addToListBtn" type="button"
                                    class="w-full bg-blue-600 hover:bg-blue-500 disabled:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                    Adicionar
                                </button>
                                {{-- Lista --}}

                                <ul id="badgeList" class="mt-4 space-y-2 max-h-60 overflow-y-auto"></ul>

                            </div>

                            <button id="generateBadgeBtnBulk" type="button"
                                class="w-full bg-blue-600 hover:bg-blue-500 disabled:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                Gerar Crachás
                            </button>
                            <div class="flex flex-col sm:flex-row gap-3">

                                <button id="exportPngBtnBulk" type="button"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                    Exportar Zip
                                </button>
                                <button id="exportPdfBtnBulk" type="button"
                                    class="w-full bg-red-600 hover:bg-red-900 disabled:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                    Exportar PDF
                                </button>
                            </div>

                            <button id="resetBtnBulk" type="button"
                                class="w-full border border-slate-600 hover:bg-slate-300 dark:hover:bg-slate-800 disabled:hover:bg-transparent dark:disabled:hover:bg-transparent disabled:opacity-50 disabled:cursor-not-allowed text-zinc-900 dark:text-slate-200 font-medium rounded-xl px-4 py-3 cursor-pointer">
                                Recomeçar do Zero
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 flex flex-col h-full">
                    <div class="card-bg rounded-2xl flex flex-col h-full overflow-hidden shadow-2xl dark:bg-zinc-900">
                        <div class="p-6 border-b border-slate-800">
                            <h3 class="font-semibold text-lg">Modelo do Crachá</h3>
                        </div>

                        <div class="flex-1 p-8 flex items-center justify-center min-h-[400px]">
                            <div id="canvasContainer"
                                class="relative w-full max-w-2xl min-h-[420px] border-2 border-dashed border-slate-700 rounded-3xl overflow-hidden">
                                <label id="uploadLabel"
                                    class="absolute inset-0 flex flex-col items-center justify-center gap-6 cursor-pointer hover:bg-blue-500/5 transition-all group p-12 z-20">
                                    <input id="templateInput" type="file" class="hidden"
                                        accept="image/png, image/jpeg, image/webp">
                                    <p class="text-slate-300">Carregar modelo do crachá (PNG, JPG ou WEBP)</p>
                                </label>

                                <img id="templateImage" class="hidden w-full h-auto select-none" alt="Template">
                                <div id="areaLayer" class="absolute inset-0 pointer-events-none"></div>
                                <div id="pointsLayer" class="absolute inset-0 pointer-events-none"></div>
                                <canvas id="previewCanvas" class="hidden w-full h-auto"></canvas>

                                <div id="bulkNavigation"
                                    class="hidden absolute inset-y-0 left-0 right-0 z-30 flex items-center justify-between px-3 pointer-events-none">
                                    <button id="btnPrevBulk" type="button"
                                        class="pointer-events-auto rounded-full bg-slate-900/70 text-white px-3 py-2 hover:bg-slate-800 cursor-pointer">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="15 18 9 12 15 6"></polyline>
                                        </svg>
                                    </button>
                                    <span id="bilkPreviewCounter"
                                        class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-md bg-slate-900/70 text-white text-xs px-2 py-1">
                                        1 de 1
                                    </span>
                                    <button id="btnNextBulk" type="button"
                                        class="pointer-events-auto rounded-full bg-slate-900/70 text-white px-3 py-2 hover:bg-slate-800 cursor-pointer">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        @vite('resources/js/badge/app.js')
    </section>
</x-layouts.app>
