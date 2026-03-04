import { allPointsSet } from "./render.js";

export function findOptimalFontSize(ctx, text, maxWidth, min = 12, max = 220) {
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

export function buildBadgeCanvas(
    state,
    { nameValue, teamValue, includeTeamValue, customNameFontSize = null } = {},
) {
    if (!state.imageObj || !allPointsSet(state)) return null;

    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");

    const w = state.imageNatural.w;
    const h = state.imageNatural.h;

    canvas.width = w;
    canvas.height = h;
    ctx.clearRect(0, 0, w, h);
    ctx.drawImage(state.imageObj, 0, 0);

    const { topLeft, topRight, bottomLeft, bottomRight } = state.points;
    if (!topLeft || !topRight || !bottomLeft || !bottomRight) return null;

    const minX = Math.min(topLeft.x, topRight.x, bottomLeft.x, bottomRight.x);
    const maxX = Math.max(topLeft.x, topRight.x, bottomLeft.x, bottomRight.x);
    const minY = Math.min(topLeft.y, topRight.y, bottomLeft.y, bottomRight.y);
    const maxY = Math.max(topLeft.y, topRight.y, bottomLeft.y, bottomRight.y);

    const areaWidth = maxX - minX;
    const areaHeight = maxY - minY;
    const centerX = minX + areaWidth / 2;
    const centerY = minY + areaHeight / 2;
    const padding = areaWidth * 0.05;
    const maxTextWidth = areaWidth - padding * 2;

    const name = nameValue || "Nome";
    const team = teamValue || "";
    const includeTeam = !!includeTeamValue;

    const maxNameFontSize = includeTeam
        ? Math.floor(areaHeight * 0.42)
        : Math.floor(areaHeight * 0.7);

    const autoNameFontSize = findOptimalFontSize(
        ctx,
        name,
        maxTextWidth,
        14,
        maxNameFontSize,
    );
    const nameFontSize = customNameFontSize ?? autoNameFontSize;

    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillStyle = "#000";
    ctx.font = `bold ${nameFontSize}px Inter, sans-serif`;

    // Quebra de linhas
    if (!includeTeam) {
        const words = name.trim().split(/\s+/);
        let line1 = name;
        let line2 = "";

        if (words.length > 1) {
            let bestLine1 = name;
            let bestLine2 = "";

            for (let i = 1; i < words.length; i++) {
                const test1 = words.slice(0, i).join(" ");
                const text2 = words.slice(i).join(" ");

                const w1 = ctx.measureText(test1).width;
                const w2 = ctx.measureText(text2).width;

                if (w1 <= maxTextWidth && w2 <= maxTextWidth) {
                    bestLine1 = test1;
                    bestLine2 = text2;
                }
            }

            line1 = bestLine1;
            line2 = bestLine2;
        }

        if (line2) {
            const lineHeight = nameFontSize * 1.1;
            const y1 = centerY - lineHeight / 2;
            const y2 = centerY + lineHeight / 2;
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

        ctx.fillStyle = "rgba(0,0,0,.85)";
        ctx.font = `${teamFontSize}px Inter, sans-serif`;
        ctx.fillText(team, centerX, teamY);
    }

    return canvas;
}
