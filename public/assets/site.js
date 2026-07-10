/*
 * Endless feed for the centre column.
 *
 * The brief asks for a "Тағы да" button. Scrolling near the end loads the
 * next page by itself, and the button is kept as the fallback: browsers with
 * no IntersectionObserver, and any page whose fetch failed.
 */
(function () {
    'use strict';

    var feed = document.getElementById('feed');
    var button = document.getElementById('load-more');
    var sentinel = document.getElementById('feed-sentinel');
    var status = document.getElementById('feed-status');

    if (!feed || !button) {
        return;
    }

    var page = 1;
    var loading = false;
    var exhausted = false;
    var observer = null;

    function setBusy(busy) {
        loading = busy;
        button.disabled = busy;

        if (status) {
            status.hidden = !busy;
        }
    }

    function detachObserver() {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
    }

    function stop() {
        exhausted = true;
        detachObserver();
        button.remove();

        if (status) {
            status.remove();
        }
    }

    function loadNext() {
        if (loading || exhausted) {
            return;
        }

        setBusy(true);

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
                    stop();
                    return;
                }

                feed.insertAdjacentHTML('beforeend', markup);
                page += 1;
                setBusy(false);

                /*
                 * A short page can leave the sentinel still on screen, and the
                 * observer only fires on a crossing. Nudge it once more.
                 */
                if (observer && sentinel && isNearViewport(sentinel)) {
                    loadNext();
                }
            })
            .catch(function () {
                /*
                 * Stop watching, or every scroll would hammer a failing
                 * endpoint. The reader retries by hand instead.
                 */
                setBusy(false);
                detachObserver();
                button.hidden = false;
            });
    }

    function isNearViewport(element) {
        return element.getBoundingClientRect().top <= window.innerHeight + 600;
    }

    button.addEventListener('click', loadNext);

    if ('IntersectionObserver' in window && sentinel) {
        button.hidden = true;

        observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                loadNext();
            }
        }, { rootMargin: '600px 0px' });

        observer.observe(sentinel);
    }
})();
