const DEFAULT_HEADERS = {
    Accept: 'application/json',
};

function buildUrl(url, query = {}) {
    const target = new URL(url, window.location.origin);

    Object.entries(query).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') {
            return;
        }

        target.searchParams.set(key, String(value));
    });

    return target.toString();
}

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            ...DEFAULT_HEADERS,
            ...(options.headers || {}),
        },
        ...options,
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok) {
        const message = payload?.error?.message || 'Request failed.';
        const error = new Error(message);
        error.status = response.status;
        error.code = payload?.error?.code || 'http_error';
        error.details = payload?.error?.details || {};

        SpawnToast('Error', message, 'error', undefined, false);

        throw error;
    }

    if (!payload || payload.ok !== true) {
        throw new Error(payload?.error?.message || 'Invalid API response.');
    }

    return payload;
}

export async function apiGet(url, query = {}) {
    return requestJson(buildUrl(url, query), {
        method: 'GET',
    });
}

export async function apiPost(url, body = {}) {
    console.log('apiPost', url, body);
    return requestJson(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    });
}
