<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AI Knowledge Platform' }}</title>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } }</script>
        <script src="{{ asset('js/api.js') }}"></script>
    @endif
    @stack('head')
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-800 antialiased min-h-screen">

<nav class="bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                </span>
                <span class="font-semibold text-slate-900">AI Knowledge Platform</span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm text-slate-600 hover:text-slate-900 {{ request()->routeIs('home') ? 'font-semibold text-indigo-600' : '' }}">
                    Home
                </a>
                @auth
                    <a href="{{ route('chat') }}" class="text-sm text-slate-600 hover:text-slate-900 {{ request()->routeIs('chat') ? 'font-semibold text-indigo-600' : '' }}">
                        Chat
                    </a>
                    <span class="hidden sm:inline text-sm text-slate-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-rose-600 hover:text-rose-700">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900">Sign in</a>
                    <a href="{{ route('register') }}" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-md">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @yield('content')
</main>

<footer class="border-t border-slate-200 mt-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'AI Knowledge Platform') }}. All rights reserved.
    </div>
</footer>

<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
    window.API_BASE = "{{ url('/api') }}";
    @if(session('api_token'))
        window.API_TOKEN = @json(session('api_token'));
    @endif
</script>

@stack('scripts')
</body>
</html>