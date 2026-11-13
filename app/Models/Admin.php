<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'role', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => 'Superadmin',
            'admin' => 'Admin',
            'editor' => 'Editor',
            default => 'Administrator',
        };
    }

    public function roleDescription(): string
    {
        return match ($this->role) {
            'superadmin' => 'Akses penuh ke konten, pengaturan, media, dan manajemen akun. Hanya ada satu Superadmin; gunakan Manajemen Admin untuk membuat akun Admin atau Editor.',
            'admin' => 'Mengelola konten, pengaturan website, dan Media Manager. Manajemen akun hanya tersedia untuk Superadmin.',
            'editor' => 'Mengelola program, tenaga kependidikan, sambutan, berita, lowongan, dan galeri. Editor dapat mengunggah foto untuk konten, tetapi tidak dapat mengakses pengaturan, Media Manager, atau akun admin.',
            default => 'Akses panel administrator.',
        };
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
