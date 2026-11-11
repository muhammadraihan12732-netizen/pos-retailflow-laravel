@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="page-header">
        <h2>Manajemen Pengguna</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary"><x-icon name="plus" :size="18" /> Tambah Pengguna</a>
    </div>

    <form method="GET" class="filters">
        <div class="search">
            <x-icon name="search" :size="18" />
            <input type="text" name="q" class="input" placeholder="Cari nama atau email..." value="{{ request('q') }}">
        </div>
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Nama</th><th>Email</th><th class="center">Peran</th><th class="center">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($users as $u)
                    @php $badge = ['admin' => 'badge-purple', 'manager' => 'badge-blue', 'cashier' => 'badge-green'][$u->role] ?? 'badge-gray'; @endphp
                    <tr>
                        <td class="strong">{{ $u->name }} @if ($u->id === auth()->id()) <span class="muted small">(Anda)</span> @endif</td>
                        <td>{{ $u->email }}</td>
                        <td class="center"><span class="badge {{ $badge }}">{{ $u->role_label }}</span></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('users.edit', $u) }}" class="icon-btn edit" title="Ubah"><x-icon name="edit" :size="18" /></a>
                                @if ($u->id !== auth()->id())
                                    <x-delete-form :action="route('users.destroy', $u)" message="Hapus pengguna ini?" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Belum ada pengguna.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$users" />
@endsection
