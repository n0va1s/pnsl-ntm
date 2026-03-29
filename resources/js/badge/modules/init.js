import { createState, createUiState } from "./state.js";
import { getDom } from "./dom.js";
import { bindEvents } from "./events.js";

import { renderPoints, switchTab } from "./render.js";
import { syncDataCardState, bindFormEvents } from "./form.js";
import { bindUploadEvents } from "./upload.js";
import { bindListEvents } from "./list.js";

import {
    drawBadge,
    drawBulkPreview,
    goToNextBulkPreview,
    goToPrevBulkPreview,
} from "./preview.js";

import {
    handleExportPng,
    handleExportPdf,
    handleExportBulkZip,
    handleExportPdfBulk,
} from "./export.js";

import { handleResetIndividual, handleResetBulk } from "./reset.js";

function ensureBootstrapIcons() {
    if (document.querySelector('link[data-icons="bootstrap-icons"]')) return;

    const iconLink = document.createElement("link");
    iconLink.rel = "stylesheet";
    iconLink.href =
        "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css";
    iconLink.setAttribute("data-icons", "bootstrap-icons");
    document.head.appendChild(iconLink);
}

function initLucide() {
    if (window.lucide && typeof window.lucide.createIcons === "function") {
        window.lucide.createIcons();
    }
}

export function initBadge() {
    const state = createState();
    const ui = createUiState();
    const dom = getDom();

    ensureBootstrapIcons();
    initLucide();

    const actions = {
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
    };

    bindEvents({ state, ui, dom, actions });
}
