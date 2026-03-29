import { syncTeamInputState, syncTeamInputStateBulk } from "./form.js";

function resetBaseState(state) {
    state.imageObj = null;
    state.imageNatural = { w: 0, h: 0 };
    state.points = {
        topRight: null,
        topLeft: null,
        bottomRight: null,
        bottomLeft: null,
    };
    state.activePoint = null;
    state.name = "";
    state.team = "";
    state.previewMode = false;
}

function resetVisual(dom) {
    dom.templateImage.removeAttribute("src");
    dom.templateImage.classList.add("hidden");
    dom.previewCanvas.classList.add("hidden");
    dom.uploadLabel.classList.remove("hidden");

    if (dom.areaLayer) dom.areaLayer.innerHTML = "";
    if (dom.pointsLayer) dom.pointsLayer.innerHTML = "";

    dom.pointCards.forEach((c) =>
        c.classList.remove(
            "ring-2",
            "ring-blue-500",
            "ring-emerald-500/50",
            "ring-blue-500/50",
            "ring-purple-500/50",
            "ring-blue-500",
        ),
    );

    if (dom.templateInput) dom.templateInput.value = "";
}

export function handleResetIndividual(
    state,
    ui,
    dom,
    { syncDataCardState } = {},
) {
    resetBaseState(state);
    resetVisual(dom);

    if (dom.nameInput) dom.nameInput.value = "";
    if (dom.teamInput) dom.teamInput.value = "";

    if (dom.includeTeamCheckbox) dom.includeTeamCheckbox.checked = true;
    syncTeamInputState(state, dom);

    dom.bulkNavigation?.classList.add("hidden");
    dom.canvasContainer?.classList.add("border-0");
    dom.canvasContainer?.classList.remove("border-transparent");

    if (typeof syncDataCardState === "function") {
        syncDataCardState(state, dom);
    }
}

export function handleResetBulk(state, ui, dom, { syncDataCardState } = {}) {
    resetBaseState(state);
    resetVisual(dom);

    ui.items = [];
    ui.editingIndex = null;
    ui.currentBulkPreviewIndex = 0;

    if (dom.badgeList) dom.badgeList.innerHTML = "";
    if (dom.nameInputBulk) dom.nameInputBulk.value = "";
    if (dom.teamInputBulk) dom.teamInputBulk.value = "";
    if (dom.addToListBtn) dom.addToListBtn.textContent = "Adicionar";

    if (dom.includeTeamCheckboxBulk) dom.includeTeamCheckboxBulk.checked = true;
    syncTeamInputStateBulk(dom);

    dom.bulkNavigation?.classList.add("hidden");
    dom.canvasContainer?.classList.add("border-0");
    dom.canvasContainer?.classList.remove("border-transparent");

    if (typeof syncDataCardState === "function") {
        syncDataCardState(state, dom);
    }
}
