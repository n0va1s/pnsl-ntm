import JSZip from "jszip";
import { jsPDF } from "jspdf";
import { buildBadgeCanvas } from "./canvas.js";

function toSafeSlug(value, fallback = "item") {
    return (value || fallback).trim().toLowerCase().replace(/\s+/g, "-");
}

function ensurePreviewReady(dom, ensurePreview) {
    if (!dom.previewCanvas || dom.previewCanvas.classList.contains("hidden")) {
        if (typeof ensurePreview === "function") ensurePreview();
    }
    return dom.previewCanvas && !dom.previewCanvas.classList.contains("hidden");
}

export function handleExportPng(state, dom, { ensurePreview } = {}) {
    if (!ensurePreviewReady(dom, ensurePreview)) return;

    const link = document.createElement("a");
    const safeName = toSafeSlug(state.name, "badge");
    link.download = `cracha-${safeName}.png`;
    link.href = dom.previewCanvas.toDataURL("image/png");
    link.click();
}

export function handleExportPdf(state, dom, { ensurePreview } = {}) {
    if (!ensurePreviewReady(dom, ensurePreview)) return;

    const pdf = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4",
    });

    const imgData = dom.previewCanvas.toDataURL("image/png");

    const pageW = 100;
    const pageH = 70;

    const imgW = dom.previewCanvas.width;
    const imgH = dom.previewCanvas.height;

    const ratio = Math.min(pageW / imgW, pageH / imgH);
    const drawW = imgW * ratio;
    const drawH = imgH * ratio;

    const x = (pageW - drawW) / 2;
    const y = (pageH - drawH) / 2;

    pdf.addImage(imgData, "PNG", x, y, drawW, drawH);

    const safeName = (state.name || "badge")
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "-");
    pdf.save(`cracha-${safeName}.pdf`);
}

export async function handleExportBulkZip(state, ui, dom) {
    if (!ui.items.length) return;

    const includeTeamBulk = dom.includeTeamCheckboxBulk?.checked;
    const zip = new JSZip();

    ui.items.forEach((item, index) => {
        const canvas = buildBadgeCanvas(state, {
            nameValue: item.name,
            teamValue: item.team || "",
            includeTeamValue: includeTeamBulk,
            customNameFontSize: ui.customNameFontSize,
        });
        if (!canvas) return;

        const safe = toSafeSlug(item.name, `item-${index + 1}`);
        const base64 = canvas.toDataURL("image/png").split(",")[1];

        zip.file(
            `cracha-${String(index + 1).padStart(3, "0")}-${safe}.png`,
            base64,
            {
                base64: true,
            },
        );
    });

    const blob = await zip.generateAsync({
        type: "blob",
    });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "crachas-bulk.zip";
    link.click();
    URL.revokeObjectURL(link.href);
}

export async function handleExportPdfBulk(state, ui, dom) {
    if (!ui.items.length) return;

    const includeTeamBulk = dom.includeTeamCheckboxBulk?.checked;

    // CONFIGURAÇÕES
    const pageWidth = 210;
    const pageHeight = 297;
    const badgeWidth = 100;
    const badgeHeight = 70;
    const margin = 4;
    const gap = 2;

    const cols = Math.floor(
        (pageWidth - margin * 2 + gap) / (badgeWidth + gap),
    );
    const rowsPerPage = Math.floor(
        (pageHeight - margin * 2 + gap) / (badgeHeight + gap),
    );

    const pdf = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4",
    });

    let positionIndex = 0;

    ui.items.forEach((item, index) => {
        const canvas = buildBadgeCanvas(state, {
            nameValue: item.name,
            teamValue: item.team || "",
            includeTeamValue: includeTeamBulk,
            customNameFontSize: ui.customNameFontSize,
        });
        if (!canvas) return;

        const imgData = canvas.toDataURL("image/png");

        const col = positionIndex % cols;
        const row = Math.floor(positionIndex / cols);

        const x = margin + col * (badgeWidth + gap);
        const y = margin + row * (badgeHeight + gap);

        pdf.addImage(imgData, "PNG", x, y, badgeWidth, badgeHeight);

        positionIndex++;

        if (
            positionIndex >= cols * rowsPerPage &&
            index < ui.items.length - 1
        ) {
            pdf.addPage();
            positionIndex = 0;
        }
    });

    pdf.save("crachas-a4.pdf");
}
