<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => User::ROLE_CASHIER])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), attributes: ['password' => 'Kata sandi']);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->rules($user));

        // Kosongkan password = tidak diganti.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Cegah admin terakhir kehilangan hak akses.
        if ($user->role === User::ROLE_ADMIN
            && $data['role'] !== User::ROLE_ADMIN
            && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->withInput()->with('error', 'Harus ada minimal satu admin.');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }

        if ($user->sales()->exists() || $user->purchases()->exists()) {
            return back()->with('error', 'Pengguna ini punya riwayat transaksi dan tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
