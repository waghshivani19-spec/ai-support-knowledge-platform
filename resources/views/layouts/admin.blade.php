<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} - {{ config('app.name', 'AI Knowledge Platform') }}</title>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="{{ asset('js/api.js') }}"></script>
    @endif
    @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

<div class="flex min-h-screen">
    <aside class="hidden md:flex md:w-64 flex-col bg-slate-900 text-slate-100">
        <div class="px-6 py-5 border-b border-slate-800">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold tracking-wide">
                <span class="text-indigo-400">AI</span> Knowledge
            </a>
            <p class="text-xs text-slate-400 mt-1">Admin Panel</p>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                    ['route' => 'admin.knowledge-bases.index', 'label' => 'Knowledge Bases', 'icon' => 'database'],
                    ['route' => 'admin.ai-test', 'label' => 'AI Service Test', 'icon' => 'sparkles'],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md transition
                          {{ $active ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="w-5 h-5 inline-flex items-center justify-center">
                        @switch($item['icon'])
                            @case('home')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 12 2.25 21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                @break
                            @case('database')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                                @break
                            @case('sparkles')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                                @break
                        @endswitch
                    </span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            <div class="mb-2">Signed in as</div>
            <div class="text-slate-100 font-medium truncate">{{ auth()->user()?->name ?? 'Admin' }}</div>
            <div class="truncate">{{ auth()->user()?->email }}</div>

            <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                @csrf
                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-md text-rose-300 hover:bg-slate-800 hover:text-rose-200 transition">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">{{ $header ?? 'Dashboard' }}</h1>
                @isset($subtitle)
                    <p class="text-sm text-slate-500">{{ $subtitle }}</p>
                @endisset
            </div>
            <div class="flex items-center gap-2">
                @yield('actions')
            </div>
        </header>

        <div class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

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