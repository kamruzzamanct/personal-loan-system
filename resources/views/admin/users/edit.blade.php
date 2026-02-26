@extends('layouts.admin')

@section('title', 'Admin - Edit User')

@section('content')
    <section class="admin-page">
        <header class="admin-header admin-header-inline">
            <div>
                <h1>Edit User</h1>
                <p>Update user profile, role assignment, and credentials.</p>
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
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form-grid">
                @csrf
                @method('PUT')

                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                </div>

                @php
                    $roleRaw = $user->role;
                    $roleValue = $roleRaw instanceof \BackedEnum ? $roleRaw->value : (string) $roleRaw;
                @endphp
                <div class="field field-full">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $roleValue) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="password">New Password (optional)</label>
                    <input id="password" name="password" type="password">
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password">
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn-primary">Update User</button>
                </div>
            </form>
        </article>
    </section>
@endsection
