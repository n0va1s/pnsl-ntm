import { buildBadgeCanvas } from "./canvas.js";

function renderCanvasToPreview(dom, canvas) {
    if (!canvas || !dom.previewCanvas) return;

    dom.previewCanvas.width = canvas.width;
    dom.previewCanvas.height = canvas.height;

    const ctx = dom.previewCanvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(canvas, 0, 0);
}

function enterPreviewMode(state, dom) {
    state.previewMode = true;

    dom.templateImage?.classList.add("hidden");
    dom.previewCanvas?.classList.remove("hidden");

    if (dom.pointsLayer) dom.pointsLayer.innerHTML = "";
    if (dom.areaLayer) dom.areaLayer.innerHTML = "";
}

export function drawBadge(state, dom, ui = null) {
    const canvas = buildBadgeCanvas(state, {
        nameValue: state.name || "Nome",
        teamValue: state.team || "",
        includeTeamValue: dom.includeTeamCheckbox?.checked,
        customNameFontSize: ui?.customNameFontSize ?? null,
    });

    if (!canvas) return;

    renderCanvasToPreview(dom, canvas);
    enterPreviewMode(state, dom);
}

export function drawBulkPreview(state, ui, dom, index = 0) {
    if (!ui.items.length) return;

    const item = ui.items[index];
    if (!item) return;

    const canvas = buildBadgeCanvas(state, {
        nameValue: item.name || "Nome",
        teamValue: item.team || "",
        includeTeamValue: dom.includeTeamCheckboxBulk?.checked,
        customNameFontSize: ui?.customNameFontSize ?? null,
    });

    if (!canvas) return;

    renderCanvasToPreview(dom, canvas);

    if (dom.bilkPreviewCounter) {
        dom.bilkPreviewCounter.textContent = `${index + 1} de ${ui.items.length}`;
    }

    dom.bulkNavigation?.classList.remove("hidden");
    dom.canvasContainer?.classList.remove("border-0");
    dom.canvasContainer?.classList.add("border-transparent");

    enterPreviewMode(state, dom);
}

export function goToNextBulkPreview(state, ui, dom) {
    if (!ui.items.length) return;

    ui.currentBulkPreviewIndex =
        (ui.currentBulkPreviewIndex + 1) % ui.items.length;
    drawBulkPreview(state, ui, dom, ui.currentBulkPreviewIndex);
}

export function goToPrevBulkPreview(state, ui, dom) {
    if (!ui.items.length) return;

    ui.currentBulkPreviewIndex =
        (ui.currentBulkPreviewIndex - 1 + ui.items.length) % ui.items.length;

    drawBulkPreview(state, ui, dom, ui.currentBulkPreviewIndex);
}
