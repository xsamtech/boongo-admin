(() => {
    'use strict';
    async function request(url, data, {method = 'POST', csrf, labels, fetcher = globalThis.fetch, logger = globalThis.console} = {}) {
        let response;
        try {
            response = await fetcher(url, {method, credentials:'same-origin', headers:{
                'X-CSRF-TOKEN':csrf, 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest',
            }, body:data});
        } catch (cause) {
            logger.error('[Work request failed]', {url, method, reason:'network', error:cause});
            throw new Error(labels.network_error, {cause});
        }
        let raw;
        try {raw = await response.text();} catch (cause) {
            logger.error('[Work request failed]', {url, method, status:response.status, reason:'response-interrupted', error:cause});
            throw new Error(labels.network_error, {cause});
        }
        let payload;
        try {payload = JSON.parse(raw);} catch (_) {payload = null;}
        const valid = payload && typeof payload === 'object' && !Array.isArray(payload);
        if (response.ok && !response.redirected && valid && payload.success !== false) return payload;

        const statusMessages = {
            401:labels.session_expired, 419:labels.session_expired, 403:labels.access_denied,
            404:labels.route_missing, 405:labels.route_missing, 413:labels.upload_too_large,
            500:labels.server_error, 502:labels.server_error, 503:labels.server_error, 504:labels.server_timeout,
        };
        let message = response.redirected ? labels.session_expired : (statusMessages[response.status] || labels.unexpected_response);
        if (!response.redirected && valid && response.status === 422 && payload.errors) {
            message = Object.values(payload.errors).flat().join('\n');
        } else if (!response.redirected && valid && payload.message && response.status !== 401 && response.status !== 419) {
            // Production exceptions use the server's generic message and reference;
            // never copy HTML error pages into the modal.
            message = response.status >= 500 && !payload.reference ? (statusMessages[response.status] || labels.server_error) : payload.message;
        }
        const reference = valid && payload.reference ? String(payload.reference) : null;
        logger.error('[Work request failed]', {
            url, method, status:response.status, statusText:response.statusText,
            redirected:response.redirected, finalUrl:response.url,
            contentType:response.headers.get('content-type'), reference,
            responsePreview: raw.slice(0, 2000),
        });
        throw new Error(`${message || labels.error} (HTTP ${response.status})${reference ? ` — ${labels.error_reference}: ${reference}` : ''}`);
    }
    globalThis.WorkRequests = {request};
})();
