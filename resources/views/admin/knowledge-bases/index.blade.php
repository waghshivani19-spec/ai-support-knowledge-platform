@extends('layouts.admin', [
    'title' => 'Knowledge Bases',
    'header' => 'Knowledge Bases',
    'subtitle' => 'Manage knowledge collections used by the AI chat.',
])

@section('actions')
    <a href="{{ route('admin.knowledge-bases.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md">
        + New Knowledge Base
    </a>
@endsection

@section('content')
    <div class="bg-white border border-slate-200 rounded-lg">
        <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap gap-3 items-center justify-between">
            <form id="filter-form" class="flex flex-wrap gap-2 items-center">
                <div class="relative">
                    <input id="search" type="search" name="search" placeholder="Search knowledge bases…"
                           class="w-64 rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <select id="active-filter" name="is_active"
                        class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button type="submit"
                        class="text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-md">
                    Filter
                </button>
            </form>

            <div class="text-xs text-slate-500" id="results-summary">—</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Name</th>
                        <th class="text-left px-5 py-3 font-medium">Embedding</th>
                        <th class="text-left px-5 py-3 font-medium">Documents</th>
                        <th class="text-left px-5 py-3 font-medium">Status</th>
                        <th class="text-left px-5 py-3 font-medium">Created</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="kb-tbody" class="divide-y divide-slate-100">
                    <tr><td colspan="6" class="px-5 py-6 text-center text-slate-500">Loading…</td></tr>
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-200 flex items-center justify-between text-sm">
            <div class="text-slate-500">
                Page <span id="current-page">1</span>
            </div>
            <div class="flex gap-2">
                <button id="prev-page" class="px-3 py-1.5 rounded border border-slate-300 disabled:opacity-40" disabled>Previous</button>
                <button id="next-page" class="px-3 py-1.5 rounded border border-slate-300 disabled:opacity-40" disabled>Next</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const { apiFetch, escapeHtml, formatDate, showToast } = window;

        let state = { page: 1, search: '', active: '' };

        const tbody = document.getElementById('kb-tbody');
        const summary = document.getElementById('results-summary');
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const pageLabel = document.getElementById('current-page');

        function buildQuery() {
            const params = new URLSearchParams();
            params.set('page', state.page);
            params.set('per_page', 15);
            if (state.search) params.set('search', state.search);
            if (state.active !== '') params.set('is_active', state.active);
            return params.toString();
        }

        function renderEmpty(message) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-6 text-center text-slate-500">${escapeHtml(message)}</td></tr>`;
        }

        async function load() {
            renderEmpty('Loading…');
            try {
                const data = await apiFetch(`knowledge-bases?${buildQuery()}`);
                const items = data?.data || data || [];
                const meta = data?.meta || { current_page: 1, last_page: 1, total: items.length };

                pageLabel.textContent = meta.current_page || 1;
                prevBtn.disabled = !(meta.current_page > 1);
                nextBtn.disabled = !(meta.current_page < meta.last_page);

                summary.textContent = `${meta.total ?? items.length} knowledge base(s)`;

                if (!items.length) {
                    renderEmpty('No knowledge bases found.');
                    return;
                }

                tbody.innerHTML = items.map((kb) => `
                    <tr>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900">${escapeHtml(kb.name)}</div>
                            <div class="text-xs text-slate-500 truncate max-w-xs">${escapeHtml(kb.description || '—')}</div>
                            <div class="text-xs text-slate-400 mt-0.5">slug: ${escapeHtml(kb.slug)}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-slate-700">${escapeHtml(kb.embedding?.provider || '—')}</div>
                            <div class="text-xs text-slate-500">${escapeHtml(kb.embedding?.model || '')}</div>
                            <div class="text-xs text-slate-400">chunk: ${kb.chunking?.size ?? '—'} / ${kb.chunking?.overlap ?? '—'}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-700">${kb.documents_count ?? 0}</td>
                        <td class="px-5 py-3">
                            ${kb.is_active
                                ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">Active</span>'
                                : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-700">Inactive</span>'}
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">${formatDate(kb.created_at)}</td>
                        <td class="px-5 py-3 text-right space-x-2">
                            <a href="/admin/knowledge-bases/${kb.id}/documents"
                               class="inline-flex items-center px-2.5 py-1 text-xs rounded border border-slate-300 hover:bg-slate-100">
                                Documents
                            </a>
                            <a href="/admin/knowledge-bases/${kb.id}/edit"
                               class="inline-flex items-center px-2.5 py-1 text-xs rounded border border-indigo-300 text-indigo-700 hover:bg-indigo-50">
                                Edit
                            </a>
                            <button data-id="${kb.id}" data-name="${escapeHtml(kb.name)}"
                                    class="kb-delete inline-flex items-center px-2.5 py-1 text-xs rounded border border-rose-300 text-rose-700 hover:bg-rose-50">
                                Delete
                            </button>
                        </td>
                    </tr>
                `).join('');

                tbody.querySelectorAll('.kb-delete').forEach((btn) => {
                    btn.addEventListener('click', () => handleDelete(btn.dataset.id, btn.dataset.name));
                });
            } catch (err) {
                renderEmpty('Failed to load: ' + err.message);
            }
        }

        async function handleDelete(id, name) {
            if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
            try {
                await apiFetch(`knowledge-bases/${id}`, { method: 'DELETE' });
                showToast('Knowledge base deleted.', 'success');
                load();
            } catch (err) {
                showToast(err.message, 'error');
            }
        }

        document.getElementById('filter-form').addEventListener('submit', (e) => {
            e.preventDefault();
            state.search = document.getElementById('search').value.trim();
            state.active = document.getElementById('active-filter').value;
            state.page = 1;
            load();
        });

        prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
        nextBtn.addEventListener('click', () => { state.page++; load(); });

        load();
    </script>
@endpush