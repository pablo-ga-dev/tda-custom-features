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

        if (!input || !status || !results) {
            return;
        }

        var page = 1;
        var hasMore = false;
        var query = '';
        var isLoading = false;
        var renderedCount = 0;

        var setStatus = function (text) {
            status.textContent = text || '';
        };

        var removeBottomLoading = function () {
            var loadingItem = results.querySelector('.tda-search__loading');
            if (loadingItem) {
                loadingItem.remove();
            }
        };

        var setBottomLoading = function (isVisible) {
            removeBottomLoading();

            if (!isVisible) {
                return;
            }

            var loadingItem = document.createElement('li');
            loadingItem.className = 'tda-search__loading';
            loadingItem.textContent = 'Cargando...';
            results.appendChild(loadingItem);
        };

        var renderItems = function (items, append) {
            if (!append) {
                results.innerHTML = '';
                renderedCount = 0;
            }

            removeBottomLoading();

            if (!items.length && !append) {
                results.innerHTML = '<li class="tda-search__empty">No hay coincidencias</li>';
                return;
            }

            items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'tda-search__item';
                var title = escapeHtml(item.title || 'Ver vehiculo');
                var url = typeof item.url === 'string' ? item.url : '';
                li.innerHTML = url
                    ? '<a class="tda-search__item-link" href="' + escapeHtml(url) + '">' + title + '</a>'
                    : '<span class="tda-search__item-link tda-search__item-link--disabled">' + title + '</span>';
                results.appendChild(li);
                renderedCount += 1;
            });
        };

        var loadNextPage = function () {
            if (!hasMore || isLoading || !query) {
                return;
            }

            page += 1;
            fetchVehicles(true);
        };

        var maybeLoadUntilScrollable = function () {
            if (!hasMore || isLoading || !query) {
                return;
            }

            if (results.scrollHeight <= results.clientHeight + 8) {
                loadNextPage();
            }
        };

        var resetResults = function () {
            results.innerHTML = '';
            hasMore = false;
            renderedCount = 0;
            setStatus('');
        };

        var fetchVehicles = function (append) {
            if (isLoading) {
                return;
            }

            isLoading = true;
            setBottomLoading(append);
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
                    hasMore = false;
                    setStatus('No se pudo completar la busqueda');
                })
                .finally(function () {
                    isLoading = false;
                    setBottomLoading(false);
                    maybeLoadUntilScrollable();
                });
        };

        var onInput = debounce(function (value) {
            query = value.trim();
            page = 1;

            if (!query) {
                resetResults();
                return;
            }

            fetchVehicles(false);
        }, 250);

        input.addEventListener('input', function (event) {
            onInput(event.target.value || '');
        });

        results.addEventListener('scroll', function () {
            if (!hasMore || isLoading || !query) {
                return;
            }

            var threshold = 24;
            var distanceToBottom = results.scrollHeight - (results.scrollTop + results.clientHeight);
            if (distanceToBottom <= threshold) {
                loadNextPage();
            }
        });

        resetResults();
    });
})();