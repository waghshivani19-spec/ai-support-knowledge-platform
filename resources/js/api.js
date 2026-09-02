import './bootstrap';

export function getToken() {
    let token = localStorage.getItem('api_token');
    if (!token && window.API_TOKEN) {
        token = window.API_TOKEN;
        try { localStorage.setItem('api_token', token); } catch {}
    }
    return token;
}

export function setToken(token) {
    if (token) {
        localStorage.setItem('api_token', token);
    } else {
        localStorage.removeItem('api_token');
    }
}

export function getUser() {
    const raw = localStorage.getItem('api_user');
    try {
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

export function setUser(user) {
    if (user) {
        localStorage.setItem('api_user', JSON.stringify(user));
    } else {
        localStorage.removeItem('api_user');
    }
}

export function clearAuth() {
    localStorage.removeItem('api_token');
    localStorage.removeItem('api_user');
}

export async function apiFetch(path, { method = 'GET', body, headers = {}, isForm = false } = {}) {
    const token = getToken();

    const opts = {
        method,
        headers: {
            Accept: 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...headers,
        },
        credentials: 'same-origin',
    };

    if (body !== undefined) {
        if (isForm) {
            opts.body = body;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
    }

    const base = window.API_BASE || '/api';
    const url = path.startsWith('http') ? path : `${base}/${path.replace(/^\/+/, '')}`;

    const res = await fetch(url, opts);
    let data = null;

    try {
        data = await res.json();
    } catch {
        data = null;
    }

    if (!res.ok) {
        const message =
            (data && (data.message || data.error)) ||
            `Request failed with status ${res.status}`;
        const error = new Error(message);
        error.status = res.status;
        error.data = data;
        if (res.status === 401) {
            clearAuth();
        }
        throw error;
    }

    return data;
}

export function showToast(message, type = 'info', timeout = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) {
        alert(message);
        return;
    }

    const colors = {
        success: 'border-emerald-500 bg-emerald-50 text-emerald-800',
        error: 'border-rose-500 bg-rose-50 text-rose-800',
        info: 'border-indigo-500 bg-indigo-50 text-indigo-800',
        warning: 'border-amber-500 bg-amber-50 text-amber-800',
    };

    const el = document.createElement('div');
    el.className = `border-l-4 px-4 py-3 rounded shadow-sm text-sm ${colors[type] || colors.info}`;
    el.textContent = message;
    container.appendChild(el);

    setTimeout(() => {
        el.classList.add('opacity-0', 'translate-x-2');
        setTimeout(() => el.remove(), 300);
    }, timeout);
}

export function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function formatDate(value) {
    if (!value) return '-';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return value;
    }
}

window.apiFetch = apiFetch;
window.showToast = showToast;
window.escapeHtml = escapeHtml;
window.formatDate = formatDate;
window.getToken = getToken;
window.setToken = setToken;
window.getUser = getUser;
window.setUser = setUser;
window.clearAuth = clearAuth;