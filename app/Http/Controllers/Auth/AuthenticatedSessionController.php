<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const DUMMY_PASSWORD_HASH = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    public function showRoleSelection(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRoute());
        }

        return view('auth.choose-role');
    }

    public function showTeacherLogin(): View|RedirectResponse
    {
        return $this->showLoginForm(User::ROLE_TEACHER);
    }

    public function showStudentLogin(): View|RedirectResponse
    {
        return $this->showLoginForm(User::ROLE_STUDENT);
    }

    public function storeTeacherLogin(Request $request): RedirectResponse
    {
        return $this->authenticate($request, User::ROLE_TEACHER, 'email');
    }

    public function storeStudentLogin(Request $request): RedirectResponse
    {
        return $this->authenticate($request, User::ROLE_STUDENT, 'nisn');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function showLoginForm(string $role): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRoute());
        }

        return view('auth.login', ['role' => $role]);
    }

    private function authenticate(Request $request, string $role, string $identifierField): RedirectResponse
    {
        $identifierRules = $identifierField === 'nisn'
            ? ['required', 'string', 'regex:/^\d{10}$/']
            : ['required', 'string', 'email', 'max:255'];

        $validated = $request->validate([
            $identifierField => $identifierRules,
            'password' => ['required', 'string', 'max:255'],
        ]);

        $identifier = $identifierField === 'email'
            ? mb_strtolower(trim($validated[$identifierField]))
            : trim($validated[$identifierField]);
        $limiterKey = 'portal-auth:'.hash('sha256', $role.'|'.$identifier);

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            return back()
                ->withErrors([$identifierField => 'Terlalu banyak percobaan masuk. Coba lagi dalam 15 menit.'])
                ->withInput([$identifierField => $identifier]);
        }

        $user = User::query()
            ->where('role', $role)
            ->where($identifierField, $identifier)
            ->first();

        $passwordMatches = Hash::check($validated['password'], $user?->password ?? self::DUMMY_PASSWORD_HASH);

        if (! $user || ! $passwordMatches) {
            RateLimiter::hit($limiterKey, 900);

            return back()
                ->withErrors([$identifierField => 'Kredensial tidak cocok. Periksa data masuk Anda.'])
                ->withInput([$identifierField => $identifier]);
        }

        RateLimiter::clear($limiterKey);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRoute()));
    }
}
