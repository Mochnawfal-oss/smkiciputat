<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

#[Signature('admin:create')]
#[Description('Create an administrator account for the CMS')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $name = $this->ask('Nama admin');
        $username = $this->ask('Username admin');
        $roles = Admin::query()->where('role', 'superadmin')->exists()
            ? ['admin', 'editor']
            : ['superadmin', 'admin', 'editor'];
        $role = $this->choice('Role / Peran', $roles, 0);
        $email = $this->ask('Email admin (opsional, tekan Enter untuk lewati)');
        $password = $this->secret('Kata sandi (minimal 8 karakter)');
        $passwordConfirmation = $this->secret('Ulangi kata sandi');
        $validator = Validator::make([
            'name' => $name,
            'username' => $username,
            'role' => $role,
            'email' => $email ?: null,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:admins,username'],
            'role' => ['required', 'string', Rule::in($roles)],
            'email' => ['nullable', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($role === 'superadmin' && Admin::query()->where('role', 'superadmin')->exists()) {
            $this->error('Akun Superadmin sudah tersedia. Buat akun Admin atau Editor melalui Manajemen Admin.');

            return self::FAILURE;
        }

        Admin::query()->create([
            'name' => $name,
            'username' => $username,
            'role' => $role,
            'email' => $email ?: null,
            'password' => $password,
        ]);

        $this->info("Admin '{$username}' dengan role '{$role}' berhasil dibuat.");

        return self::SUCCESS;
    }
}
