@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="brand-logo">RetailFlow</div>
        <h2>Selamat datang</h2>
        <p class="sub">Masuk untuk membuka aplikasi kasir</p>

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email') <div class="hint" style="color:var(--red)">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" class="input" required autocomplete="current-password">
                @error('password') <div class="hint" style="color:var(--red)">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        @if (config('app.debug'))
            <div class="demo-box">
                <strong>Akun demo</strong> (hanya tampil saat APP_DEBUG=true)<br>
                admin@retailflow.test · manager@retailflow.test · kasir@retailflow.test<br>
                Kata sandi: <span class="mono">password</span>
            </div>
        @endif
    </div>
</div>
@endsection
