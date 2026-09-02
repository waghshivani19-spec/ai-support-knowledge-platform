<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Sign in - {{ config('app.name', 'AI Knowledge Platform') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($errors->any())
        <meta name="has-errors" content="true">
    @endif
</head>
<body class="min-h-screen bg-slate-900 text-slate-100 flex items-center justify-center px-4">

<div class="w-full max-w-md">
    <div class="text-center mb-6">
        <div class="inline-flex w-12 h-12 items-center justify-center rounded-xl bg-indigo-600 text-white mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="w-7 h-7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <h1 class="text-2xl font-semibold">Admin Sign in</h1>
        <p class="text-sm text-slate-400 mt-1">Use your administrator credentials to access the panel.</p>
    </div>

    <form method="POST" action="{{ route('admin.login.attempt') }}" class="bg-slate-800 border border-slate-700 rounded-xl p-6 space-y-4">
        @csrf

        @if ($errors->any())
            <div class="rounded-md border border-rose-500 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}"
                   class="w-full rounded-md bg-slate-900 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="w-full rounded-md bg-slate-900 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 transition rounded-md py-2 text-sm font-medium">
            Sign in
        </button>

        <p class="text-xs text-slate-400 text-center pt-2">
            Not an admin? <a href="{{ route('home') }}" class="text-indigo-400 hover:text-indigo-300">Go to site</a>
        </p>
    </form>
</div>

</body>
</html>