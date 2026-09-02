@extends('layouts.admin', [
    'title' => 'Documents',
    'header' => 'Documents',
    'subtitle' => 'Upload policy files for this knowledge base.',
])

@section('actions')
    <a href="{{ route('admin.knowledge-bases.index') }}"
       class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900">
        ← All Knowledge Bases
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div id="kb-info" class="bg-white border border-slate-200 rounded-lg p-5 text-sm">
                <p class="text-slate-500">Loading knowledge base…</p>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-lg">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h2 class="text-sm font-semibold text-slate-900">Upload document</h2>
                    <p class="text-xs text-slate-500">Allowed: PDF, DOCX, TXT, CSV — up to 20 MB.</p>
                </div>
                <form id="upload-form" class="p-5 space-y-3">
                    <div id="upload-error" class="hidden rounded-md border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-800"></div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title (optional)</label>
                        <input name="title" maxlength="255"
                               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">File</label>
                        <input id="file-input" name="file" type="file" required accept=".pdf,.docx,.txt,.csv"
                               class="w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-600 file:text-white file:px-3 file:py-1.5 hover:file:bg-indigo-700">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" id="upload-btn"
                                class="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2 disabled:opacity-60">
                            <span>Upload</span>
                            <svg id="upload-spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Documents</h2>
                    <span id="docs-count" class="text-xs text-slate-500">—</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium">Title</th>
                                <th class="text-left px-5 py-3 font-medium">File</th>
                                <th class="text-left px-5 py-3 font-medium">Status</th>
                                <th class="text-left px-5 py-3 font-medium">Chunks</th>
                                <th class="text-left px-5 py-3 font-medium">Uploaded</th>
                                <th class="text-right px-5 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="docs-tbody" class="divide-y divide-slate-100">
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const { apiFetch, escapeHtml, formatDate, showToast } = window;

        const kbId = {{ (int) ($id ?? 0) }};
        const state = { page: 1 };

        const kbInfo = document.getElementById('kb-info');
        const tbody = document.getElementById('docs-tbody');
        const docsCount = document.getElementById('docs-count');
        const pageLabel = document.getElementById('current-page');
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const uploadForm = document.getElementById('upload-form');
        const uploadError = document.getElementById('upload-error');
        const uploadBtn = document.getElementById('upload-btn');
        const uploadSpinner = document.getElementById('upload-spinner');
        const fileInput = document.getElementById('file-input');

        function statusBadge(status) {
            const map = {
                processed: 'bg-emerald-100 text-emerald-700',
                processing: 'bg-amber-100 text-amber-700',
                pending: 'bg-slate-200 text-slate-700',
                failed: 'bg-rose-100 text-rose-700',
            };
            const cls = map[status] || 'bg-slate-200 text-slate-700';
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs ${cls}">${escapeHtml(status || 'unknown')}</span>`;
        }

        async function loadKb() {
            try {
                const data = await apiFetch(`knowledge-bases/${kbId}`);
                const kb = data?.data || data;
                kbInfo.innerHTML = `
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-900">${escapeHtml(kb.name)}</h3>
                        ${kb.is_active
                            ? '<span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Active</span>'
                            : '<span class="text-xs px-2 py-0.5 rounded bg-slate-200 text-slate-700">Inactive</span>'}
                    </div>
                    <p class="text-xs text-slate-500 mb-3">${escapeHtml(kb.description || 'No description')}</p>
                    <dl class="space-y-1 text-xs">
                        <div class="flex justify-between"><dt class="text-slate-500">Slug</dt><dd class="text-slate-700">${escapeHtml(kb.slug)}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Provider</dt><dd class="text-slate-700">${escapeHtml(kb.embedding?.provider || '—')}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Model</dt><dd class="text-slate-700 truncate ml-2">${escapeHtml(kb.embedding?.model || '—')}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Chunk</dt><dd class="text-slate-700">${kb.chunking?.size ?? '—'} / ${kb.chunking?.overlap ?? '—'}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Documents</dt><dd class="text-slate-700">${kb.documents_count ?? 0}</dd></div>
                    </dl>
                    <div class="mt-4 flex gap-2">
                        <a href="/admin/knowledge-bases/${kb.id}/edit"
                           class="text-xs text-indigo-600 hover:text-indigo-700">Edit settings →</a>
                    </div>
                `;
            } catch (err) {
                kbInfo.innerHTML = `<p class="text-sm text-rose-600">Failed to load: ${escapeHtml(err.message)}</p>`;
            }
        }

        async function loadDocs() {
            tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-6 text-center text-slate-500">Loading…</td></tr>`;
            try {
                const data = await apiFetch(`knowledge-bases/${kbId}/documents?per_page=15&page=${state.page}`);
                const items = data?.data || data || [];
                const meta = data?.meta || { current_page: 1, last_page: 1, total: items.length };

                pageLabel.textContent = meta.current_page || 1;
                prevBtn.disabled = !(meta.current_page > 1);
                nextBtn.disabled = !(meta.current_page < meta.last_page);
                docsCount.textContent = `${meta.total ?? items.length} document(s)`;

                if (!items.length) {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-6 text-center text-slate-500">No documents yet.</td></tr>`;
                    return;
                }

                tbody.innerHTML = items.map((d) => `
                    <tr>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900">${escapeHtml(d.title || 'Untitled')}</div>
                            ${d.processing_error ? `<div class="text-xs text-rose-600 mt-0.5">${escapeHtml(d.processing_error)}</div>` : ''}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            <div class="truncate max-w-xs">${escapeHtml(d.original_filename)}</div>
                            <div class="text-xs text-slate-400">${escapeHtml(d.mime_type || '')} · ${escapeHtml(d.file_size_human || '')}</div>
                        </td>
                        <td class="px-5 py-3">${statusBadge(d.status)}</td>
                        <td class="px-5 py-3 text-slate-700">${d.chunk_count ?? '—'}</td>
                        <td class="px-5 py-3 text-xs text-slate-500">
                            ${formatDate(d.created_at)}
                            ${d.uploader ? `<div class="text-xs text-slate-400">by ${escapeHtml(d.uploader.name)}</div>` : ''}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button data-id="${d.id}" data-name="${escapeHtml(d.title || d.original_filename)}"
                                    class="doc-delete inline-flex items-center px-2.5 py-1 text-xs rounded border border-rose-300 text-rose-700 hover:bg-rose-50">
                                Delete
                            </button>
                        </td>
                    </tr>
                `).join('');

                tbody.querySelectorAll('.doc-delete').forEach((btn) => {
                    btn.addEventListener('click', () => handleDelete(btn.dataset.id, btn.dataset.name));
                });
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-6 text-center text-rose-600">${escapeHtml(err.message)}</td></tr>`;
            }
        }

        async function handleDelete(id, name) {
            if (!confirm(`Delete "${name}"? This will remove embeddings for this file.`)) return;
            try {
                await apiFetch(`knowledge-bases/${kbId}/documents/${id}`, { method: 'DELETE' });
                showToast('Document deleted.', 'success');
                loadDocs();
                loadKb();
            } catch (err) {
                showToast(err.message, 'error');
            }
        }

        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            uploadError.classList.add('hidden');

            const file = fileInput.files[0];
            if (!file) {
                uploadError.textContent = 'Please choose a file.';
                uploadError.classList.remove('hidden');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            const title = uploadForm.querySelector('[name=title]').value.trim();
            if (title) formData.append('title', title);

            uploadBtn.disabled = true;
            uploadSpinner.classList.remove('hidden');

            try {
                await apiFetch(`knowledge-bases/${kbId}/documents`, { method: 'POST', body: formData, isForm: true });
                showToast('Document uploaded. Processing in background.', 'success');
                uploadForm.reset();
                loadDocs();
                loadKb();
            } catch (err) {
                if (err.data?.errors) {
                    uploadError.textContent = Object.values(err.data.errors).flat().join(' ');
                } else {
                    uploadError.textContent = err.message;
                }
                uploadError.classList.remove('hidden');
            } finally {
                uploadBtn.disabled = false;
                uploadSpinner.classList.add('hidden');
            }
        });

        prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; loadDocs(); } });
        nextBtn.addEventListener('click', () => { state.page++; loadDocs(); });

        loadKb();
        loadDocs();
    </script>
@endpush