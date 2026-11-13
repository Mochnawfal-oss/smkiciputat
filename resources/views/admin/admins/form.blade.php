@extends('admin.layout')

@php($isEditing = $adminUser->exists)
@section('title', $isEditing ? 'Edit Admin' : 'Tambah Admin')
@section('page-title', $isEditing ? 'Edit Admin' : 'Tambah Admin')
@section('breadcrumb', 'Manajemen Admin')

@section('content')
    <form
        method="POST"
        action="{{ $isEditing ? route('admin.admins.update', $adminUser) : route('admin.admins.store') }}"
        class="admin-card admin-form admin-admin-form"
    >
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="admin-form-heading">
            <div>
                <h2>{{ $isEditing ? 'Perbarui akun administrator' : 'Buat akun administrator' }}</h2>
                <p>{{ $isEditing ? 'Kosongkan password jika tidak ingin menggantinya. Username dapat disesuaikan.' : 'Gunakan username unik dan kata sandi minimal 8 karakter.' }}</p>
            </div>
        </div>

        <div class="admin-form-grid admin-admin-form-grid">
            <div class="admin-field">
                <label for="name">Nama Lengkap <span>*</span></label>
                <input id="name" name="name" value="{{ old('name', $adminUser->name) }}" autocomplete="name" required>
                @error('name')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="admin-field">
                <label for="username">Username <span>*</span></label>
                <input id="username" name="username" value="{{ old('username', $adminUser->username) }}" autocomplete="username" required>
                @error('username')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="admin-field">
                <label for="role">Role / Peran <span>*</span></label>
                @if (auth('admin')->user()?->isSuperAdmin())
                    @if ($adminUser->isSuperAdmin())
                        <input type="text" value="Superadmin (akun tunggal)" disabled>
                        <input type="hidden" name="role" value="superadmin">
                    @else
                        <select id="role" name="role" required>
                            <option value="admin" @selected(old('role', $adminUser->role ?? 'admin') === 'admin')>Admin — konten, pengaturan, dan media</option>
                            <option value="editor" @selected(old('role', $adminUser->role) === 'editor')>Editor — konten dan unggah foto konten</option>
                        </select>
                    @endif
                @else
                    <input type="text" value="{{ $adminUser->roleLabel() }}" disabled>
                    <input type="hidden" name="role" value="{{ $adminUser->role }}">
                @endif
                @error('role')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="admin-field">
                <label for="email">Email (Opsional)</label>
                <input id="email" name="email" type="email" value="{{ old('email', $adminUser->email) }}" autocomplete="email">
                @error('email')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="admin-field">
                <label for="password">Kata sandi @unless ($isEditing)<span>*</span>@endunless</label>
                <input id="password" name="password" type="password" autocomplete="new-password" @required(! $isEditing)>
                @error('password')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="admin-field">
                <label for="password_confirmation">Ulangi kata sandi @unless ($isEditing)<span>*</span>@endunless</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $isEditing)>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.admins.index') }}" class="admin-button admin-button-secondary">Batal</a>
            <button type="submit" class="admin-button admin-button-primary">{{ $isEditing ? 'Simpan perubahan' : 'Buat akun' }}</button>
        </div>
    </form>
@endsection
