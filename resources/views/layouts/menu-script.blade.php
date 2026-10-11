<script>
(() => {
    const menuToggle = document.querySelector('.fg-menu-toggle');
    const menu = document.getElementById('fg-menu');
    const categories = document.querySelector('.fg-category-menu');
    document.addEventListener('click', event => { if (!categories?.contains(event.target)) categories?.removeAttribute('open'); });
    menuToggle?.addEventListener('click', () => {
        const open = menuToggle.getAttribute('aria-expanded') !== 'true';
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.textContent = open ? 'Close' : 'Menu';
        menu.classList.toggle('is-open', open);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            if (categories?.open) { categories.removeAttribute('open'); categories.querySelector('summary').focus(); return; }
            menu.classList.remove('is-open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.textContent = 'Menu';
        }
    });
})();
</script>
