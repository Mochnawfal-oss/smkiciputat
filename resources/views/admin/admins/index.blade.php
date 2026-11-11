@extends('admin.layout')

@section('title', 'Manajemen Admin')
@section('page-title', 'Manajemen Admin')
@section('breadcrumb', 'Manajemen Admin')

@section('content')
    <div class="admin-page-intro">
        <div>
            <h2>Akun administrator</h2>
            <p>Hanya akun terdaftar yang dapat mengakses panel pengelolaan website. Akun sendiri dan superadmin terakhir dilindungi dari penghapusan massal.</p>
        </div>
        <a href="{{ route('admin.admins.create') }}" class="admin-button admin-button-primary">+ Tambah admin</a>
    </div>

    <section class="admin-card" data-bulk-root data-total-count="{{ $totalCount }}" data-category-label="Akun Admin">
        <form method="POST" action="{{ route('admin.admins.bulk-destroy') }}" data-bulk-form>
            @csrf
            <input type="hidden" name="select_all" value="0">

            <div class="admin-bulk-bar">
                <div class="admin-bulk-summary"><strong><span data-bulk-count>0</span></strong> data dipilih (total: {{ $totalCount }})</div>
                <div class="admin-bulk-actions">
                    <button type="button" class="admin-button admin-button-small admin-button-secondary" data-bulk-clear>Batalkan Pilihan</button>
                    <button type="button" class="admin-button admin-button-small admin-button-danger" data-bulk-delete disabled>Hapus Terpilih</button>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th class="admin-check-col"><input type="checkbox" data-bulk-select-page aria-label="Pilih semua di halaman"></th><th>Nama</th><th>Username</th><th>Role</th><th>Terdaftar</th><th class="admin-table-actions">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($admins as $admin)
                            @php($protected = auth('admin')->id() === $admin->id || ($admin->isSuperAdmin() && $superadminCount <= 1))
                            <tr>
                                <td class="admin-check-col"><input type="checkbox" name="ids[]" value="{{ $admin->id }}" data-bulk-item data-name="{{ e($admin->name) }}" aria-label="Pilih {{ $admin->name }}" @disabled($protected)></td>
                                <td><strong>{{ $admin->name }}</strong>@if (auth('admin')->id() === $admin->id) <span class="admin-current-user">Anda</span>@endif</td>
                                <td><code>{{ $admin->username }}</code></td>
                                <td><span class="admin-status {{ $admin->isSuperAdmin() ? 'admin-status-published' : 'admin-status-draft' }}">{{ $admin->isSuperAdmin() ? 'Superadmin' : 'Admin' }}</span></td>
                                <td>{{ $admin->created_at->format('d M Y') }}</td>
                                <td class="admin-table-actions">
                                    <div class="admin-row-actions">
                                        <a class="admin-button admin-button-small admin-button-secondary" href="{{ route('admin.admins.edit', $admin) }}">Edit</a>
                                        @if (! $protected)
                                            <button type="submit" class="admin-button admin-button-small admin-button-danger" form="delete-admin-{{ $admin->id }}">Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>

        @foreach ($admins as $admin)
            @php($protected = auth('admin')->id() === $admin->id || ($admin->isSuperAdmin() && $superadminCount <= 1))
            @if (! $protected)
                <form id="delete-admin-{{ $admin->id }}" method="POST" action="{{ route('admin.admins.destroy', $admin) }}" data-confirm="Hapus akun admin {{ e($admin->name) }} secara permanen?" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @endforeach

        <div class="admin-pagination">{{ $admins->links() }}</div>
    </section>
@endsection
