<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminRegistrationController extends Controller
{
    public function create(): View
    {
        $this->ensureRegistrationIsAvailable();

        return view('admin.setup-welcome');
    }

    public function form(): View
    {
        $this->ensureRegistrationIsAvailable();

        return view('admin.register');
    }

    public function success(): View
    {
        return view('admin.register-success');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationIsAvailable();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:admins,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = Cache::lock('admin-superadmin-bootstrap', 10)->get(function () use ($validated): ?Admin {
            return DB::transaction(function () use ($validated): ?Admin {
                if (Admin::query()->exists()) {
                    return null;
                }

                return Admin::query()->create([
                    ...$validated,
                    'role' => 'superadmin',
                ]);
            });
        });

        if ($admin === false) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['registration' => 'Pendaftaran sedang diproses. Silakan coba kembali.']);
        }

        abort_if($admin === null, 404);

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.register.success')->with('success', 'Akun Superadmin pertama berhasil dibuat.');
    }

    private function ensureRegistrationIsAvailable(): void
    {
        abort_unless(
            in_array(config('app.env'), ['local', 'testing'], true)
                && Admin::query()->doesntExist(),
            404,
        );
    }
}
