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
                            <button id="exportPngBtn" type="button"
                                class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl px-4 py-3 cursor-pointer">
                                Exportar PNG
                            </button>

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
                                    Exportar PNG.zip
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

        <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                        if (!document.querySelector('link[data-icons="bootstrap-icons"]')) {
                            const iconLink = document.createElement('link');
                            iconLink.rel = 'stylesheet';
                            iconLink.href =
                                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';
                            iconLink.setAttribute('data-icons', 'bootstrap-icons');
                            document.head.appendChild(iconLink);
                        }

                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }


                        const state = {
                            imageObj: null,
                            imageNatural: {
                                w: 0,
                                h: 0
                            },
                            activePoint: null,
                            points: {
                                topRight: null,
                                topLeft: null,
                                bottomRight: null,
                                bottomLeft: null,
                            },
                            name: '',
                            team: '',
                            previewMode: false,
                        };


                        const templateInput = document.getElementById('templateInput'); // Input file do modelo.
                        const templateImage = document.getElementById('templateImage'); // <img> para exibir o modelo.
                        const uploadLabel = document.getElementById(
                            'uploadLabel');
                        const canvasContainer = document.getElementById(
                            'canvasContainer');
                        const previewCanvas = document.getElementById('previewCanvas'); // Canvas final com nome/equipe.
                        const areaLayer = document.getElementById(
                            'areaLayer');
                        const pointsLayer = document.getElementById('pointsLayer');
                        const nameInput = document.getElementById('nameInput');
                        const teamInput = document.getElementById('teamInput');
                        const nameInputBulk = document.getElementById('nameInputBulk');
                        const teamInputBulk = document.getElementById('teamInputBulk');

                        const generateBadgeBtn = document.getElementById('generateBadgeBtn');
                        const generateBadgeBtnBulk = document.getElementById(
                            'generateBadgeBtnBulk');
                        const pointCards = document.querySelectorAll(
                            '.point-card');
                        const exportPngBtn = document.getElementById('exportPngBtn');
                        const exportPngBtnBulk = document.getElementById('exportPngBtnBulk');
                        const resetIndividualBtn = document.getElementById('resetBtnIndividual');
                        const resetBulkBtn = document.getElementById('resetBtnBulk');
                        const includeTeamCheckbox = document.getElementById(
                            'includeTeamCheckbox');
                        const includeTeamCheckboxBulk = document.getElementById(
                            'includeTeamCheckboxBulk');
                        const dataCard = document.getElementById('dataCard');
                        const individualTab = document.getElementById('individualTab');
                        const bulkTab = document.getElementById('bulkTab');
                        const btnIndividualTab = document.getElementById('tabIndividualBtn');
                        const btnBulkTab = document.getElementById('tabBulkBtn');
                        const addToListBtn = document.getElementById('addToListBtn');
                        const badgeList = document.getElementById('badgeList');
                        const btnPrevBulk = document.getElementById('btnPrevBulk');
                        const btnNextBulk = document.getElementById('btnNextBulk');
                        const bilkPreviewCounter = document.getElementById(
                            'bilkPreviewCounter');
                        const bulkNavigation = document.getElementById('bulkNavigation');
                        const exportPdfBtnBulk = document.getElementById('exportPdfBtnBulk');



                        let items = [];
                        let editingIndex = null;
                        let currentBulkPreviewIndex = 0;

                        // Rótulo visual (número) de cada ponto.
                        const pointLabels = {
                            topRight: '1',
                            topLeft: '2',
                            bottomRight: '3',
                            bottomLeft: '4'
                        };

                        //  Retorna true quando os 4 pontos já foram definidos.
                        // ======================================================
                        // ======================================================
                        // ======================================================
                        // ======================================================
                        // ======================================================
                        function allPointsSet() {
                            // return Object.values(state.points).every(Boolean);
                            return true;
                        }

                        // Desenha o polí­gono tracejado da área de impressão quando os 4 pontos existem.
                        function renderAreaOverlay() {
                            areaLayer.innerHTML = '';
                            if (!state.imageObj || !allPointsSet() || state.previewMode) return;

                            const p = state.points;
                            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            svg.setAttribute('class', 'absolute inset-0 w-full h-full');
                            svg.setAttribute('viewBox', `0 0 ${state.imageNatural.w} ${state.imageNatural.h}`);
                            svg.setAttribute('preserveAspectRatio', 'none');

                            const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                            polygon.setAttribute('points',
                                `${p.topLeft.x},${p.topLeft.y} ${p.topRight.x},${p.topRight.y} ${p.bottomRight.x},${p.bottomRight.y} ${p.bottomLeft.x},${p.bottomLeft.y}`
                            );
                            polygon.setAttribute('fill', 'rgba(14, 165, 233, 0.15)');
                            polygon.setAttribute('stroke', 'hsl(199, 89%, 48%)');
                            polygon.setAttribute('stroke-width', '2');
                            polygon.setAttribute('stroke-dasharray', '8 4');

                            svg.appendChild(polygon);
                            areaLayer.appendChild(svg);

                            console.log('[area] polÃƒÂ­gono desenhado');
                        }

                        // Desenha as bolinhas dos pontos já marcados e atualiza o overlay da área.
                        function renderPoints() {
                            pointsLayer.innerHTML = '';
                            if (!state.imageObj || state.previewMode) return;

                            Object.keys(state.points).forEach((key) => {
                                const p = state.points[key];
                                if (!p) return;

                                const leftPct = (p.x / state.imageNatural.w) * 100;
                                const topPct = (p.y / state.imageNatural.h) * 100;

                                const pointClasses = {
                                    topRight: 'bg-rose-500/70',
                                    topLeft: 'bg-emerald-500/70',
                                    bottomRight: 'bg-blue-500/70',
                                    bottomLeft: 'bg-purple-500/70',
                                };

                                const el = document.createElement('div');
                                const colorClass = pointClasses[key] || 'bg-blue-600';

                                el.className = [
                                    'absolute -translate-x-1/2 -translate-y-1/2 w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center',
                                    'border-2 border-black/70',
                                    colorClass,
                                ].join(' ');

                                el.style.left = `${leftPct}%`;
                                el.style.top = `${topPct}%`;
                                el.textContent = pointLabels[key];
                                pointsLayer.appendChild(el);

                            });

                            renderAreaOverlay();
                            console.log('[pontos] renderPoints', state.points);
                        }

                        function switchTab(tab) {

                            if (tab === 'individualTab') {
                                individualTab.classList.remove('hidden');
                                bulkTab.classList.add('hidden');

                                btnIndividualTab.classList.add('bg-blue-500', 'text-white');
                                btnIndividualTab.classList.remove('text-gray-500', 'border-transparent');
                                btnBulkTab.classList.remove('bg-blue-500', 'text-white');
                                btnBulkTab.classList.add('text-gray-500', 'border-transparent');

                            } else {
                                individualTab.classList.add('hidden');
                                bulkTab.classList.remove('hidden');

                                btnBulkTab.classList.add('bg-blue-500', 'text-white');
                                btnBulkTab.classList.remove('text-gray-500', 'border-transparent');
                                btnIndividualTab.classList.remove('bg-blue-500', 'text-white');
                                btnIndividualTab.classList.add('text-gray-500', 'border-transparent');
                            }

                        }

                        function renderList() {
                            badgeList.innerHTML = '';

                            items.forEach((item, index) => {
                                const li = document.createElement("li");
                                li.className =
                                    "flex items-center justify-between bg-zinc-800 rounded-xl px-4 py-2 text-sm";

                                li.innerHTML = `
                            <div>
                                <p class="text-white font-semibold">${item.name}</p>
                                <p class="text-gray-400 text-sm">${item.team}</p>
                            </div>
                            <div class="flex gap-2">
                                <button data-edit="${index}"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 cursor-pointer">
                                <i class="bi bi-pencil-square"></i>
                                </button>
                                <button data-delete="${index}"
                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-800 cursor-pointer">
                                <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        `;

                                badgeList.appendChild(li);
                            });
                        };

                        addToListBtn.addEventListener("click", () => {
                            const name = nameInputBulk.value.trim();
                            const team = teamInputBulk.value.trim();
                            const includeTeamBulk = includeTeamCheckboxBulk?.checked;

                            if (!name || (includeTeamBulk && !team)) return;

                            if (editingIndex !== null) {
                                items[editingIndex] = {
                                    name,
                                    team
                                };
                                editingIndex = null;
                                addToListBtn.textContent = "Adicionar";
                            } else {
                                items.push({
                                    name,
                                    team
                                });
                            }


                            nameInputBulk.value = "";
                            teamInputBulk.value = "";
                            renderList();
                        });

                        function deleteItem(index) {
                            items.splice(index, 1);
                            renderList();
                        }

                        function editItem(index) {
                            nameInputBulk.value = items[index].name;
                            teamInputBulk.value = items[index].team;
                            editingIndex = index;
                            addToListBtn.textContent = "Salvar";
                        }

                        badgeList.addEventListener('click', function(e) {
                            const editIndex = e.target.getAttribute('data-edit');
                            const deleteIndex = e.target.getAttribute('data-delete');

                            if (editIndex !== null) {
                                editItem(Number(editIndex));
                            }
                            if (deleteIndex !== null) {
                                deleteItem(Number(deleteIndex));
                            }
                        });

                        templateInput?.addEventListener('change', function(e) {
                            const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
                            console.log('[upload] change', file ? {
                                name: file.name,
                                type: file.type,
                                size: file.size
                            } : 'sem arquivo');
                            if (!file) return;

                            const reader = new FileReader();
                            reader.onload = function(ev) {
                                const dataUrl = ev.target && ev.target.result ? ev.target.result : null;
                                console.log('[upload] FileReader.onload', {
                                    ok: !!dataUrl
                                });
                                if (!dataUrl || typeof dataUrl !== 'string') return;

                                const img =
                                    new Image();
                                img.onload = function() {
                                    state.imageObj = img;
                                    state.imageNatural = {
                                        w: img.width,
                                        h: img.height
                                    };
                                    state.previewMode = false;

                                    templateImage.src = dataUrl;
                                    templateImage.classList.remove('hidden');
                                    previewCanvas.classList.add('hidden');
                                    uploadLabel.classList.add('hidden');

                                    renderPoints();
                                    syncDataCardState();
                                    console.log('[upload] imagem carregada', state.imageNatural);
                                };
                                img.onerror = function(err) {
                                    console.error('[upload] erro image', err);
                                };
                                img.src = dataUrl;
                            };

                            reader.onerror = function(err) {
                                console.error('[upload] erro reader', err);
                            };
                            reader.readAsDataURL(file);
                        });

                        const activeRing = {
                            topRight: 'ring-rose-500/50',
                            topLeft: 'ring-emerald-500/50',
                            bottomRight: 'ring-blue-500/50',
                            bottomLeft: 'ring-purple-500/50',
                        };

                        pointCards.forEach((card) => {
                            card.addEventListener('click', function() {
                                state.activePoint = card.getAttribute('data-point-key');

                                pointCards.forEach((c) => c.classList.remove(
                                    'ring-2', 'ring-rose-500', 'ring-emerald-500', 'ring-blue-500',
                                    'ring-purple-500'
                                ));

                                card.classList.add('ring-2', activeRing[state.activePoint] || 'ring-blue-500');
                                console.log('[pontos] ativo', state.activePoint);
                            });
                        });


                        canvasContainer?.addEventListener('click', function(e) {
                            if (!state.activePoint || !state.imageObj || state.previewMode) {
                                console.log('[pontos] clique ignorado');
                                return;
                            }

                            const rect = canvasContainer.getBoundingClientRect();
                            const x = Math.round(((e.clientX - rect.left) / rect.width) * state.imageNatural.w);
                            const y = Math.round(((e.clientY - rect.top) / rect.height) * state.imageNatural.h);
                            state.points[state.activePoint] = {
                                x,
                                y
                            };

                            console.log('[pontos] marcado', {
                                key: state.activePoint,
                                x,
                                y
                            });

                            state.activePoint = null;
                            pointCards.forEach((c) => c.classList.remove('ring-2', 'ring-blue-500'));
                            renderPoints();
                            syncDataCardState();
                        });

                        nameInput?.addEventListener('input', function(e) {
                            state.name = e.target.value;
                            console.log('[input] name', state.name);
                        });

                        teamInput?.addEventListener('input', function(e) {
                            state.team = e.target.value;
                            console.log('[input] team', state.team);
                        });

                        function syncTeamInputState() {
                            const includeTeam = includeTeamCheckbox?.checked;
                            teamInput.disabled = !includeTeam;

                            if (!includeTeam) {
                                teamInput.value = '';
                                state.team = '';
                                teamInput.classList.add('opacity-50', 'cursor-not-allowed');
                                teamInput.classList.remove('opacity-100', 'cursor-text');
                            } else {
                                teamInput.classList.remove('opacity-50', 'cursor-not-allowed');
                                teamInput.classList.add('opacity-100', 'cursor-text');
                            }
                        }

                        function syncDataCardState() {
                            const enabled = allPointsSet();
                            const controls = [
                                nameInput,
                                teamInput,
                                includeTeamCheckbox,
                                generateBadgeBtn,
                                exportPngBtn,
                                resetIndividualBtn,
                            ];

                            controls.forEach((el) => {
                                if (!el) return;
                                el.disabled = !enabled;
                            });

                            [btnIndividualTab, btnBulkTab].forEach((tabBtn) => {
                                if (!tabBtn) return;
                                tabBtn.disabled = !enabled;
                                tabBtn.classList.toggle('opacity-50', !enabled);
                                tabBtn.classList.toggle('cursor-not-allowed', !enabled);
                            });

                            if (!enabled) {
                                dataCard.classList.add('opacity-50', 'cursor-not-allowed', );
                                dataCard.classList.remove('opacity-100', 'cursor-text');
                            } else {
                                dataCard.classList.remove('opacity-50', 'cursor-not-allowed', );
                                dataCard.classList.add('opacity-100', 'cursor-text');
                                syncTeamInputState();
                            }
                        }

                        includeTeamCheckbox?.addEventListener('change', function() {
                            syncTeamInputState();
                        });

                        function syncTeamInputStateBulk() {
                            const includeTeamBulk = includeTeamCheckboxBulk?.checked;
                            teamInputBulk.disabled = !includeTeamBulk;

                            if (!includeTeamBulk) {
                                teamInputBulk.value = '';
                                teamInputBulk.classList.add('opacity-50', 'cursor-not-allowed');
                                teamInputBulk.classList.remove('opacity-100', 'cursor-text');
                            } else {
                                teamInputBulk.classList.remove('opacity-50', 'cursor-not-allowed');
                                teamInputBulk.classList.add('opacity-100', 'cursor-text');
                            }
                        }

                        includeTeamCheckboxBulk?.addEventListener('change', function() {
                            syncTeamInputStateBulk();
                        });

                        let customNameFontSize = null;

                        function findOptimalFontSize(ctx, text, maxWidth, min = 12, max = 220) {
                            if (!text || !text.trim()) return min;

                            let low = min;
                            let high = max;
                            let best = min;

                            while (low <= high) {
                                const mid = Math.floor((low + high) / 2);
                                ctx.font = `bold ${mid}px Inter, sans-serif`;
                                const w = ctx.measureText(text).width;

                                if (w <= maxWidth) {
                                    best = mid;
                                    low = mid + 1;
                                } else {
                                    high = mid - 1;
                                }
                            }

                            return best;
                        }

                        function buildBadgeCanvas(nameValue, teamValue, includeTeamValue) {
                            if (!state.imageObj || !allPointsSet()) {
                                console.log('[gerar] faltando imagem ou 4 pontos');
                                return null;
                            }

                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            const w = state.imageNatural.w;
                            const h = state.imageNatural.h;

                            canvas.width = w;
                            canvas.height = h;
                            ctx.clearRect(0, 0, w, h);
                            ctx.drawImage(state.imageObj, 0, 0);

                            const {
                                topLeft,
                                topRight,
                                bottomLeft,
                                bottomRight
                            } = state.points;
                            if (!topLeft || !topRight || !bottomLeft || !bottomRight) {
                                console.log('[gerar] pontos ainda não definidos');
                                return null;
                            }
                            const minX = Math.min(topLeft.x, topRight.x, bottomLeft.x, bottomRight.x);
                            const maxX = Math.max(topLeft.x, topRight.x, bottomLeft.x, bottomRight.x);
                            const minY = Math.min(topLeft.y, topRight.y, bottomLeft.y, bottomRight.y);
                            const maxY = Math.max(topLeft.y, topRight.y, bottomLeft.y, bottomRight.y);

                            const areaWidth = maxX - minX;
                            const areaHeight = maxY - minY;
                            const centerX = minX + areaWidth / 2;
                            const centerY = minY + areaHeight / 2;
                            const padding = areaWidth * 0.05;
                            const maxTextWidth = areaWidth - (padding * 2);

                            const name = nameValue || 'Nome';
                            const team = teamValue || 'Equipe';
                            const includeTeam = !!includeTeamValue;

                            const maxNameFontSize = includeTeam ?
                                Math.floor(areaHeight * 0.42) :
                                Math.floor(areaHeight * 0.70);
                            const autoNameFontSize = findOptimalFontSize(
                                ctx,
                                name,
                                maxTextWidth,
                                14,
                                maxNameFontSize
                            );
                            const nameFontSize = customNameFontSize ?? autoNameFontSize;


                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = '#000';
                            ctx.font = `bold ${nameFontSize}px Inter, sans-serif`;

                            // Quebra de linhas
                            if (!includeTeam) {
                                const words = name.trim().split(/\s+/);
                                let line1 = name;
                                let line2 = '';

                                if (words.length > 1) {
                                    let bestLine1 = name;
                                    let bestLine2 = '';

                                    for (let i = 1; i < words.length; i++) {
                                        const test1 = words.slice(0, i).join(' ');
                                        const text2 = words.slice(i).join(' ');

                                        const w1 = ctx.measureText(test1).width;
                                        const w2 = ctx.measureText(text2).width;

                                        if (w1 <= maxTextWidth && w2 <= maxTextWidth) {
                                            bestLine1 = test1;
                                            bestLine2 = text2;
                                        };
                                    };

                                    line1 = bestLine1;
                                    line2 = bestLine2;
                                };

                                if (line2) {
                                    const lineHeight = nameFontSize * 1.1;
                                    const y1 = centerY - (lineHeight / 2);
                                    const y2 = centerY + (lineHeight / 2);
                                    ctx.fillText(line1, centerX, y1);
                                    ctx.fillText(line2, centerX, y2);
                                } else {

                                    ctx.fillText(name, centerX, centerY);
                                }




                            } else {
                                const teamFontSize = Math.max(10, nameFontSize - 10);
                                const totalTextHeight = nameFontSize + teamFontSize + 8;
                                const nameY = centerY - totalTextHeight / 2 + nameFontSize / 2;
                                const teamY = nameY + nameFontSize / 2 + 8 + teamFontSize / 2;

                                ctx.fillText(name, centerX, nameY);

                                ctx.fillStyle = 'rgba(0,0,0,.85)';
                                ctx.font = `${teamFontSize}px Inter, sans-serif`;
                                ctx.fillText(team, centerX, teamY);
                            }

                            return canvas;
                        }



                        function drawBadge() {
                            const canvas = buildBadgeCanvas(
                                state.name || 'Nome',
                                state.team || 'Equipe',
                                includeTeamCheckbox?.checked
                            )
                            if (!canvas) return;


                            previewCanvas.width = canvas.width;
                            previewCanvas.height = canvas.height;
                            const previewCtx = previewCanvas.getContext('2d');
                            previewCtx.clearRect(0, 0, canvas.width, canvas.height);
                            previewCtx.drawImage(canvas, 0, 0);



                            state.previewMode = true;
                            templateImage.classList.add('hidden');
                            previewCanvas.classList.remove('hidden');
                            pointsLayer.innerHTML = '';
                            areaLayer.innerHTML = '';

                            console.log('[gerar] crachá gerado com sucesso');
                        }

                        function generateBulkPngs() {
                            if (!items.length) return;

                            const includeTeamBulk = includeTeamCheckboxBulk?.checked;

                            items.forEach((item, index) => {
                                const canvas = buildBadgeCanvas(item.name, item.team || '', includeTeamBulk);
                                if (!canvas) return;

                                const link = document.createElement('a');
                                const safe = (item.name || `item-${index+1}`).trim().toLowerCase().replace(/\s+/g, '-');
                                link.download = `cracha-${String(index + 1).padStart(3, '0')}-${safe}.png`;
                                link.href = canvas.toDataURL('image/png');

                                setTimeout(() => link.click(), index * 120);


                            })
                        }

                        function drawBulkPreview(index) {
                            if (!items.length) return;
                            if (!state.imageObj || !allPointsSet()) return;

                            const item = items[index];
                            if (!item) return;

                            const canvas = buildBadgeCanvas(
                                item.name,
                                item.team || '',
                                includeTeamCheckboxBulk?.checked
                            );

                            if (!canvas) return;

                            previewCanvas.width = canvas.width;
                            previewCanvas.height = canvas.height;

                            const ctx = previewCanvas.getContext('2d');
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            ctx.drawImage(canvas, 0, 0);

                            bilkPreviewCounter.textContent = `${index + 1} de ${items.length}`;
                            bulkNavigation.classList.remove('hidden');

                            state.previewMode = true;
                            templateImage.classList.add('hidden');
                            previewCanvas.classList.remove('hidden');
                            canvasContainer.classList.remove('border-0')
                            canvasContainer.classList.add('border-transparent')
                            pointsLayer.innerHTML = '';
                            areaLayer.innerHTML = '';
                            state.previewMode = true;


                        }

                        function goToNextBulkPreview() {
                            if (!items.length) return;

                            currentBulkPreviewIndex = (currentBulkPreviewIndex + 1) % items.length;

                            drawBulkPreview(currentBulkPreviewIndex);
                        }

                        function goToPrevBulkPreview() {
                            if (!items.length) return;

                            currentBulkPreviewIndex = (currentBulkPreviewIndex - 1 + items.length) % items.length;

                            drawBulkPreview(currentBulkPreviewIndex);
                        }


                        function handleExportPng() {
                            if (!state.previewMode) {
                                drawBadge();
                            }

                            if (!previewCanvas || previewCanvas.classList.contains('hidden')) {
                                console.log('[export] preview indisponÃƒÂ­vel');
                                return;
                            }

                            const link = document.createElement('a');
                            const safeName = (state.name || 'badge').trim().toLowerCase().replace(/\s+/g, '-');
                            link.download = `cracha-${safeName}.png`;
                            link.href = previewCanvas.toDataURL('image/png');
                            link.click();

                            console.log('[export] PNG baixado');
                        }

                        async function handleExportBulkZip() {
                            if (!items.length) return;
                            if (!state.imageObj || !allPointsSet()) return;
                            if (!window.JSZip) {
                                alert('JSZip não carregou.');
                                return;
                            }

                            const zip = new JSZip();
                            const includeTeamBulk = includeTeamCheckboxBulk?.checked;

                            items.forEach((item, index) => {
                                const canvas = buildBadgeCanvas(item.name, item.team || '', includeTeamBulk);
                                if (!canvas) return;

                                const safe = (item.name || `item-${index + 1}`)
                                    .trim()
                                    .toLowerCase()
                                    .replace(/\s+/g, '-');

                                const base64 = canvas.toDataURL('image/png').split(',')[1];
                                zip.file(`cracha-${String(index + 1).padStart(3, '0')}-${safe}.png`, base64, {
                                    base64: true
                                });


                            });

                            const blob = await zip.generateAsync({
                                type: 'blob'
                            });
                            const link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = `crachas-bulk.zip`;
                            link.click();
                            URL.revokeObjectURL(link.href);
                        }

                        async function handleExportPdf() {
                            if (!state.previewMode) {
                                drawBadge();
                            }

                            if (!previewCanvas) return;

                            const {
                                jsPDF
                            } = window.jspdf;

                            const pdf = new jsPDF({
                                orientation: previewCanvas.width > previewCanvas.height ? 'landscape' : 'portrait',
                                unit: 'px',
                                format: [previewCanvas.width, previewCanvas.height]
                            });

                            const imgData = previewCanvas.toDataURL('image/png');

                            pdf.addImage(imgData, 'PNG', 0, 0, previewCanvas.width, previewCanvas.height);

                            const safeName = (state.name || 'badge')
                                .trim()
                                .toLowerCase()
                                .replace(/\s+/g, '-');

                            pdf.save(`cracha-${safeName}.pdf`)
                        }

                        async function handleExportPdfBulk() {
                            if (!items.length) return;
                            if (!state.imageObj || !allPointsSet()) return;

                            const {
                                jsPDF
                            } = window.jspdf;
                            const includeTeamBulk = includeTeamCheckboxBulk?.checked;

                            // CONFIGURAÇÕES
                            const pageWidth = 210;
                            const pageHeight = 297;
                            const badgeWidth = 100;
                            const badgeHeight = 70;
                            const margin = 4;
                            const gap = 2;
                            const cols = Math.floor((pageWidth - margin * 2 + gap) / (badgeWidth + gap));
                            const rowsPerPage = Math.floor((pageHeight - margin * 2 + gap) / (badgeHeight + gap));

                                const pdf = new jsPDF({
                                    orientation: 'portrait',
                                    unit: 'mm',
                                    format: 'a4'
                                });

                                let positionIndex = 0;

                                items.forEach((item, index) => {

                                    const canvas = buildBadgeCanvas(item.name, item.team || '', includeTeamBulk);
                                    if (!canvas) return;

                                    const imgData = canvas.toDataURL('image/png');

                                    const col = positionIndex % cols;
                                    const row = Math.floor(positionIndex / cols);

                                    const x = margin + col * (badgeWidth + gap);
                                    const y = margin + row * (badgeHeight + gap);

                                    pdf.addImage(imgData, 'PNG', x, y, badgeWidth, badgeHeight);

                                    positionIndex++;

                                    if (positionIndex >= cols * rowsPerPage && index < items.length - 1) {
                                        pdf.addPage();
                                        positionIndex = 0;
                                    }
                                });

                                pdf.save('crachas-a4.pdf');
                            }


                            function handleResetIndividual() {

                                state.imageObj = null;
                                state.imageNatural = {
                                    w: 0,
                                    h: 0
                                };

                                state.points = {
                                    topRight: null,
                                    topLeft: null,
                                    bottomRight: null,
                                    bottomLeft: null,
                                };
                                state.activePoint = null;


                                state.name = '';
                                state.team = '';
                                state.previewMode = false;


                                templateImage.removeAttribute('src');
                                templateImage.classList.add('hidden');
                                previewCanvas.classList.add('hidden');
                                uploadLabel.classList.remove('hidden');

                                areaLayer.innerHTML = '';
                                pointsLayer.innerHTML = '';

                                nameInput.value = '';
                                teamInput.value = '';

                                pointCards.forEach((c) => c.classList.remove('ring-2', 'ring-blue-500'));


                                if (templateInput) templateInput.value = '';

                                includeTeamCheckbox.checked = true;
                                syncTeamInputState();
                                syncDataCardState();

                                console.log('[reset] modelo e pontos removidos');
                            }

                            function handleResetBulk() {

                                state.imageObj = null;
                                state.imageNatural = {
                                    w: 0,
                                    h: 0
                                };


                                state.points = {
                                    topRight: null,
                                    topLeft: null,
                                    bottomRight: null,
                                    bottomLeft: null,
                                };
                                state.activePoint = null;


                                state.name = '';
                                state.team = '';
                                state.previewMode = false;


                                templateImage.removeAttribute('src');
                                templateImage.classList.add('hidden');
                                previewCanvas.classList.add('hidden');
                                uploadLabel.classList.remove('hidden');

                                areaLayer.innerHTML = '';
                                pointsLayer.innerHTML = '';

                                items = [];
                                editingIndex = null;
                                badgeList.innerHTML = '';
                                nameInputBulk.value = '';
                                teamInputBulk.value = '';

                                pointCards.forEach((c) => c.classList.remove('ring-2', 'ring-blue-500'));


                                if (templateInput) templateInput.value = '';

                                includeTeamCheckboxBulk.checked = true;
                                bulkNavigation.classList.add('hidden');
                                canvasContainer.classList.add('border-0')
                                canvasContainer.classList.remove('border-transparent')
                                syncTeamInputStateBulk();
                                syncDataCardState();

                                console.log('[reset] modelo e pontos removidos');
                            }

                            btnIndividualTab?.addEventListener('click', () => switchTab('individualTab'));
                            btnBulkTab?.addEventListener(
                                'click', () => switchTab('bulkTab'));
                            exportPngBtn?.addEventListener('click',
                                handleExportPng);
                            resetIndividualBtn?.addEventListener('click', handleResetIndividual);
                            generateBadgeBtn
                                ?.addEventListener('click', drawBadge);

                            exportPngBtnBulk?.addEventListener('click', handleExportBulkZip);
                            exportPdfBtnBulk?.addEventListener('click',
                                handleExportPdfBulk);
                            resetBulkBtn?.addEventListener('click', handleResetBulk);
                            generateBadgeBtnBulk
                                ?.addEventListener('click', function() {
                                    if (!items.length) return;
                                    if (!state.imageObj) {
                                        alert('Carregue um template antes de gerar em massa.');
                                        return;
                                    }
                                    currentBulkPreviewIndex = 0;
                                    drawBulkPreview(currentBulkPreviewIndex);
                                });

                            btnNextBulk?.addEventListener('click', goToNextBulkPreview);
                            btnPrevBulk?.addEventListener('click',
                                goToPrevBulkPreview);




                            syncDataCardState();
                            syncTeamInputStateBulk();
                        });
        </script>
    </section>
</x-layouts.app>
