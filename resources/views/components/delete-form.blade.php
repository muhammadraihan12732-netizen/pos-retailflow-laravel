@props(['action', 'message' => 'Yakin ingin menghapus data ini?'])
<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($message))">
    @csrf
    @method('DELETE')
    <button type="submit" class="icon-btn danger" title="Hapus"><x-icon name="trash" :size="18" /></button>
</form>
