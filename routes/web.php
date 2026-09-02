<?php

use App\Models\User as UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/home', function () {
    return view('frontend.home');
})->name('home');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

Route::get('/chat', function () {
    return view('frontend.chat');
})->name('chat');

// Frontend: session login (used to support Laravel session + Sanctum token flow)
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (!Auth::attempt($credentials, true)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $user = Auth::user();

    if (!$user->is_active) {
        Auth::logout();
        return back()->withErrors(['email' => 'Your account is inactive.']);
    }

    $request->session()->regenerate();

    $token = $user->createToken('web-session')->plainTextToken;
    $request->session()->put('api_token', $token);

    return redirect()->intended(route('chat'));
})->name('login.attempt');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = UserModel::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'role' => 'customer',
        'is_active' => true,
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    $token = $user->createToken('web-session')->plainTextToken;
    $request->session()->put('api_token', $token);

    return redirect()->route('chat');
})->name('register.attempt');

Route::post('/logout', function (Request $request) {
    $user = Auth::user();
    if ($user) {
        $user->tokens()->where('name', 'web-session')->delete();
    }
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'admin.auth.login')->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Account is inactive.']);
        }

        if ($user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors(['email' => 'This account does not have administrator access.']);
        }

        $request->session()->regenerate();

        $token = $user->createToken('admin-web')->plainTextToken;
        $request->session()->put('api_token', $token);

        return redirect()->intended(route('admin.dashboard'));
    })->name('login.attempt');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));

        Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

        Route::view('/knowledge-bases', 'admin.knowledge-bases.index')
            ->name('knowledge-bases.index');

        Route::view('/knowledge-bases/create', 'admin.knowledge-bases.create')
            ->name('knowledge-bases.create');

        Route::get('/knowledge-bases/{id}/edit', function ($id) {
            return view('admin.knowledge-bases.edit', ['id' => $id]);
        })->name('knowledge-bases.edit');

        Route::get('/knowledge-bases/{id}/documents', function ($id) {
            return view('admin.knowledge-bases.documents', ['id' => $id]);
        })->name('knowledge-bases.documents');

        Route::view('/ai-test', 'admin.ai-test')->name('ai-test');

        Route::post('/logout', function (Request $request) {
            $user = Auth::user();
            if ($user) {
                $user->tokens()->where('name', 'admin-web')->delete();
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');
    });
});