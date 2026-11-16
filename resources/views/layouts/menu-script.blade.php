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
    // The GET form still works without JavaScript; AJAX only enhances suggestions.
    const searchForm = document.querySelector('.fg-nav-search[data-suggest-url]');
    const searchInput = searchForm?.querySelector('input[name="search"]');
    const searchList = searchForm?.querySelector('.fg-search-results');
    const nav = document.querySelector('.fg-nav');
    const searchToggle = nav?.querySelector('.fg-search-toggle');
    const compactSearch = window.matchMedia('(max-width: 1200px)');

    const closeCompactSearch = (restoreFocus = false) => {
        if (!searchForm || !searchToggle || !searchForm.classList.contains('is-open')) return;
        searchForm.classList.remove('is-open');
        searchToggle.setAttribute('aria-expanded', 'false');
        searchToggle.setAttribute('aria-label', 'Open game search');
        if (restoreFocus && compactSearch.matches) searchToggle.focus();
    };

    if (nav && searchForm && searchInput && searchToggle) {
        nav.classList.add('fg-search-enhanced');
        searchToggle.addEventListener('click', () => {
            const opening = !searchForm.classList.contains('is-open');
            searchForm.classList.toggle('is-open', opening);
            searchToggle.setAttribute('aria-expanded', String(opening));
            searchToggle.setAttribute('aria-label', opening ? 'Close game search' : 'Open game search');
            if (opening) {
                menu?.classList.remove('is-open');
                menuToggle?.setAttribute('aria-expanded', 'false');
                if (menuToggle) menuToggle.textContent = 'Menu';
                searchInput.focus();
            } else {
                searchToggle.focus();
            }
        });
        menuToggle?.addEventListener('click', () => closeCompactSearch());
        document.addEventListener('click', event => {
            if (!searchForm.contains(event.target) && !searchToggle.contains(event.target)) {
                closeCompactSearch();
            }
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && searchForm.classList.contains('is-open')) {
                event.preventDefault();
                closeCompactSearch(true);
            }
        });
        compactSearch.addEventListener('change', event => {
            if (!event.matches) closeCompactSearch();
        });
    }

    if (searchForm && searchInput && searchList) {
        let debounce;
        let inFlight;
        let requestId = 0;
        let options = [];
        let selected = -1;

        const closeResults = () => {
            searchList.hidden = true;
            searchList.replaceChildren();
            searchInput.setAttribute('aria-expanded', 'false');
            searchInput.removeAttribute('aria-activedescendant');
            options = [];
            selected = -1;
        };

        const setActive = index => {
            selected = index;
            options.forEach((option, i) => {
                option.classList.toggle('is-selected', i === index);
                option.setAttribute('aria-selected', String(i === index));
            });
            if (index >= 0) {
                searchInput.setAttribute('aria-activedescendant', options[index].id);
                options[index].scrollIntoView({block: 'nearest'});
            } else {
                searchInput.removeAttribute('aria-activedescendant');
            }
        };

        const showMessage = message => {
            searchList.replaceChildren();
            const item = document.createElement('div');
            item.className = 'fg-search-feedback';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-disabled', 'true');
            item.textContent = message;
            searchList.append(item);
            searchList.hidden = false;
            searchInput.setAttribute('aria-expanded', 'true');
            options = [];
            selected = -1;
            searchInput.removeAttribute('aria-activedescendant');
        };

        const renderResults = games => {
            searchList.replaceChildren();
            options = [];
            selected = -1;
            searchInput.removeAttribute('aria-activedescendant');

            if (games.length === 0) {
                showMessage('No matching games. Press Enter to search the catalog.');
                return;
            }

            games.forEach((game, index) => {
                const item = document.createElement('a');
                item.className = 'fg-search-option';
                item.id = 'fg-search-option-' + index;
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', 'false');
                item.href = game.url;

                if (game.image && game.image.startsWith('https://')) {
                    const thumbnail = document.createElement('img');
                    thumbnail.src = game.image;
                    thumbnail.alt = '';
                    thumbnail.loading = 'lazy';
                    item.append(thumbnail);
                } else {
                    const fallback = document.createElement('span');
                    fallback.className = 'fg-search-thumb';
                    fallback.setAttribute('aria-hidden', 'true');
                    item.append(fallback);
                }

                const description = document.createElement('span');
                description.className = 'fg-search-copy';
                const name = document.createElement('span');
                name.className = 'fg-search-name';
                name.textContent = game.name;
                const category = document.createElement('span');
                category.className = 'fg-search-category';
                category.textContent = game.category;
                description.append(name, category);
                item.append(description);

                item.addEventListener('mouseenter', () => setActive(index));
                options.push(item);
                searchList.append(item);
            });

            searchList.hidden = false;
            searchInput.setAttribute('aria-expanded', 'true');
        };

        const scheduleSearch = () => {
            clearTimeout(debounce);
            inFlight?.abort();
            const current = ++requestId;
            const query = searchInput.value.trim();

            if (query.length < 2) {
                closeResults();
                return;
            }

            showMessage('Searching games…');
            debounce = setTimeout(async () => {
                const controller = new AbortController();
                inFlight = controller;
                const url = new URL(searchForm.dataset.suggestUrl, window.location.origin);
                url.searchParams.set('q', query);

                try {
                    const response = await fetch(url, {
                        headers: {Accept: 'application/json'},
                        credentials: 'same-origin',
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error('Search request failed');
                    const payload = await response.json();
                    if (!Array.isArray(payload.data)) throw new Error('Invalid search results');
                    if (current === requestId) renderResults(payload.data);
                } catch (error) {
                    if (error.name !== 'AbortError' && current === requestId) {
                        showMessage('Search is unavailable. Press Enter to view all results.');
                    }
                } finally {
                    if (inFlight === controller) inFlight = null;
                }
            }, 260);
        };

        searchInput.addEventListener('input', event => {
            if (!event.isComposing) scheduleSearch();
        });
        searchInput.addEventListener('compositionend', scheduleSearch);
        searchInput.addEventListener('focus', () => {
            if (searchList.hidden && searchInput.value.trim().length >= 2) scheduleSearch();
        });
        searchInput.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !searchList.hidden) {
                event.preventDefault();
                clearTimeout(debounce);
                inFlight?.abort();
                ++requestId;
                closeResults();
            } else if (options.length && !searchList.hidden && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
                event.preventDefault();
                setActive(event.key === 'ArrowDown'
                    ? (selected + 1) % options.length
                    : (selected <= 0 ? options.length - 1 : selected - 1));
            } else if (event.key === 'Enter' && selected >= 0 && !searchList.hidden) {
                event.preventDefault();
                window.location.assign(options[selected].href);
            }
        });
        document.addEventListener('click', event => {
            if (!searchForm.contains(event.target)) {
                clearTimeout(debounce);
                inFlight?.abort();
                ++requestId;
                closeResults();
            }
        });
    }

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
