export function bindUploadEvents(
    state,
    dom,
    { renderPoints, syncDataCardState },
) {
    dom.templateInput?.addEventListener("change", (e) => {
        handleTemplateUpload(e, state, dom, {
            renderPoints,
            syncDataCardState,
        });
    });
}

function handleTemplateUpload(
    e,
    state,
    dom,
    { renderPoints, syncDataCardState },
) {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();

    reader.onload = (ev) => {
        const dataUrl = ev.target?.result;
        if (!dataUrl || typeof dataUrl !== "string") return;

        const img = new Image();

        img.onload = () => {
            state.imageObj = img;
            state.imageNatural = { w: img.width, h: img.height };
            state.previewMode = false;

            dom.templateImage.src = dataUrl;
            dom.templateImage.classList.remove("hidden");
            dom.previewCanvas.classList.add("hidden");
            dom.uploadLabel.classList.add("hidden");

            renderPoints(state, dom);
            syncDataCardState(state, dom);
        };

        img.onerror = (err) => console.error("[upload] erro image", err);
        img.src = dataUrl;
    };

    reader.onerror = (err) => console.error("[upload] erro reader", err);
    reader.readAsDataURL(file);
}
