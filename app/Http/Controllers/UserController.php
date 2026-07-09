<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorizeManageUsers();

        $users = User::query()->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorizeManageUsers();

        return view('users.create', [
            'roles' => $this->assignableRoles(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManageUsers();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $user = User::create($validated);
        $user->services()->sync($request->input('services', []));
        $user->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('users.index')
            ->with('success', __('dobs.flash_user_created'));
    }

    public function edit(User $user): View
    {
        $this->authorizeManageUsers();

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageUsers();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        if ($user->isAdmin() && $validated['role'] !== User::ROLE_ADMIN) {
            $otherAdmins = User::query()
                ->where('role', User::ROLE_ADMIN)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                return back()
                    ->withInput()
                    ->with('error', __('dobs.cannot_remove_last_admin'));
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->services()->sync($request->input('services', []));
        $user->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('users.index')
            ->with('success', __('dobs.flash_user_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeManageUsers();

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', __('dobs.cannot_delete_self'));
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::query()
                ->where('role', User::ROLE_ADMIN)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                return redirect()
                    ->route('users.index')
                    ->with('error', __('dobs.cannot_delete_last_admin'));
            }
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', __('dobs.flash_user_deleted'));
    }

    /**
     * @return list<string>
     */
    private function assignableRoles(): array
    {
        return User::ROLES;
    }
}
