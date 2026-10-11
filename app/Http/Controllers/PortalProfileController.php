<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalProfileController extends Controller
{
    public function edit(): View
    {
        return view('portal.profile');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Nama profil berhasil diperbarui.');
    }
}
