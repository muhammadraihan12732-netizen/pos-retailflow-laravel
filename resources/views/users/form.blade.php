@extends('layouts.app')

@php $editing = $user->exists; @endphp

@section('title', $editing ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('content')
    <div class="page-header">
        <h2>{{ $editing ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h2>
        <a href="{{ route('users.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
    </div>

    <div class="card card-pad" style="max-width:700px">
        <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <div>
                    <label for="name">Nama *</label>
                    <input id="name" type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required autofocus>
                </div>
                <div>
                    <label for="email">Email *</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="full">
                    <label for="role">Peran *</label>
                    <select id="role" name="role" class="input" required>
                        @foreach (\App\Models\User::ROLES as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Admin: semua menu · Manajer: produk, pembelian, laporan · Kasir: POS, transaksi, pelanggan.</div>
                </div>
                <div>
                    <label for="password">Kata sandi {{ $editing ? '' : '*' }}</label>
                    <input id="password" type="password" name="password" class="input" autocomplete="new-password" @required(! $editing)>
                    @if ($editing) <div class="hint">Kosongkan bila tidak ingin mengganti.</div> @endif
                </div>
                <div>
                    <label for="password_confirmation">Ulangi kata sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
