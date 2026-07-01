document.addEventListener('DOMContentLoaded', function () {
    const searchBar = document.querySelector('.tda-search');
    if (!searchBar) {
        return;
    }

    const input = searchBar.querySelector('[data-tda-search-input]');
    const resetButton = searchBar.querySelector('.tda-search__reset');
    const results = searchBar.querySelector('[data-tda-search-results]');
    const status = searchBar.querySelector('[data-tda-search-status]');

    const animeRuntime = window.anime;

    const animate = function (target, params) {
        if (animeRuntime && typeof animeRuntime.animate === 'function') {
            return animeRuntime.animate(target, params);
        }

        if (typeof animeRuntime === 'function') {
            return animeRuntime(Object.assign({ targets: target }, params));
        }

        return null;
    };

    if (!input) {
        return;
    }

    let ignoreOutsideClickUntil = 0;
    let spacer = null;
    let isActive = false;
    let travelState = null;

    const ACTIVE_STATUS_TEXT = 'Introduce un vehículo para mostrar resultados';
    const ACTIVE_Z_INDEX = '9999';
    const START_DELAY_MS = 250;
    const MOVE_DURATION_MS = 520;
    const STATUS_FADE_DURATION_MS = 1000;

    const removeSpacer = function () {
        if (spacer && spacer.parentNode) {
            spacer.parentNode.removeChild(spacer);
        }
        spacer = null;
    };

    const createSpacer = function (height) {
        removeSpacer();
        spacer = document.createElement('div');
        spacer.style.height = Math.max(1, Math.round(height)) + 'px';
        searchBar.parentNode.insertBefore(spacer, searchBar.nextSibling);
    };

    const clearFloatingStyles = function () {
        searchBar.style.top = '';
        searchBar.style.left = '';
        searchBar.style.position = '';
        searchBar.style.zIndex = '';
    };

    const clearStatusStyles = function () {
        if (!status) {
            return;
        }

        status.style.display = '';
        status.style.opacity = '';
    };

    const setActiveStatus = function () {
        if (!status) {
            return;
        }

        status.textContent = ACTIVE_STATUS_TEXT;
        animate(status, {
            display: 'block',
            opacity: [0, 1],
            duration: STATUS_FADE_DURATION_MS,
            easing: 'linear'
        });
    };

    const setInactiveStatus = function () {
        if (!status) {
            return;
        }

        status.textContent = ACTIVE_STATUS_TEXT;
        animate(status, {
            display: 'none',
            opacity: [1, 0],
            duration: STATUS_FADE_DURATION_MS,
            easing: 'linear'
        });
    };


    const activateSearch = function () {
        if (isActive) {
            ignoreOutsideClickUntil = Date.now() + START_DELAY_MS;
            return;
        }

        const rect = searchBar.getBoundingClientRect();
        const targetWidth = Math.min(760, Math.round(window.innerWidth * 0.92));
        const targetLeft = Math.max(8, Math.round((window.innerWidth - targetWidth) / 2));
        const targetTop = Math.round(window.innerHeight * 0.25);

        travelState = {
            startTop: rect.top,
            startLeft: rect.left,
            startWidth: rect.width,
            targetTop: targetTop,
            targetLeft: targetLeft,
            targetWidth: targetWidth,
            deltaTop: targetTop - rect.top,
            deltaLeft: targetLeft - rect.left,
        };

        createSpacer(rect.height);

        searchBar.style.position = 'fixed';
        searchBar.style.top = rect.top + 'px';
        searchBar.style.left = rect.left + 'px';
        searchBar.style.width = rect.width + 'px';
        searchBar.style.margin = '0';
        searchBar.style.zIndex = ACTIVE_Z_INDEX;

        if (animeRuntime) {
            animate('.tda-search__bar-wrapper', {
                height: '60px',
                duration: MOVE_DURATION_MS,
                easing: 'linear'
            });
            animate(searchBar, {
                top: targetTop + 'px',
                left: targetLeft + 'px',
                width: targetWidth + 'px',
                duration: MOVE_DURATION_MS,
                easing: 'linear',
                onBegin: function () {
                    animate(searchBar, {
                        background: '#fff',
                        zIndex: 9999,
                        duration: 200,
                        easing: 'linear'
                    });
                    animate(searchBar, {
                        width: '760px',
                        maxWidth: '90vw',
                        borderRadius: '30px',
                        boxShadow: '0 2px 10px -3px rgba(0, 0, 0, 0.2)',
                        duration: MOVE_DURATION_MS,
                        easing: 'linear'
                    });
                    setActiveStatus();
                }
            });
        } else {
            searchBar.style.top = targetTop + 'px';
            searchBar.style.left = targetLeft + 'px';
            searchBar.style.width = targetWidth + 'px';
            setActiveStatus();
        }

        isActive = true;
        ignoreOutsideClickUntil = Date.now() + START_DELAY_MS;
    };

    const deactivateSearch = function () {
        if (!isActive) {
            return;
        }

        const completeDeactivation = function () {
            clearStatusStyles();
            removeSpacer();
            isActive = false;
            travelState = null;
            clearFloatingStyles();
        };

        if (!animeRuntime || !travelState) {
            completeDeactivation();
            return;
        }

        animate('.tda-search__bar-wrapper', {
            height: '40px',
            duration: MOVE_DURATION_MS,
            easing: 'linear'
        });

        animate(searchBar, {
            top: travelState.startTop + 'px',
            left: travelState.startLeft + 'px',
            width: travelState.startWidth + 'px',
            duration: MOVE_DURATION_MS,
            easing: 'linear',
            onBegin: function () {
                animate(searchBar, {
                    background: '',
                    zIndex: '',
                    boxShadow: '',
                    borderRadius: '',
                    boxShadow: '',
                    duration: 200,
                    easing: 'ease-in-out'
                });
                setInactiveStatus();
            },
            onComplete: completeDeactivation
        });
    };

    const resetResults = function () {
        if (results) {
            results.innerHTML = '';
        }

        if (status) {
            status.textContent = ACTIVE_STATUS_TEXT;
        }
    };

    input.addEventListener('focus', function () {
        activateSearch();
    });

    input.addEventListener('click', function () {
        activateSearch();
    });

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            input.value = '';
            resetResults();
        });
    }

    document.addEventListener('click', function (event) {
        if (Date.now() < ignoreOutsideClickUntil) {
            return;
        }

        if (!searchBar.contains(event.target)) {
            input.value = '';
            resetResults();
            deactivateSearch();
        }
    });
});
