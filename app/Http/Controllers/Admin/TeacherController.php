<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentActivity;
use App\Models\Teacher;
use App\Support\PublicImageStorage;
use App\Support\TeacherPhotoFocus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Illuminate\View\View;
use Throwable;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $entries = Teacher::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%')
                        ->orWhere('position', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalCount = Teacher::query()->count();

        return view('admin.teachers.index', [
            'entries' => $entries,
            'search' => $search,
            'type' => 'teachers',
            'typeConfig' => $this->typeConfig(),
            'totalCount' => $totalCount,
        ]);
    }

    public function create(TeacherPhotoFocus $photoFocus): View
    {
        return view('admin.teachers.form', [
            'entry' => new Teacher(['status' => 'published', 'category' => 'Tenaga Pendidik']),
            'photoFocus' => $photoFocus->forPath(null),
            'type' => 'teachers',
            'typeConfig' => $this->typeConfig(),
        ]);
    }

    public function store(Request $request, PublicImageStorage $images, TeacherPhotoFocus $photoFocus): RedirectResponse
    {
        $this->normalizeRequest($request);
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributesForValidation());
        $this->validateNoDuplicate($request);
        $attributes = $this->attributes($validated);
        $newImage = null;
        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $images->store(
                    $request->file('image'),
                    'cms/teacher',
                    'teacher',
                    $request->file('source_image'),
                    Auth::guard('admin')->id(),
                );
                $imagePath = $newImage;
            } elseif ($request->filled('media_asset_id')) {
                $imagePath = $images->storeFromAsset($request->integer('media_asset_id'));
            }

            if ($imagePath !== null) {
                $attributes['image_path'] = $imagePath;
            }

            $this->updatePhotoFocus($photoFocus, $imagePath, $validated);

            $entry = $this->teacherConnection()->transaction(fn () => Teacher::query()->create($attributes));
        } catch (Throwable $exception) {
            $images->delete($newImage);

            throw $exception;
        }

        $this->recordActivity($entry, 'created');

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Tenaga Kependidikan berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher, TeacherPhotoFocus $photoFocus): View
    {
        return view('admin.teachers.form', [
            'entry' => $teacher,
            'photoFocus' => $photoFocus->forPath($teacher->image_path),
            'type' => 'teachers',
            'typeConfig' => $this->typeConfig(),
        ]);
    }

    public function update(
        Request $request,
        Teacher $teacher,
        PublicImageStorage $images,
        TeacherPhotoFocus $photoFocus,
    ): RedirectResponse {
        $this->normalizeRequest($request, $teacher);
        $validated = $request->validate($this->rules($teacher), $this->messages(), $this->attributesForValidation());
        $this->validateNoDuplicate($request, $teacher);
        $attributes = $this->attributes($validated);
        $oldImage = $teacher->image_path;
        $newImage = null;
        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $images->store(
                    $request->file('image'),
                    'cms/teacher',
                    'teacher',
                    $request->file('source_image'),
                    Auth::guard('admin')->id(),
                );
                $imagePath = $newImage;
            } elseif ($request->filled('media_asset_id')) {
                $imagePath = $images->storeFromAsset($request->integer('media_asset_id'));
            }

            if ($imagePath !== null) {
                $attributes['image_path'] = $imagePath;
            }

            $this->updatePhotoFocus($photoFocus, $imagePath ?? $oldImage, $validated);

            $this->teacherConnection()->transaction(function () use ($teacher, $attributes): void {
                Teacher::query()
                    ->whereKey($teacher->id)
                    ->update([
                        ...$attributes,
                        'updated_at' => now(),
                    ]);
            });
        } catch (Throwable $exception) {
            $images->delete($newImage);

            throw $exception;
        }

        $teacher->refresh();

        if ($imagePath !== null && $oldImage !== $imagePath) {
            $images->deleteIfUnused($oldImage);
        }

        $this->recordActivity($teacher, 'updated');

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Tenaga Kependidikan berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher, PublicImageStorage $images): RedirectResponse
    {
        $count = $this->deleteTeachers(Teacher::query()->whereKey($teacher->id), $images);

        if ($count < 1) {
            abort(404);
        }

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Tenaga Kependidikan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, PublicImageStorage $images): RedirectResponse
    {
        $validated = $request->validate([
            'select_all' => ['nullable', 'boolean'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:teachers.teachers,id'],
        ]);

        $query = Teacher::query();
        if ($request->boolean('select_all')) {
            // seluruh kategori (semua halaman)
        } elseif (! empty($validated['ids'])) {
            $query->whereKey($validated['ids']);
        } else {
            return back()->withErrors(['bulk' => 'Pilih minimal satu data tenaga kependidikan.']);
        }

        $count = $this->deleteTeachers($query, $images);

        if ($count < 1) {
            return back()->withErrors(['bulk' => 'Tidak ada data yang dapat dihapus.']);
        }

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', $count.' data tenaga kependidikan berhasil dihapus permanen.');
    }

    public function empty(Request $request, PublicImageStorage $images): RedirectResponse
    {
        $request->validate([
            'confirm_name' => ['required', 'string'],
        ]);

        $expected = 'TENAGA KEPENDIDIKAN';
        $given = Str::of($request->input('confirm_name'))->upper()->squish()->toString();
        if ($given !== $expected) {
            return back()->withErrors(['confirm_name' => 'Ketik "'.$expected.'" untuk mengosongkan kategori ini.']);
        }

        $count = $this->deleteTeachers(Teacher::query(), $images);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', $count > 0
                ? 'Seluruh data tenaga kependidikan ('.$count.' data) berhasil dihapus permanen.'
                : 'Kategori tenaga kependidikan sudah kosong.');
    }

    /**
     * @return array<string, mixed>
     */
    private function typeConfig(): array
    {
        return [
            'type' => 'teacher',
            'singular' => 'Tenaga Kependidikan',
            'plural' => 'Tenaga Kependidikan',
        ];
    }

    private function normalizeRequest(Request $request, ?Teacher $entry = null): void
    {
        $baseSlug = Str::slug($request->input('title', 'guru'));
        $request->merge([
            'slug' => filled($request->input('slug'))
                ? Str::slug($request->input('slug'))
                : ($baseSlug ?: 'guru-'.($entry?->id ?? Str::random(6))),
            'category' => $request->input('category') ?: 'Tenaga Pendidik',
            'status' => $request->input('status', 'published'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Teacher $entry = null): array
    {
        $connection = (new Teacher)->getConnectionName() ?? config('database.default');
        $slugRule = Rule::unique($connection.'.teachers', 'slug');
        if ($entry !== null) {
            $slugRule->ignore($entry->id);
        }

        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'summary' => ['nullable', 'string', 'max:600'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:'.(config('media.max_upload_mb') * 1024)],
            'source_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:'.(config('media.max_upload_mb') * 1024)],
            'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'focal_point_x' => ['nullable', 'numeric', 'between:0,100'],
            'focal_point_y' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['nullable', 'integer', 'between:-10000,10000'],
            'published_at' => ['nullable', 'date'],
            'metadata.position' => ['required', 'string', 'max:255'],
            'metadata.education' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'slug.unique' => 'Data tenaga kependidikan dengan slug/identitas ini sudah terdaftar.',
            'image.image' => 'File foto harus berupa gambar yang valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function attributesForValidation(): array
    {
        return [
            'title' => 'nama tenaga kependidikan',
            'slug' => 'slug',
            'metadata.position' => 'jabatan/mata pelajaran',
        ];
    }

    private function validateNoDuplicate(Request $request, ?Teacher $entry = null): void
    {
        $name = Str::of($request->input('title', ''))->squish()->lower()->toString();
        $position = Str::of(data_get($request->all(), 'metadata.position', ''))->squish()->lower()->toString();

        if ($name === '' || $position === '') {
            return;
        }

        $duplicate = Teacher::query()
            ->when($entry !== null, fn ($query) => $query->whereKeyNot($entry->id))
            ->get(['id', 'name', 'position'])
            ->first(fn (Teacher $teacher): bool => Str::of($teacher->name)->squish()->lower()->toString() === $name
                && Str::of($teacher->position)->squish()->lower()->toString() === $position);

        if ($duplicate !== null) {
            $validator = validator($request->all(), []);
            $validator->after(function (Validator $validator): void {
                $validator->errors()->add('title', 'Data tenaga kependidikan dengan nama dan jabatan yang sama sudah terdaftar.');
            });

            throw new ValidationException($validator);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributes(array $validated): array
    {
        $attributes = [
            'name' => $validated['title'],
            'slug' => $validated['slug'],
            'position' => $validated['metadata']['position'],
            'education' => $validated['metadata']['education'] ?? null,
            'category' => $validated['category'] ?? 'Tenaga Pendidik',
            'summary' => $validated['summary'] ?? null,
            'status' => $validated['status'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'published_at' => $validated['published_at'] ?? null,
        ];

        if ($attributes['status'] === 'published' && blank($attributes['published_at'])) {
            $attributes['published_at'] = now();
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function updatePhotoFocus(TeacherPhotoFocus $photoFocus, ?string $path, array $validated): void
    {
        if (! array_key_exists('focal_point_x', $validated) || ! array_key_exists('focal_point_y', $validated)) {
            return;
        }

        $x = (float) $validated['focal_point_x'];
        $y = (float) $validated['focal_point_y'];

        if ($photoFocus->updateForPath($path, $x, $y)) {
            return;
        }

        if ($path !== null && ($x !== 50.0 || $y !== 24.0)) {
            throw ValidationException::withMessages([
                'focal_point_x' => 'Foto ini belum terdaftar di Media Manager. Unggah atau pilih media sebelum mengatur posisi fokus.',
            ]);
        }
    }

    private function recordActivity(Teacher $entry, string $action): void
    {
        ContentActivity::query()->create([
            'admin_id' => Auth::guard('admin')->id(),
            'content_type' => 'teacher',
            'content_id' => $entry->id,
            'action' => $action,
            'title' => $entry->name,
        ]);
    }

    /**
     * Hard-delete teachers in the separate DB (transaction). Records activity and cleans unused images.
     * Activity Log is preserved (never removed by this operation).
     */
    private function deleteTeachers($query, PublicImageStorage $images): int
    {
        $teachers = $query->get(['id', 'name', 'image_path']);
        if ($teachers->isEmpty()) {
            return 0;
        }

        $adminId = Auth::guard('admin')->id();
        $now = now();
        $activities = $teachers->map(fn (Teacher $teacher): array => [
            'admin_id' => $adminId,
            'content_type' => 'teacher',
            'content_id' => $teacher->id,
            'action' => 'deleted',
            'title' => $teacher->name,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        $this->teacherConnection()->transaction(function () use ($teachers, $activities): void {
            Teacher::query()->whereKey($teachers->modelKeys())->delete();
            if ($activities !== []) {
                ContentActivity::query()->insert($activities);
            }
        });

        foreach ($teachers->pluck('image_path')->filter()->unique() as $path) {
            $images->deleteIfUnused($path);
        }

        return $teachers->count();
    }

    private function teacherConnection()
    {
        $connection = (new Teacher)->getConnectionName() ?? config('database.default');

        return DB::connection($connection);
    }
}
