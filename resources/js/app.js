document.documentElement.classList.add('js-ready');

document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
    const menu = document.getElementById(button.getAttribute('aria-controls'));
    if (!menu) return;
    const closeMenu = () => {
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-label', 'Buka menu');
        menu.hidden = true;
    };
    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        button.setAttribute('aria-label', expanded ? 'Buka menu' : 'Tutup menu');
        menu.hidden = expanded;
    });
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) { closeMenu(); button.focus(); }
    });
    window.matchMedia('(min-width: 801px)').addEventListener('change', closeMenu);
});

document.querySelectorAll('[data-auto-submit]').forEach((select) => {
    select.addEventListener('change', () => select.form.requestSubmit());
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        const label = button.dataset.passwordToggle.includes('confirmation') ? 'konfirmasi kata sandi' : 'kata sandi';
        button.setAttribute('aria-label', `${visible ? 'Sembunyikan' : 'Tampilkan'} ${label}`);
        button.textContent = visible ? 'Tutup' : 'Lihat';
    });
});

document.querySelectorAll('[data-dismiss-notice]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[role="status"]')?.remove());
});

document.querySelectorAll('[data-product-image]').forEach((image) => {
    const showFallback = () => {
        const fallback = document.createElement('div');
        fallback.className = 'image-placeholder';
        fallback.setAttribute('role', 'img');
        fallback.setAttribute('aria-label', image.alt + ' — foto belum tersedia');
        const label = document.createElement('span');
        label.className = 'display text-2xl';
        label.textContent = 'abfis bakery.';
        fallback.append(label);
        image.replaceWith(fallback);
    };
    if (image.complete && image.naturalWidth === 0) showFallback();
    else image.addEventListener('error', showFallback, { once: true });
});
