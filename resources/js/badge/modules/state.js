export function createState() {
    return {
        imageObj: null,
        imageNatural: {
            w: 0,
            h: 0,
        },
        activePoint: null,
        points: {
            topRight: null,
            topLeft: null,
            bottomRight: null,
            bottomLeft: null,
        },
        name: "",
        team: "",
        previewMode: false,
    };
}

export function createUiState() {
    return {
        items: [],
        editingIndex: null,
        currentBulkPreviewIndex: 0,
        customNameFontSize: null,
    };
}
