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

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
