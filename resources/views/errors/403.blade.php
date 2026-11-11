@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('title', 'Akses ditolak')

@section('content')
<div class="error-page">
    <div class="code">403</div>
    <h2>Akses ditolak</h2>
    <p class="muted">{{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>
    <a href="{{ url('/dashboard') }}" class="btn btn-primary" style="margin-top:12px">Kembali ke dashboard</a>
</div>
@endsection
