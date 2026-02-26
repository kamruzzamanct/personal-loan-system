@extends('layouts.frontend')

@section('title', 'Applicant Login')

@section('content')
    <section class="admin-auth-wrap">
        <article class="admin-auth-card">
            <h1>Applicant Login</h1>
            <p>Sign in to view your loan applications and status updates.</p>

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

            <form method="POST" action="{{ route('applicant.login.store') }}" class="admin-auth-form">
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

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <p class="auth-switch-link">
                New here? <a href="{{ route('applicant.register') }}">Create an account</a>
            </p>
        </article>
    </section>
@endsection
