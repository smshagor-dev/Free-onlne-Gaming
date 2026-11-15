import '../css/admin-cms.css';

const toggle = document.querySelector('[data-cms-menu-toggle]');
const backdrop = document.querySelector('[data-cms-backdrop]');
const side = document.querySelector('[data-cms-sidebar]');
const closeMenu = () => {
    document.body.classList.remove('cms-menu-open');
    toggle?.setAttribute('aria-expanded', 'false');
};
toggle?.addEventListener('click', () => {
    const expanded = document.body.classList.toggle('cms-menu-open');
    toggle.setAttribute('aria-expanded', String(expanded));
    if (expanded) side?.querySelector('a')?.focus();
});
backdrop?.addEventListener('click', () => { closeMenu(); toggle?.focus(); });
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.body.classList.contains('cms-menu-open')) {
        closeMenu();
        toggle?.focus();
    }
});
window.matchMedia('(min-width: 901px)').addEventListener('change', e => {
    if (e.matches) closeMenu();
});

const tabs = [...document.querySelectorAll('[data-cms-tab]')];
const panels = [...document.querySelectorAll('[data-cms-panel]')];
function activateTab(name, focus = false) {
    if (!tabs.some(tab => tab.dataset.cmsTab === name)) return;
    tabs.forEach(tab => {
        const selected = tab.dataset.cmsTab === name;
        tab.setAttribute('aria-selected', String(selected));
        tab.tabIndex = selected ? 0 : -1;
        if (selected && focus) tab.focus();
    });
    panels.forEach(panel => panel.hidden = panel.dataset.cmsPanel !== name);
    try { sessionStorage.setItem('cms-settings-tab', name); } catch (_) {}
}
if (tabs.length) {
    const saved = new URLSearchParams(location.search).get('tab');
    let preferred = saved;
    if (!preferred) {
        try { preferred = sessionStorage.getItem('cms-settings-tab'); } catch (_) {}
    }
    activateTab(preferred || tabs[0].dataset.cmsTab);
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.cmsTab));
        tab.addEventListener('keydown', e => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
            e.preventDefault();
            const next = e.key === 'Home' ? 0 : e.key === 'End' ? tabs.length - 1
                : (index + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            activateTab(tabs[next].dataset.cmsTab, true);
        });
    });
}
