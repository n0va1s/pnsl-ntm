export function getDom() {
    return {
        templateInput: document.getElementById("templateInput"),
        templateImage: document.getElementById("templateImage"),
        uploadLabel: document.getElementById("uploadLabel"),
        canvasContainer: document.getElementById("canvasContainer"),
        previewCanvas: document.getElementById("previewCanvas"),
        areaLayer: document.getElementById("areaLayer"),
        pointsLayer: document.getElementById("pointsLayer"),

        nameInput: document.getElementById("nameInput"),
        teamInput: document.getElementById("teamInput"),
        nameInputBulk: document.getElementById("nameInputBulk"),
        teamInputBulk: document.getElementById("teamInputBulk"),

        generateBadgeBtn: document.getElementById("generateBadgeBtn"),
        generateBadgeBtnBulk: document.getElementById("generateBadgeBtnBulk"),
        exportPngBtn: document.getElementById("exportPngBtn"),
        exportPdfBtn: document.getElementById("exportPdfBtn"),
        exportPngBtnBulk: document.getElementById("exportPngBtnBulk"),
        exportPdfBtnBulk: document.getElementById("exportPdfBtnBulk"),
        resetIndividualBtn: document.getElementById("resetBtnIndividual"),
        resetBulkBtn: document.getElementById("resetBtnBulk"),

        includeTeamCheckbox: document.getElementById("includeTeamCheckbox"),
        includeTeamCheckboxBulk: document.getElementById(
            "includeTeamCheckboxBulk",
        ),

        dataCard: document.getElementById("dataCard"),
        individualTab: document.getElementById("individualTab"),
        bulkTab: document.getElementById("bulkTab"),
        btnIndividualTab: document.getElementById("tabIndividualBtn"),
        btnBulkTab: document.getElementById("tabBulkBtn"),

        addToListBtn: document.getElementById("addToListBtn"),
        badgeList: document.getElementById("badgeList"),

        btnPrevBulk: document.getElementById("btnPrevBulk"),
        btnNextBulk: document.getElementById("btnNextBulk"),
        bilkPreviewCounter: document.getElementById("bilkPreviewCounter"),
        bulkNavigation: document.getElementById("bulkNavigation"),

        pointCards: document.querySelectorAll(".point-card"),
    };
}
