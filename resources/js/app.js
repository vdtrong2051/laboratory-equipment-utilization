const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

window.appFetch = async function appFetch(url, options = {}) {
    return fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            ...(options.headers || {}),
        },
    });
};
