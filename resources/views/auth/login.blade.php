@extends('layouts.frontend', ['title' => 'Sign in'])

@section('content')
    <div class="max-w-md mx-auto py-10">
        <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8">
            <h1 class="text-2xl font-semibold text-slate-900">Sign in</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome back. Please enter your details.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="rounded-md border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-md">
                    Sign in
                </button>
            </form>

            <p class="text-sm text-slate-600 mt-6 text-center">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Register</a>
            </p>
        </div>
    </div>
@endsection