@extends('layouts.admin', [
    'title' => 'New Knowledge Base',
    'header' => 'New Knowledge Base',
    'subtitle' => 'Create a knowledge collection for the AI chat.',
])

@section('content')
    <div class="max-w-3xl">
        <form id="kb-form" class="bg-white border border-slate-200 rounded-lg p-6 space-y-5">
            <div id="form-error" class="hidden rounded-md border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800"></div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
                <input name="name" required maxlength="255"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3" maxlength="2000"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Embedding provider</label>
                    <input name="embedding_provider" maxlength="100" placeholder="openai"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-slate-500 mt-1">Defaults to configured provider.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Embedding model</label>
                    <input name="embedding_model" maxlength="150" placeholder="text-embedding-3-small"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chunk size</label>
                    <input name="chunk_size" type="number" min="100" max="10000" value="1000"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chunk overlap</label>
                    <input name="chunk_overlap" type="number" min="0" max="5000" value="200"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" name="is_active" type="checkbox" value="1" checked
                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-sm text-slate-700">Active</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.knowledge-bases.index') }}"
                   class="px-4 py-2 text-sm rounded-md border border-slate-300 hover:bg-slate-50">Cancel</a>
                <button type="submit" id="submit-btn"
                        class="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2 disabled:opacity-60">
                    <span>Create Knowledge Base</span>
                    <svg id="spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"/>
                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const { apiFetch, showToast } = window;

        const form = document.getElementById('kb-form');
        const errorEl = document.getElementById('form-error');
        const submitBtn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');

        function showError(msg) {
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorEl.classList.add('hidden');
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');

            const formData = new FormData(form);
            const payload = {
                name: formData.get('name'),
                description: formData.get('description') || null,
                embedding_provider: formData.get('embedding_provider') || null,
                embedding_model: formData.get('embedding_model') || null,
                chunk_size: Number(formData.get('chunk_size')) || 1000,
                chunk_overlap: Number(formData.get('chunk_overlap')) || 200,
                is_active: !!formData.get('is_active'),
            };

            try {
                const data = await apiFetch('knowledge-bases', { method: 'POST', body: payload });
                showToast('Knowledge base created.', 'success');
                const id = data?.data?.id || data?.id;
                window.location.href = `/admin/knowledge-bases/${id}/documents`;
            } catch (err) {
                if (err.data?.errors) {
                    showError(Object.values(err.data.errors).flat().join(' '));
                } else {
                    showError(err.message);
                }
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
            }
        });
    </script>
@endpush