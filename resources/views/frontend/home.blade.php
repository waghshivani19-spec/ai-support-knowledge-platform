@extends('layouts.frontend', ['title' => 'AI Knowledge Platform'])

@section('content')
    <section class="text-center py-12">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 mb-4">
            Powered by your company's knowledge base
        </span>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight text-slate-900">
            Ask anything about our company policy.
        </h1>
        <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto">
            Our AI assistant instantly answers questions using official documents, HR handbooks, IT policies and more — with sources you can trust.
        </p>

        <div class="mt-8 flex items-center justify-center gap-3">
            @auth
                <a href="{{ route('chat') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-md text-sm font-medium">
                    Start chatting →
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-md text-sm font-medium">
                    Get started — it's free
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 border border-slate-300 hover:border-slate-400 px-5 py-3 rounded-md text-sm font-medium text-slate-700">
                    Sign in
                </a>
            @endauth
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 py-8">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="w-9 h-9 rounded-md bg-indigo-100 text-indigo-700 flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900">Accurate answers</h3>
            <p class="text-sm text-slate-500 mt-1">Grounded in your official documents, not guesses.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="w-9 h-9 rounded-md bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900">24/7 available</h3>
            <p class="text-sm text-slate-500 mt-1">Get policy answers any time without waiting for HR.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="w-9 h-9 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900">Private & secure</h3>
            <p class="text-sm text-slate-500 mt-1">Your conversations stay inside the platform.</p>
        </div>
    </section>
@endsection