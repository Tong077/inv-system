const syncSaleMode = (form) => {
    const selector = form.querySelector('[data-sale-mode-select]');
    const fields = form.querySelector('[data-sale-mode-stock-fields]');
    if (!selector || !fields) return;

    const directStock = selector.value === 'stock_item';
    fields.hidden = !directStock;
    fields.querySelector('[data-sale-stock-item]')?.toggleAttribute('required', directStock);
    fields.querySelector('[data-sale-stock-quantity]')?.toggleAttribute('required', directStock);
    const stockSelect = fields.querySelector('[data-sale-stock-item]');
    const selectedUnit = stockSelect?.selectedOptions[0]?.dataset.unit || 'base units';
    fields.querySelector('[data-sale-stock-unit]')?.replaceChildren(document.createTextNode(selectedUnit));
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(syncSaleMode);
});

document.addEventListener('change', (event) => {
    const selector = event.target.closest('[data-sale-mode-select]');
    if (selector) {
        syncSaleMode(selector.form);
        return;
    }

    const stockSelect = event.target.closest('[data-sale-stock-item]');
    if (stockSelect) {
        const selectedUnit = stockSelect.selectedOptions[0]?.dataset.unit || 'base units';
        stockSelect.form?.querySelector('[data-sale-stock-unit]')?.replaceChildren(document.createTextNode(selectedUnit));
    }
});

document.addEventListener('show.bs.offcanvas', (event) => {
    const form = event.target.querySelector('form');
    if (form) syncSaleMode(form);
});
