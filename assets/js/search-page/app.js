(function () {
    'use strict';

    var config = window.tdaSearchPage || {};
    if (!config.endpoint) {
        return;
    }

    var roots = document.querySelectorAll('[data-tda-search-page-root]');
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
        var input = root.querySelector('[data-tda-search-page-input]');
        var status = root.querySelector('[data-tda-search-page-status]');
        var results = root.querySelector('[data-tda-search-page-results]');
        var pagination = root.querySelector('[data-tda-search-page-pagination]');
        var pageLabel = root.querySelector('[data-tda-search-page-label]');
        var sentinel = root.querySelector('[data-tda-search-page-sentinel]');
        var resetButton = root.querySelector('.tda-search-page__reset');

        if (!input || !status || !results || !pagination || !pageLabel || !sentinel) {
            return;
        }

        var page = 1;
        var hasMore = false;
        var query = '';
        var isLoading = false;
        var renderedCount = 0;
        var observer = null;
        var perPage = parseInt(root.getAttribute('data-tda-search-page-per-page') || '12', 10);

        if (!perPage || perPage < 1) {
            perPage = 12;
        }

        var setStatus = function (text) {
            status.textContent = text || '';
        };

        var setPaginationState = function () {
            if (!renderedCount && !isLoading) {
                pageLabel.textContent = '';
                pagination.hidden = true;
                return;
            }

            pageLabel.textContent = isLoading && page > 1
                ? 'Cargando más vehículos...'
                : 'Mostrando ' + String(renderedCount) + ' vehículos';
            pagination.hidden = !hasMore && !isLoading;
        };

        var buildMetaItems = function (item) {
            var values = [item.marca, item.modelo, item.combustible, item.potencia, item.carroceria];

            return values
                .filter(function (value) {
                    return Boolean(value);
                })
                .map(function (value) {
                    return '<span class="tda-search-page__meta-item">' + escapeHtml(value) + '</span>';
                })
                .join('');
        };

        var renderItems = function (items, append) {
            if (!append) {
                results.innerHTML = '';
                renderedCount = 0;
            }

            if (!items.length && !append) {
                results.innerHTML = '<div class="tda-search-page__empty">No hay coincidencias para esta búsqueda.</div>';
                setPaginationState();
                return;
            }

            if (!items.length) {
                setPaginationState();
                return;
            }

            var markup = items.map(function (item) {
                var title = escapeHtml(item.title || 'Ver vehículo');
                var url = typeof item.url === 'string' ? item.url : '';
                var imageUrl = item.image && typeof item.image.url === 'string' ? item.image.url : '';
                var imageAlt = item.image && typeof item.image.alt === 'string' ? item.image.alt : (item.title || 'Vehículo');
                var metaMarkup = buildMetaItems(item);
                var imageMarkup = imageUrl
                    ? '<div class="tda-search-page__media"><img class="tda-search-page__image" src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(imageAlt) + '" loading="lazy" /></div>'
                    : '';
                var headingMarkup = url
                    ? '<h3 class="tda-search-page__card-title"><a class="tda-search-page__card-link" href="' + escapeHtml(url) + '">' + title + '</a></h3>'
                    : '<h3 class="tda-search-page__card-title">' + title + '</h3>';
                var footerMarkup = url
                    ? '<div class="tda-search-page__footer"><a class="tda-search-page__details-link" href="' + escapeHtml(url) + '">Ver vehículo</a></div>'
                    : '';

                return '' +
                    '<article class="tda-search-page__card">' +
                    imageMarkup +
                    headingMarkup +
                    (metaMarkup ? '<div class="tda-search-page__meta">' + metaMarkup + '</div>' : '') +
                    footerMarkup +
                    '</article>';
            }).join('');

            if (append) {
                results.insertAdjacentHTML('beforeend', markup);
            } else {
                results.innerHTML = markup;
            }

            renderedCount += items.length;
            setPaginationState();
        };

        var renderLoading = function (append) {
            if (append) {
                setPaginationState();
                return;
            }

            results.innerHTML = '<div class="tda-search-page__loading">Cargando vehículos...</div>';
            setPaginationState();
        };

        var renderError = function () {
            results.innerHTML = '<div class="tda-search-page__error">No se pudo completar la búsqueda.</div>';
            renderedCount = 0;
            setPaginationState();
        };

        var fetchVehicles = function (append) {
            if (isLoading) {
                return;
            }

            isLoading = true;
            setStatus(append ? 'Cargando más vehículos...' : 'Buscando...');
            renderLoading(append);
            setPaginationState();

            var url = new URL(config.endpoint, window.location.origin);
            url.searchParams.set('q', query);
            url.searchParams.set('page', String(page));
            url.searchParams.set('per_page', String(perPage));

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
                    setStatus(renderedCount ? 'Mostrando ' + String(renderedCount) + ' vehículos' : 'Sin resultados');
                })
                .catch(function () {
                    hasMore = false;
                    setStatus('No se pudo completar la búsqueda');
                    if (append) {
                        page -= 1;
                        setPaginationState();
                    } else {
                        renderError();
                    }
                })
                .finally(function () {
                    isLoading = false;
                    setPaginationState();
                });
        };

        var loadNextPage = function () {
            if (!hasMore || isLoading) {
                return;
            }

            page += 1;
            fetchVehicles(true);
        };

        var bindInfiniteScroll = function () {
            if (observer) {
                observer.disconnect();
            }

            observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadNextPage();
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px 320px 0px',
                threshold: 0
            });

            observer.observe(sentinel);
        };

        var onInput = debounce(function (value) {
            query = value.trim();
            page = 1;
            fetchVehicles(false);
        }, 250);

        input.addEventListener('input', function (event) {
            onInput(event.target.value || '');
        });

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                input.value = '';
                query = '';
                page = 1;
                fetchVehicles(false);
            });
        }

        bindInfiniteScroll();
        setPaginationState();
        fetchVehicles(false);
    });
})();