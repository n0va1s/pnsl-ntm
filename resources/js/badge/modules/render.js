import { POINT_LABELS } from "./constants.js";

export function allPointsSet(state) {
    return Object.values(state.points).every(Boolean);
}

export function renderAreaOverlay(state, dom) {
    if (!dom.areaLayer) return;

    dom.areaLayer.innerHTML = "";
    if (!state.imageObj || !allPointsSet(state) || state.previewMode) return;

    const p = state.points;
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("class", "absolute inset-0 w-full h-full");
    svg.setAttribute(
        "viewBox",
        `0 0 ${state.imageNatural.w} ${state.imageNatural.h}`,
    );
    svg.setAttribute("preserveAspectRatio", "none");

    const polygon = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "polygon",
    );
    polygon.setAttribute(
        "points",
        `${p.topLeft.x},${p.topLeft.y} ${p.topRight.x},${p.topRight.y} ${p.bottomRight.x},${p.bottomRight.y} ${p.bottomLeft.x},${p.bottomLeft.y}`,
    );
    polygon.setAttribute("fill", "rgba(14, 165, 233, 0.15)");
    polygon.setAttribute("stroke", "hsl(199, 89%, 48%)");
    polygon.setAttribute("stroke-width", "2");
    polygon.setAttribute("stroke-dasharray", "8 4");

    svg.appendChild(polygon);
    dom.areaLayer.appendChild(svg);
}

export function renderPoints(state, dom) {
    if (!dom.pointsLayer) return;

    dom.pointsLayer.innerHTML = "";
    if (!state.imageObj || state.previewMode) return;

    Object.keys(state.points).forEach((key) => {
        const p = state.points[key];
        if (!p) return;

        const leftPct = (p.x / state.imageNatural.w) * 100;
        const topPct = (p.y / state.imageNatural.h) * 100;

        const pointClasses = {
            topRight: "bg-rose-500/70",
            topLeft: "bg-emerald-500/70",
            bottomRight: "bg-blue-500/70",
            bottomLeft: "bg-purple-500/70",
        };

        const el = document.createElement("div");
        const colorClass = pointClasses[key] || "bg-blue-600";

        el.className = [
            "absolute -translate-x-1/2 -translate-y-1/2 w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center",
            "border-2 border-black/70",
            colorClass,
        ].join(" ");

        el.style.left = `${leftPct}%`;
        el.style.top = `${topPct}%`;
        el.textContent = POINT_LABELS[key] || "";
        dom.pointsLayer.appendChild(el);
    });

    renderAreaOverlay(state, dom);
}

export function switchTab(tab, dom) {
    if (tab === "individualTab") {
        dom.individualTab.classList.remove("hidden");
        dom.bulkTab.classList.add("hidden");

        dom.btnIndividualTab.classList.add("bg-blue-500", "text-white");
        dom.btnIndividualTab.classList.remove(
            "text-gray-500",
            "border-transparent",
        );
        dom.btnBulkTab.classList.remove("bg-blue-500", "text-white");
        dom.btnBulkTab.classList.add("text-gray-500", "border-transparent");
        return;
    }
    dom.individualTab.classList.add("hidden");
    dom.bulkTab.classList.remove("hidden");

    dom.btnBulkTab.classList.add("bg-blue-500", "text-white");
    dom.btnBulkTab.classList.remove("text-gray-500", "border-transparent");

    dom.btnIndividualTab.classList.remove("bg-blue-500", "text-white");
    dom.btnIndividualTab.classList.add("text-gray-500", "border-transparent");
}
