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
