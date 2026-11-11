@extends('layouts.app')

@php $editing = $customer->exists; @endphp

@section('title', $editing ? 'Ubah Pelanggan' : 'Tambah Pelanggan')

@section('content')
    <div class="page-header">
        <h2>{{ $editing ? 'Ubah Pelanggan' : 'Tambah Pelanggan' }}</h2>
        <a href="{{ route('customers.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
    </div>

    <div class="card card-pad" style="max-width:640px">
        <form method="POST" action="{{ $editing ? route('customers.update', $customer) : route('customers.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <div class="full">
                    <label for="name">Nama pelanggan *</label>
                    <input id="name" type="text" name="name" class="input" value="{{ old('name', $customer->name) }}" required autofocus>
                </div>
                <div class="full">
                    <label for="phone">Telepon</label>
                    <input id="phone" type="text" name="phone" class="input" value="{{ old('phone', $customer->phone) }}">
                </div>
                <div class="full">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="input">{{ old('address', $customer->address) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
