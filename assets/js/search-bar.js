(function () {
    'use strict';

    var config = window.tdaSearchBar || {};
    if (!config.endpoint) {
        return;
    }

    var roots = document.querySelectorAll('[data-tda-search-root]');
    if (!roots.length) {
        return;
    }

    var debounce = function (fn, wait) {
        var timer;
        return function () {
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(null, args);
            }, wait);
        };
    };

    var escapeHtml = function (value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    roots.forEach(function (root) {
        var input = root.querySelector('[data-tda-search-input]');
        var status = root.querySelector('[data-tda-search-status]');
        var results = root.querySelector('[data-tda-search-results]');
        var moreButton = root.querySelector('[data-tda-search-more]');

        if (!input || !status || !results || !moreButton) {
            return;
        }

        var page = 1;
        var hasMore = false;
        var query = '';
        var isLoading = false;

        var setStatus = function (text) {
            status.textContent = text || '';
        };

        var renderItems = function (items, append) {
            if (!append) {
                results.innerHTML = '';
            }

            if (!items.length && !append) {
                results.innerHTML = '<li class="tda-search__empty">No hay coincidencias</li>';
                return;
            }

            items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'tda-search__item';
                var title = escapeHtml(item.title || '');
                var vehicleName = escapeHtml(((item.marca || '') + ' ' + (item.modelo || '')).trim());
                var url = typeof item.url === 'string' ? item.url : '';
                var titleMarkup = url
                    ? '<a class="tda-search__link" href="' + escapeHtml(url) + '">' + title + '</a>'
                    : '<span>' + title + '</span>';
                li.innerHTML =
                    '<strong>' + vehicleName + '</strong>' +
                    titleMarkup;
                results.appendChild(li);
            });
        };

        var setMoreVisibility = function () {
            moreButton.hidden = !hasMore || isLoading;
        };

        var fetchVehicles = function (append) {
            if (isLoading) {
                return;
            }

            isLoading = true;
            setMoreVisibility();
            setStatus('Buscando...');

            var url = new URL(config.endpoint, window.location.origin);
            url.searchParams.set('q', query);
            url.searchParams.set('page', String(page));
            url.searchParams.set('per_page', '20');

            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': config.nonce || ''
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Request failed');
                    }
                    return response.json();
                })
                .then(function (data) {
                    var items = Array.isArray(data.items) ? data.items : [];
                    hasMore = Boolean(data.has_more);
                    renderItems(items, append);
                    setStatus(items.length ? items.length + ' resultados' : 'Sin resultados');
                })
                .catch(function () {
                    setStatus('No se pudo completar la busqueda');
                })
                .finally(function () {
                    isLoading = false;
                    setMoreVisibility();
                });
        };

        var onInput = debounce(function (value) {
            query = value.trim();
            page = 1;
            fetchVehicles(false);
        }, 250);

        input.addEventListener('input', function (event) {
            onInput(event.target.value || '');
        });

        moreButton.addEventListener('click', function () {
            if (!hasMore || isLoading) {
                return;
            }
            page += 1;
            fetchVehicles(true);
        });

        fetchVehicles(false);
    });
})();