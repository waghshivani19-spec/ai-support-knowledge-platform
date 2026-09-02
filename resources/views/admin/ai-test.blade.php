@extends('layouts.admin', [
    'title' => 'AI Service',
    'header' => 'AI Service Test',
    'subtitle' => 'Verify Laravel ↔ FastAPI connectivity.',
])

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="bg-white border border-slate-200 rounded-lg p-5">
            <p class="text-sm text-slate-600">
                This page calls the Laravel endpoint
                <code class="px-1.5 py-0.5 bg-slate-100 rounded text-xs">GET /api/ai/test</code>,
                which proxies a request to the FastAPI AI microservice.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Connection check</h2>
                <button id="run-test"
                        class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-md flex items-center gap-2 disabled:opacity-60">
                    <span>Run test</span>
                    <svg id="test-spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"/>
                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div id="test-result" class="p-5 text-sm text-slate-500">
                Click "Run test" to verify the AI service.
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const { apiFetch, escapeHtml, showToast } = window;

        const btn = document.getElementById('run-test');
        const spinner = document.getElementById('test-spinner');
        const result = document.getElementById('test-result');

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            spinner.classList.remove('hidden');
            result.innerHTML = '<p class="text-slate-500">Testing…</p>';

            try {
                const data = await apiFetch('ai/test');
                result.innerHTML = `
                    <div class="space-y-2">
                        <p class="text-emerald-700 font-medium">✓ Connection successful</p>
                        <pre class="bg-slate-900 text-slate-100 text-xs p-4 rounded-md overflow-auto">${escapeHtml(JSON.stringify(data, null, 2))}</pre>
                    </div>
                `;
                showToast('AI service is reachable.', 'success');
            } catch (err) {
                result.innerHTML = `
                    <div class="space-y-2">
                        <p class="text-rose-700 font-medium">✗ Failed</p>
                        <p class="text-sm text-slate-700">${escapeHtml(err.message)}</p>
                    </div>
                `;
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        });
    </script>
@endpush