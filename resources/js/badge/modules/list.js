export function renderList(ui, dom) {
    if (!dom.badgeList) return;

    dom.badgeList.innerHTML = "";

    ui.items.forEach((item, index) => {
        const li = document.createElement("li");
        li.className =
            "flex items-center justify-between bg-zinc-800 rounded-xl px-4 py-2 text-sm";

        li.innerHTML = `
                <div>
                    <p class="text-white font-semibold">${item.name}</p>
                    <p class="text-gray-400 text-sm">${item.team || ""}</p>
                </div>
                <div class="flex gap-2">
                    <button data-edit="${index}"
                        class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 cursor-pointer">
                    <i class="bi bi-pencil-square"></i>
                    </button>
                    <button data-delete="${index}"
                        class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-800 cursor-pointer">
                    <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            `;

        dom.badgeList.appendChild(li);
    });
}

function handleAddToListClick(ui, dom) {
    const name = dom.nameInputBulk.value.trim() || "";
    const team = dom.teamInputBulk.value.trim() || "";
    const includeTeamBulk = dom.includeTeamCheckboxBulk?.checked;

    if (!name || (includeTeamBulk && !team)) return;

    if (ui.editingIndex !== null) {
        ui.items[ui.editingIndex] = { name, team };
        ui.editingIndex = null;
        if (dom.addToListBtn) dom.addToListBtn.textContent = "Adicionar";
    } else {
        ui.items.push({ name, team });
    }

    if (dom.nameInputBulk) dom.nameInputBulk.value = "";
    if (dom.teamInputBulk) dom.teamInputBulk.value = "";

    renderList(ui, dom);
}

function editItem(ui, dom, index) {
    const item = ui.items[index];
    if (!item) return;

    if (dom.nameInputBulk) dom.nameInputBulk.value = item.name;
    if (dom.teamInputBulk) dom.teamInputBulk.value = item.team || "";
    ui.editingIndex = index;

    if (dom.addToListBtn) dom.addToListBtn.textContent = "Salvar";
}

function deleteItem(ui, dom, index) {
    ui.items.splice(index, 1);

    if (ui.editingIndex === index) {
        ui.editingIndex = null;
        if (dom.addToListBtn) dom.addToListBtn.textContent = "Adicionar";
    } else if (ui.editingIndex !== null && index < ui.editingIndex) {
        ui.editingIndex -= 1;
    }

    renderList(ui, dom);
}

export function bindListEvents(ui, dom) {
    dom.addToListBtn?.addEventListener("click", () => {
        handleAddToListClick(ui, dom);
    });

    dom.badgeList?.addEventListener("click", (e) => {
        const target = e.target.closest("button");
        if (!target) return;

        const editIndex = target.getAttribute("data-edit");
        const deleteIndex = target.getAttribute("data-delete");

        if (editIndex !== null) {
            editItem(ui, dom, Number(editIndex));
            return;
        }

        if (deleteIndex !== null) {
            deleteItem(ui, dom, Number(deleteIndex));
        }
    });
}
