@extends('layouts.app')

@php $editing = $category->exists; @endphp

@section('title', $editing ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
    <div class="page-header">
        <h2>{{ $editing ? 'Ubah Kategori' : 'Tambah Kategori' }}</h2>
        <a href="{{ route('categories.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
    </div>

    <div class="card card-pad" style="max-width:640px">
        <form method="POST" action="{{ $editing ? route('categories.update', $category) : route('categories.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <div class="full">
                    <label for="name">Nama kategori *</label>
                    <input id="name" type="text" name="name" class="input" value="{{ old('name', $category->name) }}" required autofocus>
                </div>
                <div class="full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="input">{{ old('description', $category->description) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
