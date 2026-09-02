@extends('layouts.frontend', ['title' => 'AI Chat'])

@section('content')
    @guest
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
            <h1 class="text-2xl font-semibold text-slate-900">Sign in to chat</h1>
            <p class="text-sm text-slate-500 mt-2">Please sign in or create an account to use the AI assistant.</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-md">Sign in</a>
                <a href="{{ route('register') }}" class="border border-slate-300 hover:border-slate-400 text-slate-700 text-sm font-medium px-5 py-2 rounded-md">Register</a>
            </div>
        </div>
    @endguest

    @auth
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <aside class="lg:col-span-1">
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">Knowledge Base</h2>
                    <p class="text-xs text-slate-500 mb-3">The AI will search within the selected knowledge base.</p>

                    <select id="kb-select"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Loading…</option>
                    </select>

                    <button id="new-chat-btn"
                            class="mt-3 w-full text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md py-2">
                        + New conversation
                    </button>
                </div>

                <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs text-indigo-900">
                    <p class="font-semibold mb-1">About this assistant</p>
                    <p>It answers questions about company policy using the official documents uploaded by administrators.</p>
                </div>
            </aside>

            <section class="lg:col-span-3">
                <div class="bg-white border border-slate-200 rounded-xl flex flex-col h-[70vh]">
                    <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h1 class="text-base font-semibold text-slate-900">Company Policy Assistant</h1>
                            <p class="text-xs text-slate-500">Ask anything about HR, IT, leave, expenses, etc.</p>
                        </div>
                        <div class="text-xs text-slate-400" id="status-pill">Ready</div>
                    </div>

                    <div id="chat-log" class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold">AI</div>
                            <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-md px-4 py-3 max-w-2xl text-sm text-slate-800">
                                Hello {{ auth()->user()->name }}! I'm your company policy assistant. Ask me a question and I'll find the answer in your documents.
                            </div>
                        </div>
                    </div>

                    <form id="chat-form" class="border-t border-slate-200 p-4 flex gap-2">
                        <textarea id="chat-input" rows="1" required maxlength="4000"
                                  placeholder="Ask a question about company policy…"
                                  class="flex-1 resize-none rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <button type="submit" id="send-btn"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 rounded-md flex items-center gap-2 disabled:opacity-60">
                            <span>Send</span>
                            <svg id="send-spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    @endauth
@endsection

@auth
    @push('scripts')
        <script>
            const { apiFetch, escapeHtml, showToast } = window;

            const kbSelect = document.getElementById('kb-select');
            const log = document.getElementById('chat-log');
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-input');
            const sendBtn = document.getElementById('send-btn');
            const sendSpinner = document.getElementById('send-spinner');
            const statusPill = document.getElementById('status-pill');
            const newChatBtn = document.getElementById('new-chat-btn');

            let history = [];
            let currentConversationId = null;

            function setStatus(text, type = 'ready') {
                statusPill.textContent = text;
                statusPill.className = 'text-xs ' + (
                    type === 'thinking' ? 'text-amber-600' :
                    type === 'error' ? 'text-rose-600' :
                    'text-slate-400'
                );
            }

            function appendMessage(role, content, sources = []) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex gap-3' + (role === 'user' ? ' justify-end' : '');

                const avatar = `<div class="w-8 h-8 rounded-full ${role === 'user' ? 'bg-slate-700 text-white' : 'bg-indigo-600 text-white'} flex items-center justify-center text-xs font-semibold">${role === 'user' ? 'You' : 'AI'}</div>`;

                const bubble = document.createElement('div');
                bubble.className = `rounded-2xl px-4 py-3 max-w-2xl text-sm whitespace-pre-wrap ${
                    role === 'user'
                        ? 'bg-indigo-600 text-white rounded-tr-md'
                        : 'bg-white border border-slate-200 text-slate-800 rounded-tl-md'
                }`;
                bubble.textContent = content;

                let sourcesHtml = '';
                if (role === 'assistant' && Array.isArray(sources) && sources.length) {
                    sourcesHtml = `
                        <div class="mt-3 border-t border-slate-200 pt-2">
                            <p class="text-xs font-semibold text-slate-500 mb-1">Sources</p>
                            <ul class="text-xs text-slate-600 space-y-1">
                                ${sources.slice(0, 5).map((s, idx) => `
                                    <li>${idx + 1}. ${escapeHtml(s.filename || s.document_id || 'document')}${s.chunk_index !== undefined ? ` · chunk ${s.chunk_index}` : ''}</li>
                                `).join('')}
                            </ul>
                        </div>
                    `;
                }

                wrapper.innerHTML = avatar;
                const contentWrap = document.createElement('div');
                contentWrap.className = role === 'user' ? 'max-w-2xl' : 'max-w-2xl';
                contentWrap.innerHTML = `
                    ${role === 'user' ? '' : '<div class="sr-only">assistant</div>'}
                    <div class="${bubble.className}">${escapeHtml(content)}</div>
                    ${sourcesHtml}
                `;
                wrapper.appendChild(contentWrap);

                log.appendChild(wrapper);
                log.scrollTop = log.scrollHeight;
            }

            async function loadKnowledgeBases() {
                try {
                    const data = await apiFetch('ai/knowledge-bases');
                    const items = data?.data?.data || data?.data || data || [];
                    kbSelect.innerHTML = '<option value="">Use default knowledge base</option>';
                    items.forEach((kb) => {
                        const opt = document.createElement('option');
                        opt.value = kb.id;
                        opt.textContent = kb.name || ('KB #' + kb.id);
                        if (kb.is_active === false) opt.disabled = true;
                        kbSelect.appendChild(opt);
                    });
                } catch (err) {
                    kbSelect.innerHTML = '<option value="">Default</option>';
                }
            }

            async function send() {
                const message = input.value.trim();
                if (!message) return;

                appendMessage('user', message);
                history.push({ role: 'user', content: message });
                input.value = '';
                input.disabled = true;
                sendBtn.disabled = true;
                sendSpinner.classList.remove('hidden');
                setStatus('Thinking…', 'thinking');

                const body = { message };
                if (kbSelect.value) body.knowledge_base_id = Number(kbSelect.value);

                try {
                    const data = await apiFetch('ai/chat', {
                        method: 'POST',
                        body: {
                            ...body,
                            conversation_id: currentConversationId,
                        },
                    });
                    const result = data?.data || data;
                    currentConversationId = result?.conversation_id || currentConversationId;

                    const reply = result?.reply || result?.response || result?.answer || JSON.stringify(result);
                    const sources = result?.sources || [];

                    history.push({ role: 'assistant', content: reply });
                    appendMessage('assistant', reply, sources);
                    setStatus('Ready');
                } catch (err) {
                    setStatus('Error', 'error');
                    appendMessage('assistant', `Sorry, something went wrong: ${err.message}`);
                } finally {
                    input.disabled = false;
                    sendBtn.disabled = false;
                    sendSpinner.classList.add('hidden');
                    input.focus();
                }
            }

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                send();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    send();
                }
            });

            newChatBtn.addEventListener('click', () => {
                history = [];
                currentConversationId = null;
                log.innerHTML = '';
                appendMessage('assistant', 'New conversation started. How can I help?');
            });

            loadKnowledgeBases();
        </script>
    @endpush
@endauth