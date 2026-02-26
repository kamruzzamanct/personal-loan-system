@extends('layouts.frontend')

@section('title', 'Applicant Registration')

@section('content')
    <section class="admin-auth-wrap">
        <article class="admin-auth-card">
            <h1>Applicant Registration</h1>
            <p>Create your account to track loan applications in one place.</p>

            @if ($errors->any())
                <div class="alert alert-error">
                    <strong>Registration failed:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('applicant.register.store') }}" class="admin-auth-form">
                @csrf

                <div class="field">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <button type="submit" class="btn-primary">Create Account</button>
            </form>

            <p class="auth-switch-link">
                Already registered? <a href="{{ route('applicant.login') }}">Login</a>
            </p>
        </article>
    </section>
@endsection
