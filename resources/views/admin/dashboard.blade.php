@extends('layouts.admin', ['title' => 'Dashboard', 'header' => 'Dashboard'])

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Knowledge Bases</p>
            <p id="stat-kbs" class="text-3xl font-semibold text-slate-900 mt-2">—</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Active KBs</p>
            <p id="stat-kbs-active" class="text-3xl font-semibold text-emerald-600 mt-2">—</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Documents</p>
            <p id="stat-docs" class="text-3xl font-semibold text-slate-900 mt-2">—</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Signed in as</p>
            <p id="stat-user" class="text-base font-medium text-slate-900 mt-2 truncate">—</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Recent Knowledge Bases</h2>
                <a href="{{ route('admin.knowledge-bases.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">View all →</a>
            </div>
            <div id="recent-kbs" class="divide-y divide-slate-100">
                <div class="p-5 text-sm text-slate-500">Loading…</div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-sm font-semibold text-slate-900">Quick actions</h2>
            </div>
            <div class="p-5 space-y-3">
                <a href="{{ route('admin.knowledge-bases.create') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-4 py-3 hover:bg-slate-50 transition">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Create a knowledge base</p>
                        <p class="text-xs text-slate-500">Define embeddings and chunking for a new policy corpus.</p>
                    </div>
                    <span class="text-indigo-600">→</span>
                </a>
                <a href="{{ route('admin.knowledge-bases.index') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-4 py-3 hover:bg-slate-50 transition">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Manage documents</p>
                        <p class="text-xs text-slate-500">Upload company policy files (PDF, DOCX, TXT, CSV).</p>
                    </div>
                    <span class="text-indigo-600">→</span>
                </a>
                <a href="{{ route('admin.ai-test') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-4 py-3 hover:bg-slate-50 transition">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Test AI service</p>
                        <p class="text-xs text-slate-500">Verify Laravel ↔ FastAPI connectivity.</p>
                    </div>
                    <span class="text-indigo-600">→</span>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('actions')
    <a href="{{ route('admin.knowledge-bases.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md">
        + New Knowledge Base
    </a>
@endsection

@push('scripts')
    <script>
        const { apiFetch, escapeHtml, formatDate } = window;

        const recentEl = document.getElementById('recent-kbs');

        async function load() {
            try {
                const data = await apiFetch('knowledge-bases?per_page=5');
                const items = data?.data || data || [];

                document.getElementById('stat-kbs').textContent = data?.meta?.total ?? items.length;
                document.getElementById('stat-kbs-active').textContent = items.filter((k) => k.is_active).length;

                if (!items.length) {
                    recentEl.innerHTML = '<div class="p-5 text-sm text-slate-500">No knowledge bases yet. Create your first one.</div>';
                } else {
                    recentEl.innerHTML = items.map((kb) => `
                        <a href="/admin/knowledge-bases/${kb.id}/documents" class="block px-5 py-3 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">${escapeHtml(kb.name)}</p>
                                    <p class="text-xs text-slate-500 truncate">${escapeHtml(kb.description || 'No description')}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-500">${kb.documents_count ?? 0} docs</p>
                                    <p class="text-xs text-slate-400">${formatDate(kb.created_at)}</p>
                                </div>
                            </div>
                        </a>
                    `).join('');
                }
            } catch (err) {
                recentEl.innerHTML = `<div class="p-5 text-sm text-rose-600">Failed to load: ${escapeHtml(err.message)}</div>`;
            }
        }

        async function loadDocsTotal() {
            try {
                const kbs = await apiFetch('knowledge-bases?per_page=100');
                const items = kbs?.data || kbs || [];
                let total = 0;
                for (const kb of items) {
                    try {
                        const docs = await apiFetch(`knowledge-bases/${kb.id}/documents?per_page=1`);
                        total += docs?.meta?.total ?? (Array.isArray(docs?.data) ? docs.data.length : (Array.isArray(docs) ? docs.length : 0));
                    } catch {}
                }
                document.getElementById('stat-docs').textContent = total;
            } catch {
                document.getElementById('stat-docs').textContent = '—';
            }
        }

        const userRaw = localStorage.getItem('api_user');
        if (userRaw) {
            try {
                const user = JSON.parse(userRaw);
                document.getElementById('stat-user').textContent = `${user.name} (${user.role})`;
            } catch {}
        }

        load();
        loadDocsTotal();
    </script>
@endpush