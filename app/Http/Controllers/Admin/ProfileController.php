<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('userProfile');

        return view('admin.profile.index', [
            'user' => $user,
            'displayName' => $this->displayName($user),
            'roleLabel' => $this->roleLabel($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan akun lain.',
        ]);

        $nameParts = preg_split('/\s+/', trim($validated['name']), 2);

        $user->name = $validated['name'];
        $user->first_name = $nameParts[0] ?? $validated['name'];
        $user->last_name = $nameParts[1] ?? '';
        $user->email = $validated['email'];
        $user->username = $validated['username'];
        $user->phone_number = $validated['phone_number'];
        $user->save();

        if ($user->userProfile) {
            $user->userProfile->update([
                'phone_number' => $validated['phone_number'],
            ]);
        }

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Password berhasil diperbarui.');
    }

    private function displayName($user): string
    {
        if (! empty($user->name)) {
            return $user->name;
        }

        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $fullName !== '' ? $fullName : 'User';
    }

    private function roleLabel($user): string
    {
        if (! empty($user->role)) {
            return ucfirst($user->role);
        }

        if (! empty($user->user_type)) {
            return ucfirst(str_replace('_', ' ', $user->user_type));
        }

        if (method_exists($user, 'getRoleNames') && $user->getRoleNames()->isNotEmpty()) {
            return ucfirst($user->getRoleNames()->first());
        }

        return 'User';
    }
}
