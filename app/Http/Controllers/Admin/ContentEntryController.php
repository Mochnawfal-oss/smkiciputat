<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentActivity;
use App\Models\ContentEntry;
use App\Support\PublicImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentEntryController extends Controller
{
    public function index(Request $request, string $type): View
    {
        $typeConfig = $this->typeConfig($type);
        $search = $request->string('search')->trim()->toString();

        $query = ContentEntry::query()
            ->where('type', $typeConfig['type'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            });

        $entries = (clone $query)
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalCount = (clone $query)->count();

        return view('admin.content.index', compact('entries', 'search', 'type', 'typeConfig', 'totalCount'));
    }

    public function create(string $type): View
    {
        $typeConfig = $this->typeConfig($type);
        $defaultStatus = $typeConfig['type'] === 'teacher' ? 'published' : 'draft';

        return view('admin.content.form', [
            'entry' => new ContentEntry(['status' => $defaultStatus]),
            'type' => $type,
            'typeConfig' => $typeConfig,
        ]);
    }

    public function store(Request $request, string $type, PublicImageStorage $images): RedirectResponse
    {
        $typeConfig = $this->typeConfig($type);

        if ($typeConfig['type'] === 'teacher') {
            $baseSlug = Str::slug($request->input('title', 'guru'));
            $request->merge([
                'slug' => filled($request->input('slug')) ? Str::slug($request->input('slug')) : ($baseSlug ?: 'guru-'.Str::random(6)),
                'category' => $request->input('category') ?: 'Tenaga Pendidik',
                'status' => $request->input('status', 'published'),
            ]);
        } else {
            $request->merge([
                'slug' => filled($request->input('slug'))
                    ? Str::slug($request->input('slug'))
                    : Str::slug($request->input('title', '')),
            ]);
        }

        $validated = $request->validate($this->rules($request, $typeConfig));
        $attributes = $this->attributes($validated, $typeConfig);

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $images->store(
                $request->file('image'),
                'cms/'.$typeConfig['type'],
                $this->mediaCategory($typeConfig['type']),
                $request->file('source_image'),
                Auth::guard('admin')->id(),
            );
        } elseif ($request->filled('media_asset_id')) {
            $attributes['image_path'] = $images->storeFromAsset($request->integer('media_asset_id'));
        }

        $entry = ContentEntry::query()->create([
            ...$attributes,
            'type' => $typeConfig['type'],
        ]);

        $this->recordActivity($entry, 'created');

        return redirect()
            ->route('admin.content.index', $type)
            ->with('success', $typeConfig['singular'].' berhasil ditambahkan.');
    }

    public function edit(string $type, ContentEntry $entry): View
    {
        $typeConfig = $this->typeConfig($type);
        $this->ensureEntryType($entry, $typeConfig['type']);

        return view('admin.content.form', compact('entry', 'type', 'typeConfig'));
    }

    public function update(
        Request $request,
        string $type,
        ContentEntry $entry,
        PublicImageStorage $images,
    ): RedirectResponse {
        $typeConfig = $this->typeConfig($type);
        $this->ensureEntryType($entry, $typeConfig['type']);

        if ($typeConfig['type'] === 'teacher') {
            $baseSlug = Str::slug($request->input('title', 'guru'));
            $request->merge([
                'slug' => filled($request->input('slug')) ? Str::slug($request->input('slug')) : ($baseSlug ?: 'guru-'.$entry->id),
                'category' => $request->input('category') ?: 'Tenaga Pendidik',
                'status' => $request->input('status', 'published'),
            ]);
        } else {
            $request->merge([
                'slug' => filled($request->input('slug'))
                    ? Str::slug($request->input('slug'))
                    : Str::slug($request->input('title', '')),
            ]);
        }

        $validated = $request->validate($this->rules($request, $typeConfig, $entry));
        $attributes = $this->attributes($validated, $typeConfig);
        $oldImage = $entry->image_path;

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $images->store(
                $request->file('image'),
                'cms/'.$typeConfig['type'],
                $this->mediaCategory($typeConfig['type']),
                $request->file('source_image'),
                Auth::guard('admin')->id(),
            );
        } elseif ($request->filled('media_asset_id')) {
            $attributes['image_path'] = $images->storeFromAsset($request->integer('media_asset_id'));
        }

        $entry->update($attributes);
        $images->deleteIfUnused($oldImage);
        $this->recordActivity($entry, 'updated');

        return redirect()
            ->route('admin.content.index', $type)
            ->with('success', $typeConfig['singular'].' berhasil diperbarui.');
    }

    public function destroy(string $type, ContentEntry $entry, PublicImageStorage $images): RedirectResponse
    {
        $typeConfig = $this->typeConfig($type);
        $this->ensureEntryType($entry, $typeConfig['type']);

        $count = $this->deleteEntries(
            ContentEntry::query()->whereKey($entry->id)->where('type', $typeConfig['type']),
            $typeConfig,
            $images,
        );

        if ($count < 1) {
            abort(404);
        }

        return redirect()
            ->route('admin.content.index', $type)
            ->with('success', $typeConfig['singular'].' berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, string $type, PublicImageStorage $images): RedirectResponse
    {
        $typeConfig = $this->typeConfig($type);
        $validated = $request->validate([
            'select_all' => ['nullable', 'boolean'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $base = ContentEntry::query()->where('type', $typeConfig['type']);
        if ($request->boolean('select_all')) {
            // seluruh kategori (semua halaman)
        } elseif (! empty($validated['ids'])) {
            $base->whereKey($validated['ids']);
        } else {
            return back()->withErrors(['bulk' => 'Pilih minimal satu data.']);
        }

        $count = $this->deleteEntries($base, $typeConfig, $images);

        if ($count < 1) {
            return back()->withErrors(['bulk' => 'Tidak ada data yang dapat dihapus.']);
        }

        return redirect()
            ->route('admin.content.index', $type)
            ->with('success', $count.' data '.$typeConfig['singular'].' berhasil dihapus permanen.');
    }

    public function empty(Request $request, string $type, PublicImageStorage $images): RedirectResponse
    {
        $typeConfig = $this->typeConfig($type);
        $request->validate([
            'confirm_name' => ['required', 'string'],
        ]);

        $expected = Str::of($typeConfig['plural'])->upper()->toString();
        $given = Str::of($request->input('confirm_name'))->upper()->squish()->toString();
        if ($given !== $expected) {
            return back()->withErrors(['confirm_name' => 'Ketik "'.$expected.'" untuk mengosongkan kategori ini.']);
        }

        $count = $this->deleteEntries(
            ContentEntry::query()->where('type', $typeConfig['type']),
            $typeConfig,
            $images,
        );

        return redirect()
            ->route('admin.content.index', $type)
            ->with('success', $count > 0
                ? 'Seluruh data '.$typeConfig['plural'].' ('.$count.' data) berhasil dihapus permanen.'
                : 'Kategori '.$typeConfig['plural'].' sudah kosong.');
    }

    /**
     * @return array<string, mixed>
     */
    private function typeConfig(string $type): array
    {
        abort_if($type === 'teachers', 404);

        $typeConfig = config('site-content.content_types.'.$type);

        abort_if(! is_array($typeConfig), 404);

        return $typeConfig;
    }

    /**
     * @param  array<string, mixed>  $typeConfig
     * @return array<string, mixed>
     */
    private function rules(Request $request, array $typeConfig, ?ContentEntry $entry = null): array
    {
        $typeValue = $typeConfig['type'];
        $slugRule = Rule::unique('content_entries', 'slug')
            ->where(fn (QueryBuilder $query): QueryBuilder => $query->where('type', $typeValue));

        if ($entry !== null) {
            $slugRule->ignore($entry->id);
        }

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'summary' => ['nullable', 'string', 'max:600'],
            'body' => ['nullable', 'string', 'max:50000'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:'.(config('media.max_upload_mb') * 1024)],
            'source_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:'.(config('media.max_upload_mb') * 1024)],
            'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'between:-10000,10000'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ];

        foreach ($typeConfig['fields'] as $name => $field) {
            $fieldRule = $field['required'] ? ['required'] : ['nullable'];
            $fieldRule[] = match ($field['type']) {
                'text' => 'string',
                'textarea' => 'string',
                'date' => 'date',
                'url' => ['url', 'regex:/^https?:\/\/\S+$/i'],
                'select' => Rule::in(array_keys($field['options'])),
                default => 'string',
            };
            $fieldRule[] = $field['type'] === 'textarea' ? 'max:10000' : 'max:255';

            if ($name === 'route_key') {
                $routeKeyRule = Rule::unique('content_entries', 'route_key')
                    ->where(fn (QueryBuilder $query): QueryBuilder => $query->where('type', $typeValue));

                if ($entry !== null) {
                    $routeKeyRule->ignore($entry->id);
                }

                $fieldRule[] = 'regex:/^[a-z0-9-]+$/';
                $fieldRule[] = $routeKeyRule;
            }

            $rules['metadata.'.$name] = $fieldRule;
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $typeConfig
     * @return array<string, mixed>
     */
    private function attributes(array $validated, array $typeConfig): array
    {
        $metadata = Arr::only($validated['metadata'] ?? [], array_keys($typeConfig['fields']));
        $routeKey = $metadata['route_key'] ?? null;
        unset($metadata['route_key']);

        $attributes = [
            ...Arr::only($validated, [
                'title',
                'slug',
                'summary',
                'body',
                'category',
                'status',
                'published_at',
                'meta_title',
                'meta_description',
            ]),
            'route_key' => $routeKey,
            'featured' => (bool) ($validated['featured'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'metadata' => $metadata,
        ];

        if ($typeConfig['type'] === 'teacher') {
            $attributes['category'] = $attributes['category'] ?: 'Tenaga Pendidik';
            $attributes['status'] = $attributes['status'] ?: 'published';
            if ($attributes['status'] === 'published' && blank($attributes['published_at'] ?? null)) {
                $attributes['published_at'] = now();
            }
        }

        return $attributes;
    }

    private function ensureEntryType(ContentEntry $entry, string $type): void
    {
        abort_unless($entry->type === $type, 404);
    }

    private function mediaCategory(string $type): string
    {
        return match ($type) {
            'teacher' => 'teacher',
            'program' => 'program',
            'news' => 'news',
            'gallery' => 'gallery',
            default => 'general',
        };
    }

    /**
     * Hard-delete content entries scoped to a validated type. IDs are always constrained by query scope.
     * Activity Log is preserved (never removed).
     *
     * @param  array<string, mixed>  $typeConfig
     */
    private function deleteEntries($query, array $typeConfig, PublicImageStorage $images): int
    {
        $entries = $query->where('type', $typeConfig['type'])->get(['id', 'title', 'image_path', 'type']);
        if ($entries->isEmpty()) {
            return 0;
        }

        $adminId = Auth::guard('admin')->id();
        $now = now();
        $activities = $entries->map(fn (ContentEntry $entry): array => [
            'admin_id' => $adminId,
            'content_type' => $entry->type,
            'content_id' => $entry->id,
            'action' => 'deleted',
            'title' => $entry->title,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::transaction(function () use ($entries, $activities): void {
            ContentEntry::query()->whereKey($entries->modelKeys())->delete();
            if ($activities !== []) {
                ContentActivity::query()->insert($activities);
            }
        });

        foreach ($entries->pluck('image_path')->filter()->unique() as $path) {
            $images->deleteIfUnused($path);
        }

        return $entries->count();
    }

    private function recordActivity(ContentEntry $entry, string $action): void
    {
        ContentActivity::query()->create([
            'admin_id' => Auth::guard('admin')->id(),
            'content_type' => $entry->type,
            'content_id' => $entry->id,
            'action' => $action,
            'title' => $entry->title,
        ]);
    }
}
