@extends('layouts.admin')

@section('title', 'Admin - Manage Users')

@section('content')
    <section class="admin-page">
        <header class="admin-header admin-header-inline">
            <div>
                <h1>Manage Users</h1>
                <p>Create, edit, and control access for admin users.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">Add User</a>
        </header>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->has('user'))
            <div class="alert alert-error">{{ $errors->first('user') }}</div>
        @endif

        <article class="admin-card admin-filter-card">
            <form method="GET" action="{{ route('admin.users.index') }}" class="admin-users-search-form">
                <div class="field">
                    <label for="search">Search User</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        placeholder="Name or email"
                        value="{{ $search }}"
                    >
                </div>
                <div class="admin-filter-actions">
                    <button type="submit" class="btn-primary">Search</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </article>

        <article class="admin-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $roleRaw = $user->role;
                                $roleValue = $roleRaw instanceof \BackedEnum ? $roleRaw->value : (string) $roleRaw;
                            @endphp
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $roleValue)) }}</td>
                                <td>{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="table-action-link">Edit</a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="admin-inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn-danger-outline"
                                                onclick="return confirm('Delete this user account?');"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-empty">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="admin-pagination">
                {{ $users->onEachSide(1)->links() }}
            </div>
        </article>
    </section>
@endsection
