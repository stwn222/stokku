<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Redirect;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('hak_akses', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()
                       ->paginate(10)
                       ->withQueryString();

        return Inertia::render('User/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
            ]
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'hak_akses' => 'required|in:Administrator,Karyawan',
        ], [
            'name.required' => 'Nama user wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'hak_akses.required' => 'Hak akses wajib dipilih',
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['username'] . '@stokku.app',
                'password' => Hash::make($validated['password']),
                'hak_akses' => $validated['hak_akses'],
                'email_verified_at' => now(),
            ]);

            return Redirect::route('user-management.index')
                ->with('success', 'User berhasil ditambahkan');
                
        } catch (\Exception $e) {
            return Redirect::back()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'hak_akses' => 'required|in:Administrator,Karyawan',
        ], [
            'name.required' => 'Nama user wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'hak_akses.required' => 'Hak akses wajib dipilih',
        ]);

        try {
            $data = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['username'] . '@stokku.app',
                'hak_akses' => $validated['hak_akses'],
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            return Redirect::route('user-management.index')
                ->with('success', 'User berhasil diupdate');
                
        } catch (\Exception $e) {
            return Redirect::back()
                ->with('error', 'Gagal mengupdate user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Get authenticated user
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();
        
        // Prevent deleting own account
        if ($user->id === $authUser->id) {
            return Redirect::back()
                ->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        try {
            $user->delete();

            return Redirect::route('user-management.index')
                ->with('success', 'User berhasil dihapus');
                
        } catch (\Exception $e) {
            return Redirect::back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}