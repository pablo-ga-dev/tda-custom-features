(function () {
    'use strict';

    var config = window.tdaClientDataForm || {};
    var debugEnabled = true;

    var debugLog = function () {
        if (!debugEnabled || !window.console || typeof window.console.log !== 'function') {
            return;
        }

        var args = Array.prototype.slice.call(arguments);
        args.unshift('[TDA_CF_DEBUG]');
        window.console.log.apply(window.console, args);
    };

    var getVehicleEndpoint = function () {
        if (!config.endpoint || !config.vehicleId) {
            return '';
        }

        return String(config.endpoint).replace(/\/+$/, '') + '/' + String(config.vehicleId);
    };

    var getSubmitEndpoint = function () {
        if (config.submitEndpoint) {
            return String(config.submitEndpoint);
        }

        if (config.endpoint) {
            return String(config.endpoint).replace(/\/vehicles\/?$/, '/client-data-form/submit');
        }

        return '';
    };

    var parseApiResponse = function (response) {
        var contentType = (response.headers.get('content-type') || '').toLowerCase();
        debugLog('parseApiResponse', { status: response.status, contentType: contentType });

        if (contentType.indexOf('application/json') !== -1) {
            return response.json();
        }

        return response.text().then(function (text) {
            var isHtml = /^\s*</.test(text || '');
            debugLog('nonJsonResponsePreview', (text || '').slice(0, 260));
            if (isHtml) {
                throw new Error('El servidor devolvio HTML en lugar de JSON. Revisa la ruta REST y el nonce.');
            }

            throw new Error(text || 'Respuesta inesperada del servidor.');
        });
    };

    var fetchVehicleData = function () {
        var endpoint = getVehicleEndpoint();
        debugLog('fetchVehicleData:endpoint', endpoint);
        if (!endpoint) {
            return Promise.reject(new Error('Missing endpoint or vehicle id.'));
        }

        return fetch(endpoint, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': config.nonce || ''
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed with status ' + response.status);
            }
            return response.json();
        }).then(function (data) {
            window.tdaClientVehicleData = data;
            return data;
        });
    };

    var bindFormSubmit = function () {
        var form = document.querySelector('.tda-client-form__form');
        var submitEndpoint = getSubmitEndpoint();
        if (!form || !submitEndpoint) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            debugLog('formSubmit:start');

            var submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            var formData = new FormData(form);
            if (config.vehicleId && !formData.get('vehicle_id')) {
                formData.append('vehicle_id', String(config.vehicleId));
            }

            debugLog('formSubmit:vehicleId', config.vehicleId || null);

            if (window.tdaClientVehicleData) {
                formData.append('vehicle_payload', JSON.stringify(window.tdaClientVehicleData));
            }

            fetch(submitEndpoint, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': config.nonce || '',
                    'Accept': 'application/json'
                },
                body: formData,
                credentials: 'same-origin'
            })
                .then(function (response) {
                    debugLog('formSubmit:responseStatus', response.status);
                    return parseApiResponse(response).then(function (data) {
                        debugLog('formSubmit:responseData', data);
                        if (!response.ok) {
                            throw new Error(data && data.message ? data.message : 'No se pudo procesar el formulario.');
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (data && data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    window.location.reload();
                })
                .catch(function (error) {
                    window.alert(error.message || 'No se pudo procesar el formulario.');
                })
                .finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
        });
    };

    window.fetchVehicleData = fetchVehicleData;
    bindFormSubmit();

    if (config.vehicleId) {
        fetchVehicleData().catch(function () {
            // Keep the page stable when vehicle data cannot be loaded.
        });
    }
})();