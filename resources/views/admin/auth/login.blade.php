@extends('layouts.frontend')

@section('title', 'Admin Login')

@section('content')
    <section class="admin-auth-wrap">
        <article class="admin-auth-card">
            <h1>Admin Login</h1>
            <p>Sign in with your admin account to access the dashboard.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <strong>Login failed:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="admin-auth-form">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <label class="consent-box" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    <span>Remember me on this device</span>
                </label>

                <button type="submit" class="btn-primary">Login to Admin</button>
            </form>
        </article>
    </section>
@endsection
