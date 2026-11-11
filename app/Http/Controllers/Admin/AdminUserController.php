<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ContentActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $this->ensureSuperAdmin();

        $admins = Admin::query()->orderBy('role')->orderBy('username')->paginate(10);
        $superadminCount = Admin::query()->where('role', 'superadmin')->count();
        $totalCount = Admin::query()->count();

        return view('admin.admins.index', compact('admins', 'superadminCount', 'totalCount'));
    }

    public function create(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.admins.form', ['adminUser' => new Admin(['role' => 'admin'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:admins,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:admins,email'],
            'role' => ['required', 'string', Rule::in(['superadmin', 'admin'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Admin::query()->create($validated);

        return redirect()->route('admin.admins.index')->with('success', 'User admin berhasil ditambahkan.');
    }

    public function edit(Admin $admin): View
    {
        $this->ensureCanManage($admin);

        return view('admin.admins.form', ['adminUser' => $admin]);
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $this->ensureCanManage($admin);

        $currentUser = auth('admin')->user();
        $isSuperAdmin = $currentUser?->isSuperAdmin();

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('admins', 'username')->ignore($admin->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($isSuperAdmin) {
            $rules['role'] = ['required', 'string', Rule::in(['superadmin', 'admin'])];
        }

        $validated = $request->validate($rules);

        if ($isSuperAdmin && isset($validated['role'])) {
            if ($admin->isSuperAdmin() && $validated['role'] !== 'superadmin' && Admin::query()->where('role', 'superadmin')->count() <= 1) {
                return back()->withInput()->withErrors(['role' => 'Setidaknya harus ada satu akun Superadmin aktif.']);
            }
        } else {
            $validated['role'] = $admin->role;
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $admin->update($validated);

        $redirectRoute = $isSuperAdmin ? 'admin.admins.index' : 'admin.dashboard';

        return redirect()->route($redirectRoute)->with('success', 'Data user admin berhasil diperbarui.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $count = $this->deleteAdmins(Admin::query()->whereKey($admin->id));

        if ($count < 1) {
            return back()->withErrors(['admin' => 'Akun admin tidak dapat dihapus (mungkin akun Anda sendiri, superadmin terakhir, atau tidak valid).']);
        }

        return redirect()->route('admin.admins.index')->with('success', 'User admin berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:admins,id'],
        ]);

        $count = $this->deleteAdmins(Admin::query()->whereKey($validated['ids']));

        if ($count < 1) {
            return back()->withErrors(['admin' => 'Tidak ada akun admin yang dapat dihapus. Akun Anda sendiri dan superadmin terakhir tetap dilindungi.']);
        }

        $skipped = count($validated['ids']) - $count;
        $message = $count.' akun admin berhasil dihapus permanen.';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' akun dilindungi (akun sendiri/superadmin terakhir) dan tidak dihapus.';
        }

        return redirect()->route('admin.admins.index')->with('success', $message);
    }

    /**
     * Hard-delete admin accounts while protecting current user and the last superadmin.
     * Activity Log is preserved.
     */
    private function deleteAdmins($query): int
    {
        $currentId = (int) Auth::guard('admin')->id();
        $admins = $query->get(['id', 'name', 'role']);

        $candidates = $admins->filter(fn (Admin $admin): bool => (int) $admin->id !== $currentId);
        if ($candidates->isEmpty()) {
            return 0;
        }

        $superadminCount = Admin::query()->where('role', 'superadmin')->count();
        $superadminsToDelete = $candidates->where('role', 'superadmin')->count();
        if ($superadminCount - $superadminsToDelete < 1) {
            $candidates = $candidates->reject(fn (Admin $admin): bool => $admin->role === 'superadmin');
        }

        if ($candidates->isEmpty()) {
            return 0;
        }

        $now = now();
        $activities = $candidates->map(fn (Admin $admin): array => [
            'admin_id' => $currentId ?: null,
            'content_type' => 'admin',
            'content_id' => $admin->id,
            'action' => 'deleted',
            'title' => $admin->name,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::transaction(function () use ($candidates, $activities): void {
            Admin::query()->whereKey($candidates->modelKeys())->delete();
            if ($activities !== []) {
                ContentActivity::query()->insert($activities);
            }
        });

        return $candidates->count();
    }

    private function ensureSuperAdmin(): void
    {
        if (! auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Akses khusus Superadmin.');
        }
    }

    private function ensureCanManage(Admin $admin): void
    {
        $current = auth('admin')->user();
        if (! $current?->isSuperAdmin() && $current?->id !== $admin->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengelola user ini.');
        }
    }
}
