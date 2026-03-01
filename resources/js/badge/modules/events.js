import { ACTIVE_RING } from "./constants";

function clearPointCardRings(dom) {
    dom.pointCards.forEach((c) =>
        c.classList.remove(
            "ring-2",
            "ring-rose-500",
            "ring-emerald-500",
            "ring-blue-500",
            "ring-purple-500",
        ),
    );
}

export function bindEvents({ state, ui, dom, actions }) {
    const {
        switchTab,
        renderPoints,
        syncDataCardState,
        bindListEvents,
        bindUploadEvents,
        bindFormEvents,
        drawBadge,
        drawBulkPreview,
        goToNextBulkPreview,
        goToPrevBulkPreview,
        handleExportPng,
        handleExportPdf,
        handleExportBulkZip,
        handleExportPdfBulk,
        handleResetIndividual,
        handleResetBulk,
    } = actions;

    bindListEvents?.(ui, dom);
    bindUploadEvents?.(state, dom, { renderPoints, syncDataCardState });
    bindFormEvents?.(state, dom, { syncDataCardStateRef: syncDataCardState });

    dom.btnIndividualTab?.addEventListener("click", () =>
        switchTab("individualTab", dom),
    );
    dom.btnBulkTab?.addEventListener("click", () => switchTab("bulkTab", dom));

    dom.pointCards.forEach((card) => {
        card.addEventListener("click", () => {
            state.activePoint = card.getAttribute("data-point-key");
            clearPointCardRings(dom);
            card.classList.add(
                "ring-2",
                ACTIVE_RING[state.activePoint] || "ring-blue-500",
            );
        });
    });

    dom.canvasContainer?.addEventListener("click", (e) => {
        if (!state.activePoint || !state.imageObj || state.previewMode) return;

        const rect = dom.canvasContainer.getBoundingClientRect();
        const x = Math.round(
            ((e.clientX - rect.left) / rect.width) * state.imageNatural.w,
        );
        const y = Math.round(
            ((e.clientY - rect.top) / rect.height) * state.imageNatural.h,
        );
        state.points[state.activePoint] = { x, y };
        state.activePoint = null;

        clearPointCardRings(dom);
        renderPoints(state, dom);
        syncDataCardState(state, dom);
    });

    dom.generateBadgeBtn?.addEventListener("click", () =>
        drawBadge(state, dom),
    );
    dom.exportPngBtn?.addEventListener("click", () =>
        handleExportPng(state, dom, {
            ensurePreview: () => drawBadge(state, dom, ui),
        }),
    );

    dom.exportPdfBtn?.addEventListener("click", () =>
        handleExportPdf(state, dom, {
            ensurePreview: () => drawBadge(state, dom, ui),
        }),
    );

    dom.generateBadgeBtnBulk?.addEventListener("click", () => {
        if (!ui.items.length || !state.imageObj) return;
        ui.currentBulkPreviewIndex = 0;
        drawBulkPreview(state, ui, dom, ui.currentBulkPreviewIndex);
    });

    dom.exportPngBtnBulk?.addEventListener("click", () =>
        handleExportBulkZip(state, ui, dom),
    );
    dom.exportPdfBtnBulk?.addEventListener("click", () =>
        handleExportPdfBulk(state, ui, dom),
    );

    dom.btnNextBulk?.addEventListener("click", () =>
        goToNextBulkPreview(state, ui, dom),
    );
    dom.btnPrevBulk?.addEventListener("click", () =>
        goToPrevBulkPreview(state, ui, dom),
    );

    dom.resetIndividualBtn?.addEventListener("click", () =>
        handleResetIndividual(state, ui, dom, { syncDataCardState }),
    );
    dom.resetBulkBtn?.addEventListener("click", () =>
        handleResetBulk(state, ui, dom, { syncDataCardState }),
    );

    syncDataCardState(state, dom);
}
