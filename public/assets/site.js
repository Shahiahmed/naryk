/*
 * Burger menu. The nav is plain markup and works with JS off; this only
 * toggles it on narrow screens, where CSS hides it behind the button.
 */
(function () {
    'use strict';

    var burger = document.getElementById('burger');
    var menu = document.getElementById('site-menu');

    if (!burger || !menu) {
        return;
    }

    function setOpen(open) {
        menu.classList.toggle('site-menu--open', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    burger.addEventListener('click', function () {
        setOpen(burger.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && burger.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            burger.focus();
        }
    });

    // Tapping outside closes it, the way a drawer should behave.
    document.addEventListener('click', function (event) {
        if (burger.getAttribute('aria-expanded') !== 'true') {
            return;
        }
        if (!menu.contains(event.target) && !burger.contains(event.target)) {
            setOpen(false);
        }
    });

    /*
     * Widening past the breakpoint reveals the menu through CSS anyway; drop
     * the open state so the button does not come back mid-cross.
     */
    window.addEventListener('resize', function () {
        if (window.innerWidth > 800) {
            setOpen(false);
        }
    });
})();

/*
 * "Тағы да" — appends the next 15 cards into the centre column.
 * The old site auto-loaded on scroll; the brief asks for a button.
 */
(function () {
    'use strict';

    var button = document.getElementById('load-more');
    var feed = document.getElementById('feed');

    if (!button || !feed) {
        return;
    }

    var page = 1;
    var loading = false;

    button.addEventListener('click', function () {
        if (loading) {
            return;
        }

        loading = true;
        button.disabled = true;
        var label = button.textContent;
        button.textContent = 'Жүктелуде…';

        fetch('/feed?page=' + (page + 1), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                var markup = html.trim();

                // An empty slice means we have reached the last page.
                if (!markup) {
                    button.remove();
                    return;
                }

                feed.insertAdjacentHTML('beforeend', markup);
                page += 1;
                button.disabled = false;
                button.textContent = label;
            })
            .catch(function () {
                button.disabled = false;
                button.textContent = label;
            })
            .finally(function () {
                loading = false;
            });
    });
})();
