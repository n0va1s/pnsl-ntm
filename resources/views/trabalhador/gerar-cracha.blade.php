<x-layouts.app>
    <section class="min-h-screen p-4 md:p-8">
        <header class="max-w-7xl mx-auto mb-8">
            <div class="flex items-center gap-3 mb-1">
                <div class="bg-blue-500 p-2 rounded-lg shadow-lg shadow-blue-500/20">
                    <i data-lucide="sparkles" class="w-6 h-6 text-white"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Gerador de Crachás</h1>
            </div>
            <p class="text-slate-400 text-sm md:text-base ml-12">
                Carregue seu modelo, defina a área de preenchimento e gere crachás personalizados
            </p>
        </header>

        <main class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4 space-y-6">
                    <div class="card-bg p-6 rounded-2xl shadow-2xl dark:bg-zinc-900">
                        <div class="flex items-center gap-3 mb-6">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold">1</span>
                            <h2 class="font-semibold text-lg">Definir Pontos</h2>
                        </div>

                        <div class="space-y-3">
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

                    <div class="card-bg p-6 rounded-2xl shadow-2xl dark:bg-zinc-900">
                        <div class="flex items-center gap-3 mb-6">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold">2</span>
                            <h2 class="font-semibold text-lg">Inserir Dados</h2>
                        </div>

                        <div class="space-y-4">
                            <input id="nameInput" type="text" placeholder="Digite o nome completo"
                                class="w-full dark:bg-[#0a1120] border border-slate-700 rounded-xl px-4 py-3 text-sm">
                            <input id="teamInput" type="text" placeholder="Digite a equipe"
                                class="w-full dark:bg-[#0a1120] border border-slate-700 rounded-xl px-4 py-3 text-sm">
                            <label class="flex items-center gap-2 text-sm text-slate-300">
                                <input id="includeTeamCheckbox" type="checkbox" class="rounded border-slate-600"
                                    checked>
                                Incluir equipe no crachá
                            </label>

                            <button id="generateBadgeBtn" type="button"
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-xl px-4 py-3">
                                Gerar Crachá
                            </button>
                            <button id="exportPngBtn" type="button"
                                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-xl px-4 py-3">
                                Exportar PNG
                            </button>

                            <button id="resetAllBtn" type="button"
                                class="w-full border border-slate-600 hover:bg-slate-300 dark:hover:bg-slate-800 text-zinc-900 dark:text-slate-200 font-medium rounded-xl px-4 py-3">
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
                                <canvas id="previewCanvas" class="hidden w-full h-auto"></canvas>

                                <div id="areaLayer" class="absolute inset-0 pointer-events-none"></div>
                                <div id="pointsLayer" class="absolute inset-0 pointer-events-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializa ícones do Lucide, se a lib estiver disponível na página.
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }

                // Estado central da tela (imagem, pontos, dados de texto e modo preview).
                const state = {
                    imageObj: null, // Objeto Image carregado a partir do upload.
                    imageNatural: {
                        w: 0, // Largura real da imagem.
                        h: 0 // Altura real da imagem.
                    },
                    activePoint: null, // Qual ponto (1..4) está selecionado para receber o próximo clique.
                    points: {
                        topRight: null, // Coordenada do ponto 1.
                        topLeft: null, // Coordenada do ponto 2.
                        bottomRight: null, // Coordenada do ponto 3.
                        bottomLeft: null, // Coordenada do ponto 4.
                    },
                    name: '', // Texto do nome a ser desenhado no crachá.
                    team: '', // Texto da equipe a ser desenhado no crachá.
                    previewMode: false, // true quando o canvas final já foi gerado.
                };

                // Referências dos elementos DOM usados pelo fluxo.
                const templateInput = document.getElementById('templateInput'); // Input file do modelo.
                const templateImage = document.getElementById('templateImage'); // <img> para exibir o modelo.
                const uploadLabel = document.getElementById('uploadLabel'); // Área clicável de upload (placeholder).
                const canvasContainer = document.getElementById(
                    'canvasContainer'); // Container para captar clique de ponto.
                const previewCanvas = document.getElementById('previewCanvas'); // Canvas final com nome/equipe.
                const areaLayer = document.getElementById(
                    'areaLayer'); // Camada SVG para desenhar o polígono dos 4 pontos.
                const pointsLayer = document.getElementById('pointsLayer'); // Camada de bolinhas numeradas dos pontos.
                const nameInput = document.getElementById('nameInput'); // Input de nome.
                const teamInput = document.getElementById('teamInput'); // Input de equipe.
                const generateBadgeBtn = document.getElementById('generateBadgeBtn'); // Botão "Gerar Crachá".
                const pointCards = document.querySelectorAll('.point-card'); // Cards laterais de seleção de ponto.
                const exportPngBtn = document.getElementById('exportPngBtn'); // Botão de exportar PNG.
                const resetAllBtn = document.getElementById('resetAllBtn'); // Botão de reset total.
                const includeTeamCheckbox = document.getElementById(
                'includeTeamCheckbox'); // Checkbox para incluir equipe.

                // Rótulo visual (número) de cada ponto.
                const pointLabels = {
                    topRight: '1',
                    topLeft: '2',
                    bottomRight: '3',
                    bottomLeft: '4'
                };

                // Retorna true quando os 4 pontos já foram definidos.
                function allPointsSet() {
                    return Object.values(state.points).every(Boolean);
                }

                // Desenha o polígono tracejado da área de impressão quando os 4 pontos existem.
                function renderAreaOverlay() {
                    areaLayer.innerHTML = '';
                    if (!state.imageObj || !allPointsSet() || state.previewMode) return;

                    const p = state.points; // Atalho para facilitar leitura.
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

                    console.log('[area] polígono desenhado');
                }

                // Desenha as bolinhas dos pontos já marcados e atualiza o overlay da área.
                function renderPoints() {
                    pointsLayer.innerHTML = '';
                    if (!state.imageObj || state.previewMode) return;

                    Object.keys(state.points).forEach((key) => {
                        const p = state.points[key];
                        if (!p) return;

                        // Converte coordenada real da imagem para percentual na tela.
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

                // Evento de upload da imagem base do crachá.
                templateInput?.addEventListener('change', function(e) {
                    const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
                    console.log('[upload] change', file ? {
                        name: file.name,
                        type: file.type,
                        size: file.size
                    } : 'sem arquivo');
                    if (!file) return;

                    const reader = new FileReader(); // Lê arquivo local para DataURL.
                    reader.onload = function(ev) {
                        const dataUrl = ev.target && ev.target.result ? ev.target.result : null;
                        console.log('[upload] FileReader.onload', {
                            ok: !!dataUrl
                        });
                        if (!dataUrl || typeof dataUrl !== 'string') return;

                        const img = new Image(); // Cria imagem em memória para obter dimensões naturais.
                        img.onload = function() {
                            state.imageObj = img;
                            state.imageNatural = {
                                w: img.width,
                                h: img.height
                            };
                            state.previewMode = false;

                            // Mostra modelo e esconde preview/upload placeholder.
                            templateImage.src = dataUrl;
                            templateImage.classList.remove('hidden');
                            previewCanvas.classList.add('hidden');
                            uploadLabel.classList.add('hidden');

                            renderPoints();
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


                // Marca o ponto selecionado ao clicar na imagem/modelo.
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
                });

                // Captura texto do nome em tempo real.
                nameInput?.addEventListener('input', function(e) {
                    state.name = e.target.value;
                    console.log('[input] name', state.name);
                });

                // Captura texto da equipe em tempo real.
                teamInput?.addEventListener('input', function(e) {
                    state.team = e.target.value;
                    console.log('[input] team', state.team);
                });

                // Ajusta dinamicamente tamanho da fonte para caber na largura disponível.
                function findOptimalFontSize(ctx, text, maxWidth, start, min = 12) {
                    let size = start;
                    ctx.font = `bold ${size}px Inter, sans-serif`;
                    while (ctx.measureText(text).width > maxWidth && size > min) {
                        size -= 1;
                        ctx.font = `bold ${size}px Inter, sans-serif`;
                    }
                    return size;
                }

                // Gera o crachá final no canvas com nome/equipe dentro da área dos 4 pontos.
                function drawBadge() {
                    if (!state.imageObj || !allPointsSet()) {
                        console.log('[gerar] faltando imagem ou 4 pontos');
                        return;
                    }

                    const ctx = previewCanvas.getContext('2d'); // Contexto 2D do canvas de saída.
                    const w = state.imageNatural.w;
                    const h = state.imageNatural.h;

                    previewCanvas.width = w;
                    previewCanvas.height = h;
                    ctx.clearRect(0, 0, w, h);
                    ctx.drawImage(state.imageObj, 0, 0);

                    const {
                        topLeft,
                        topRight,
                        bottomLeft,
                        bottomRight
                    } = state.points;
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

                    const name = state.name || 'Nome';
                    const team = state.team || 'Equipe';
                    const includeTeam = includeTeamCheckbox?.checked;

                    const initialNameSize = Math.max(24, Math.min(areaHeight * 0.25, areaWidth * 0.1));
                    const nameFontSize = findOptimalFontSize(ctx, name, maxTextWidth, initialNameSize, 14);


                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#000';
                    ctx.font = `bold ${nameFontSize}px Inter, sans-serif`;

                    if (!includeTeam) { // só nome
                        ctx.fillText(name, centerX, centerY);
                    } else { // Nome e equipe
                        const teamFontSize = Math.max(10, nameFontSize - 10);
                        const totalTextHeight = nameFontSize + teamFontSize + 8;
                        const nameY = centerY - totalTextHeight / 2 + nameFontSize / 2;
                        const teamY = nameY + nameFontSize / 2 + 8 + teamFontSize / 2;

                        ctx.fillText(name, centerX, nameY);

                        ctx.fillStyle = 'rgba(0,0,0,.85)';
                        ctx.font = `${teamFontSize}px Inter, sans-serif`;
                        ctx.fillText(team, centerX, teamY);
                    }

                    state.previewMode = true;
                    templateImage.classList.add('hidden');
                    previewCanvas.classList.remove('hidden');
                    pointsLayer.innerHTML = '';
                    areaLayer.innerHTML = '';

                    console.log('[gerar] crachá gerado com sucesso');
                }

                // Exporta o canvas final em arquivo PNG.
                function handleExportPng() {
                    // Se ainda não estiver em preview, tenta gerar primeiro.
                    if (!state.previewMode) {
                        drawBadge();
                    }

                    if (!previewCanvas || previewCanvas.classList.contains('hidden')) {
                        console.log('[export] preview indisponível');
                        return;
                    }

                    const link = document.createElement('a'); // Link temporário para forçar download.
                    const safeName = (state.name || 'badge').trim().toLowerCase().replace(/\s+/g, '-');
                    link.download = `cracha-${safeName}.png`;
                    link.href = previewCanvas.toDataURL('image/png');
                    link.click();

                    console.log('[export] PNG baixado');
                }

                // Reseta completamente a tela: modelo, pontos, inputs e preview.
                function handleResetAll() {
                    // modelo
                    state.imageObj = null;
                    state.imageNatural = {
                        w: 0,
                        h: 0
                    };

                    // pontos
                    state.points = {
                        topRight: null,
                        topLeft: null,
                        bottomRight: null,
                        bottomLeft: null,
                    };
                    state.activePoint = null;

                    // dados
                    state.name = '';
                    state.team = '';
                    state.previewMode = false;

                    // UI
                    templateImage.removeAttribute('src');
                    templateImage.classList.add('hidden');
                    previewCanvas.classList.add('hidden');
                    uploadLabel.classList.remove('hidden');

                    areaLayer.innerHTML = '';
                    pointsLayer.innerHTML = '';

                    nameInput.value = '';
                    teamInput.value = '';

                    pointCards.forEach((c) => c.classList.remove('ring-2', 'ring-blue-500'));

                    // Limpa input file para aceitar selecionar o mesmo arquivo novamente.
                    if (templateInput) templateInput.value = '';

                    console.log('[reset] modelo e pontos removidos');
                }

                // Liga os botões às funções de ação.
                exportPngBtn?.addEventListener('click', handleExportPng);
                resetAllBtn?.addEventListener('click', handleResetAll);
                generateBadgeBtn?.addEventListener('click', drawBadge);
            });
        </script>
    </section>
</x-layouts.app>
