<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$this->spatieRoleFromValue($validated['role'])]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $currentRole = $this->roleValue($user->role);
        $newRole = (string) $validated['role'];

        if (
            $currentRole === AdminRole::SuperAdmin->value
            && $newRole !== AdminRole::SuperAdmin->value
            && $this->superAdminCount() <= 1
        ) {
            return redirect()->route('admin.users.edit', $user)->withErrors([
                'role' => 'At least one Super Admin must remain in the system.',
            ])->withInput();
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $newRole,
        ];

        if (isset($validated['password']) && $validated['password'] !== '') {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);
        $user->syncRoles([$this->spatieRoleFromValue($newRole)]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if ($currentUser !== null && $currentUser->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        if ($this->roleValue($user->role) === AdminRole::SuperAdmin->value && $this->superAdminCount() <= 1) {
            return back()->withErrors([
                'user' => 'At least one Super Admin must remain in the system.',
            ]);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        return [
            AdminRole::SuperAdmin->value => 'Super Admin',
            AdminRole::RiskManager->value => 'Risk Manager',
            AdminRole::Viewer->value => 'Viewer',
        ];
    }

    private function superAdminCount(): int
    {
        return User::query()
            ->where('role', AdminRole::SuperAdmin->value)
            ->count();
    }

    private function spatieRoleFromValue(string $roleValue): string
    {
        return match ($roleValue) {
            AdminRole::SuperAdmin->value => 'Super Admin',
            AdminRole::RiskManager->value => 'Risk Manager',
            AdminRole::Viewer->value => 'Viewer',
            default => 'Viewer',
        };
    }

    private function roleValue(AdminRole|string|null $role): string
    {
        if ($role instanceof AdminRole) {
            return $role->value;
        }

        return (string) $role;
    }
}
