const sidebar = document.getElementById('admin-sidebar');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarOverlay = document.querySelector('.admin-sidebar-overlay');

function setSidebar(open) {
    document.body.classList.toggle('sidebar-open', open);
    sidebarToggle?.setAttribute('aria-expanded', String(open));
    if (sidebarOverlay) sidebarOverlay.hidden = !open;
    if (sidebar) sidebar.inert = !open && window.innerWidth <= 800;
    if (open) sidebar?.querySelector('[data-sidebar-close]')?.focus();
}

sidebarToggle?.addEventListener('click', () => setSidebar(!document.body.classList.contains('sidebar-open')));
document.querySelectorAll('[data-sidebar-close]').forEach((button) => button.addEventListener('click', () => {
    setSidebar(false);
    sidebarToggle?.focus();
}));
document.addEventListener('keydown', (event) => {
    if (!document.body.classList.contains('sidebar-open')) return;
    if (event.key === 'Escape') { setSidebar(false); sidebarToggle?.focus(); }
    if (event.key === 'Tab') {
        const controls = [...sidebar.querySelectorAll('a, button')].filter((el) => !el.disabled && el.getClientRects().length);
        const first = controls[0], last = controls.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
});
window.matchMedia('(min-width: 801px)').addEventListener('change', () => setSidebar(false));
setSidebar(false);

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-image-preview]').forEach((input) => {
    const image = document.getElementById(input.dataset.imagePreview);
    const placeholder = image?.parentElement.querySelector('[data-preview-placeholder]');
    let objectUrl;
    input.addEventListener('change', () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        const file = input.files[0];
        input.setCustomValidity('');
        if (!file) return;
        if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 2 * 1024 * 1024) {
            input.setCustomValidity('Pilih gambar JPG atau PNG berukuran maksimal 2 MB.');
            input.reportValidity();
            return;
        }
        objectUrl = URL.createObjectURL(file);
        image.src = objectUrl;
        image.hidden = false;
        if (placeholder) placeholder.hidden = true;
    });
});

const money = (value) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);
document.querySelectorAll('[data-order-editor]').forEach((form) => {
    const rowsContainer = form.querySelector('[data-order-rows]');
    const template = form.querySelector('[data-order-row-template]');
    let nextIndex = Math.max(-1, ...[...rowsContainer.querySelectorAll('[data-row-product]')].map((select) => Number(select.name.match(/products\[(\d+)\]/)?.[1] ?? -1))) + 1;

    function updateTotal() {
        let total = 0, count = 0;
        const rows = [...rowsContainer.querySelectorAll('[data-order-row]')];
        const selectedIds = rows.map((row) => row.querySelector('[data-row-product]')?.value).filter(Boolean);
        rows.forEach((row) => {
            const select = row.querySelector('[data-row-product]');
            const quantity = row.querySelector('[data-row-quantity]');
            const option = select?.selectedOptions[0];
            const price = Number(row.dataset.fixedPrice ?? option?.dataset.price ?? 0);
            const value = Math.max(0, Number(quantity?.value) || 0);
            const hasProduct = row.hasAttribute('data-fixed-price') || Boolean(select?.value);
            if (select) {
                select.setCustomValidity(select.value && selectedIds.filter((id) => id === select.value).length > 1 ? 'Kombinasikan produk yang sama dalam satu baris.' : '');
                if (select.value && option?.dataset.stock) quantity.max = option.dataset.stock;
                else quantity.removeAttribute('max');
            }
            const subtotal = hasProduct ? price * value : 0;
            if (hasProduct) { total += subtotal; count += value; }
            const output = row.querySelector('[data-row-subtotal]');
            if (output) output.textContent = money(subtotal);
            const remove = row.querySelector('[data-remove-order-row]');
            if (remove) remove.disabled = rows.length <= 1;
        });
        form.querySelector('[data-order-total]').textContent = money(total);
        form.querySelector('[data-order-count]').textContent = count + ' buah';
    }

    form.querySelector('[data-add-order-row]')?.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[name], [id], [for]').forEach((element) => {
            for (const attribute of ['name', 'id', 'for']) {
                if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', nextIndex));
            }
        });
        rowsContainer.append(fragment);
        nextIndex++;
        updateTotal();
        rowsContainer.lastElementChild.querySelector('select')?.focus();
    });
    form.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-order-row]');
        if (remove && rowsContainer.querySelectorAll('[data-order-row]').length > 1) {
            remove.closest('[data-order-row]').remove();
            updateTotal();
        }
    });
    form.addEventListener('input', updateTotal);
    form.addEventListener('change', updateTotal);
    form.querySelector('[data-customer-select]')?.addEventListener('change', (event) => {
        const recipient = form.querySelector('[name="customer_name"]');
        if (!recipient.value) recipient.value = event.target.selectedOptions[0]?.textContent.trim() ?? '';
    });
    updateTotal();
});
