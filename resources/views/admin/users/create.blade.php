@extends('layouts.admin')

@section('title', 'Admin - Create User')

@section('content')
    <section class="admin-page">
        <header class="admin-header admin-header-inline">
            <div>
                <h1>Create User</h1>
                <p>Add a new admin account and assign role access.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back to Users</a>
        </header>

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <article class="admin-card">
            <form method="POST" action="{{ route('admin.users.store') }}" class="admin-form-grid">
                @csrf

                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field field-full">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select role</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </article>
    </section>
@endsection
