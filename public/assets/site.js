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
 * Point 4 (brief B): the phone drawer's search icon floats the field over the
 * page as a modal, so the row itself — БІЗ ТУРАЛЫ, icon, socials — stays on one
 * line. Dismissed by ×, Escape, or a tap on the dimmed backdrop.
 */
(function () {
    'use strict';

    var toggle = document.getElementById('menu-search-toggle');
    var overlay = document.getElementById('menu-search');

    if (!toggle || !overlay) {
        return;
    }

    var input = overlay.querySelector('.menu-search__input');
    var close = document.getElementById('menu-search-close');

    function setOpen(open) {
        overlay.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open && input) {
            input.focus();
        }
    }

    toggle.addEventListener('click', function () {
        setOpen(overlay.hidden);
    });

    if (close) {
        close.addEventListener('click', function () {
            setOpen(false);
            toggle.focus();
        });
    }

    // A tap on the dimmed backdrop, clear of the field, closes it.
    overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !overlay.hidden) {
            setOpen(false);
            toggle.focus();
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

/*
 * Embedded pages of our own (data-autoheight, set by App\Support\Embeds) grow
 * to fit their content, so the reader scrolls the article, not a small box.
 * Same-origin only; anything else keeps the CSS height.
 */
(function () {
    'use strict';

    var frames = document.querySelectorAll('iframe[data-autoheight]');

    if (!frames.length) {
        return;
    }

    function contentHeight(doc) {
        var height = Math.max(doc.documentElement.scrollHeight, doc.body ? doc.body.scrollHeight : 0);

        // Some pages (PDFs converted by pdf2htmlEX) scroll an inner box
        // instead of the document, so the document alone reports one screen.
        if (doc.body) {
            Array.prototype.forEach.call(doc.body.children, function (child) {
                height = Math.max(height, child.scrollHeight);
            });
        }

        return height;
    }

    function fit(frame) {
        var doc;

        try {
            doc = frame.contentDocument;
        } catch (error) {
            return;
        }

        if (!doc || !doc.documentElement) {
            return;
        }

        var height = contentHeight(doc);

        if (height > 0) {
            frame.style.height = height + 'px';
        }
    }

    Array.prototype.forEach.call(frames, function (frame) {
        frame.addEventListener('load', function () {
            fit(frame);
            // Fonts and images inside arrive after load.
            setTimeout(function () { fit(frame); }, 1000);
        });

        if (window.ResizeObserver) {
            var width = 0;
            new ResizeObserver(function (entries) {
                var current = entries[0].contentRect.width;
                if (current !== width) {
                    width = current;
                    fit(frame);
                }
            }).observe(frame);
        }
    });
})();
