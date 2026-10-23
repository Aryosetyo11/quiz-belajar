<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in([User::ROLE_TEACHER, User::ROLE_STUDENT, User::ROLE_ADMIN_REMOTE, User::ROLE_ADMIN_FOUNDATION])],
        ]);

        $users = User::query()
            ->when($filters['role'] ?? null, fn (Builder $query, string $role) => $query->where('role', $role))
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('nisn', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('portal.admin-users', ['users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in([User::ROLE_TEACHER, User::ROLE_STUDENT, User::ROLE_ADMIN_REMOTE, User::ROLE_ADMIN_FOUNDATION])],
            'email' => [Rule::requiredIf(fn (): bool => $request->input('role') !== User::ROLE_STUDENT), 'nullable', 'email', 'max:255', 'unique:users,email'],
            'nisn' => [Rule::requiredIf(fn (): bool => $request->input('role') === User::ROLE_STUDENT), 'nullable', 'digits:10', 'unique:users,nisn'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->letters()->numbers()],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'] ?? null,
            'nisn' => $validated['nisn'] ?? null,
            'password' => $validated['password'],
        ]);

        return to_route('admin.users')->with('status', 'Akun berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $isStudent = $user->role === User::ROLE_STUDENT;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [Rule::excludeIf($isStudent), Rule::requiredIf(! $isStudent), 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'nisn' => [Rule::excludeIf(! $isStudent), Rule::requiredIf($isStudent), 'nullable', 'digits:10', Rule::unique('users', 'nisn')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->letters()->numbers()],
        ]);

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $user->role === User::ROLE_STUDENT ? null : $validated['email'],
            'nisn' => $user->role === User::ROLE_STUDENT ? $validated['nisn'] : null,
        ]);

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        $user->save();

        return to_route('admin.users')->with('status', 'Data akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak dapat menghapus akun yang sedang digunakan.']);
        }

        if ($user->role === User::ROLE_TEACHER && $user->subjectsAsTeacher()->exists()) {
            throw ValidationException::withMessages(['user' => 'Akun guru masih memiliki mata pelajaran. Pindahkan mata pelajaran sebelum menghapus akun.']);
        }

        if ($user->role === User::ROLE_STUDENT && $user->enrolledSubjects()->exists()) {
            throw ValidationException::withMessages(['user' => 'Siswa masih terdaftar pada mata pelajaran. Keluarkan siswa dari kelas sebelum menghapus akun.']);
        }

        DB::transaction(function () use ($user): void {
            if (in_array($user->role, [User::ROLE_ADMIN_REMOTE, User::ROLE_ADMIN_FOUNDATION], true)) {
                $adminIds = User::query()->whereIn('role', [User::ROLE_ADMIN_REMOTE, User::ROLE_ADMIN_FOUNDATION])->lockForUpdate()->pluck('id');

                if ($adminIds->count() <= 1) {
                    throw ValidationException::withMessages(['user' => 'Akun Admin terakhir tidak dapat dihapus.']);
                }
            }

            $user->delete();
        });

        return to_route('admin.users')->with('status', 'Akun berhasil dihapus.');
    }
}
