import { allPointsSet } from "./render.js";

export function syncTeamInputState(state, dom) {
    const includeTeam = dom.includeTeamCheckbox?.checked;
    if (!dom.teamInput) return;

    dom.teamInput.disabled = !includeTeam;

    if (!includeTeam) {
        dom.teamInput.value = "";
        state.team = "";
        dom.teamInput.classList.add("opacity-50", "cursor-not-allowed");
        dom.teamInput.classList.remove("opacity-100", "cursor-text");
    } else {
        dom.teamInput.classList.remove("opacity-50", "cursor-not-allowed");
        dom.teamInput.classList.add("opacity-100", "cursor-text");
    }
}

export function syncTeamInputStateBulk(dom) {
    const includeTeamBulk = dom.includeTeamCheckboxBulk?.checked;
    if (!dom.teamInputBulk) return;

    dom.teamInputBulk.disabled = !includeTeamBulk;

    if (!includeTeamBulk) {
        dom.teamInputBulk.value = "";
        dom.teamInputBulk.classList.add("opacity-50", "cursor-not-allowed");
        dom.teamInputBulk.classList.remove("opacity-100", "cursor-text");
    } else {
        dom.teamInputBulk.classList.remove("opacity-50", "cursor-not-allowed");
        dom.teamInputBulk.classList.add("opacity-100", "cursor-text");
    }
}

export function syncDataCardState(state, dom) {
    const enabled = !!state.imageObj && allPointsSet(state);

    const controls = [
        dom.nameInput,
        dom.teamInput,
        dom.includeTeamCheckbox,
        dom.generateBadgeBtn,
        dom.exportPngBtn,
        dom.exportPdfBtn,
        dom.resetIndividualBtn,
    ];

    controls.forEach((el) => {
        if (!el) return;
        el.disabled = !enabled;
    });

    [dom.btnIndividualTab, dom.btnBulkTab].forEach((tabBtn) => {
        if (!tabBtn) return;
        tabBtn.disabled = !enabled;
        tabBtn.classList.toggle("opacity-50", !enabled);
        tabBtn.classList.toggle("cursor-not-allowed", !enabled);
    });

    if (!dom.dataCard) return;

    if (!enabled) {
        dom.dataCard.classList.add("opacity-50", "cursor-not-allowed");
        dom.dataCard.classList.remove("opacity-100", "cursor-text");
    } else {
        dom.dataCard.classList.remove("opacity-50", "cursor-not-allowed");
        dom.dataCard.classList.add("opacity-100", "cursor-text");
        syncTeamInputState(state, dom);
    }
}

export function bindFormEvents(state, dom, { syncDataCardStateRef } = {}) {
    dom.nameInput?.addEventListener("input", (e) => {
        state.name = e.target.value;
    });

    dom.teamInput?.addEventListener("input", (e) => {
        state.team = e.target.value;
    });

    dom.includeTeamCheckbox?.addEventListener("change", () => {
        syncTeamInputState(state, dom);
    });

    dom.includeTeamCheckboxBulk.addEventListener("click", () => {
        syncTeamInputStateBulk(dom);
    });

    if (syncDataCardStateRef) {
        syncDataCardStateRef(state, dom);
    } else {
        syncDataCardState(state, dom);
    }

    syncTeamInputStateBulk(dom);
}
