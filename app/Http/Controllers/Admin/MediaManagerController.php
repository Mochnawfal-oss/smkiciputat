<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Support\MediaLibrary;
use App\Support\MediaUsage;
use App\Support\SiteConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaManagerController extends Controller
{
    public function index(Request $request, MediaLibrary $library, MediaUsage $usage): View
    {
        $library->indexReferencedFiles();
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(['image', 'video'])],
            'category' => ['nullable', Rule::in(array_keys(config('media.categories')))],
        ]);
        $search = trim($validated['search'] ?? '');

        $assets = MediaAsset::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('filename', 'like', '%'.$search.'%')
                    ->orWhere('alt_text', 'like', '%'.$search.'%')
                    ->orWhere('caption', 'like', '%'.$search.'%');
            }))
            ->when($validated['type'] ?? null, fn ($query, string $type) => $query->where('media_type', $type))
            ->when($validated['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'assets' => $assets,
            'usages' => $usage->forAssets($assets->getCollection()),
            'search' => $search,
            'type' => $validated['type'] ?? '',
            'category' => $validated['category'] ?? '',
            'categories' => config('media.categories'),
        ]);
    }

    public function store(Request $request, MediaLibrary $library): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:'.(config('media.max_upload_mb') * 1024)],
            'source_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:'.(config('media.max_upload_mb') * 1024)],
            'category' => ['required', Rule::in(array_keys(config('media.categories')))],
            'quality' => ['required', Rule::in(['original', 'optimized', 'high_quality'])],
            'duration_seconds' => ['nullable', 'integer', 'between:0,86400'],
            'width' => ['nullable', 'integer', 'between:1,100000'],
            'height' => ['nullable', 'integer', 'between:1,100000'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'replace_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
        ]);
        $previousAsset = filled($validated['replace_asset_id'] ?? null)
            ? MediaAsset::query()->findOrFail($validated['replace_asset_id'])
            : null;
        if ($request->user('admin')?->isEditor()) {
            abort_if($previousAsset !== null, 403, 'Editor tidak dapat mengganti media yang sudah tersimpan.');
        }
        $file = $request->file('file');
        $mimeType = $file->getMimeType() ?: '';
        $extension = strtolower($file->getClientOriginalExtension());
        $imageExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
            'image/avif' => ['avif'],
        ];
        $videoExtensions = [
            'video/mp4' => ['mp4', 'm4v'],
            'video/webm' => ['webm'],
            'video/ogg' => ['ogv', 'ogg'],
        ];

        if ($request->user('admin')?->isEditor()) {
            abort_unless(
                isset($imageExtensions[$mimeType]) && in_array($extension, $imageExtensions[$mimeType], true),
                403,
                'Editor hanya dapat mengunggah foto untuk konten.',
            );
        }

        if (isset($imageExtensions[$mimeType]) && in_array($extension, $imageExtensions[$mimeType], true)) {
            abort_if($previousAsset !== null && $previousAsset->media_type !== 'image', 422, 'Jenis media pengganti harus sama dengan media saat ini.');
            $asset = $library->storeImage(
                $file,
                $validated['category'],
                $validated['quality'],
                $request->file('source_file'),
                adminId: $request->user('admin')?->id,
                altText: $validated['alt_text'] ?? null,
            );
        } elseif (isset($videoExtensions[$mimeType]) && in_array($extension, $videoExtensions[$mimeType], true)) {
            abort_if($previousAsset !== null && $previousAsset->media_type !== 'video', 422, 'Jenis media pengganti harus sama dengan media saat ini.');
            $asset = $library->storeVideo(
                $file,
                $validated['category'],
                $validated['quality'],
                $request->user('admin')?->id,
                [
                    'duration_seconds' => $validated['duration_seconds'] ?? null,
                    'width' => $validated['width'] ?? null,
                    'height' => $validated['height'] ?? null,
                ],
            );
        } else {
            throw ValidationException::withMessages([
                'file' => 'Format atau MIME type media tidak didukung.',
            ]);
        }
        $cleanupPending = $previousAsset !== null && ! $library->replaceReferences($previousAsset, $asset);
        $asset->setAttribute('replacement_cleanup_pending', $cleanupPending);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $asset->id,
                'type' => $asset->media_type,
                'name' => $asset->filename,
                'path' => $asset->publicPath(),
                'url' => app(SiteConfiguration::class)->imageUrl($asset->publicPath()),
                'width' => $asset->width,
                'height' => $asset->height,
                'duration_seconds' => $asset->duration_seconds,
                'file_size' => $asset->file_size,
                'optimized_size' => $asset->optimized_size,
                'replacement_cleanup_pending' => $cleanupPending,
                'poster_url' => route('admin.media.poster', $asset),
            ], 201);
        }

        return redirect()->route('admin.media.index')->with(
            'success',
            $cleanupPending
                ? 'Media diganti. File media lama masih tersimpan dan dapat dihapus setelah diperiksa.'
                : 'Media berhasil diunggah.',
        );
    }

    public function update(Request $request, MediaAsset $media): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(config('media.categories')))],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ]);

        $media->update($validated);

        return back()->with('success', 'Informasi media berhasil diperbarui.');
    }

    public function destroy(MediaAsset $media, MediaLibrary $library): RedirectResponse
    {
        if (! $library->delete($media)) {
            return back()->withErrors([
                'media' => 'Media masih digunakan oleh konten atau file gagal dihapus dari storage. Lepaskan referensi dan periksa storage sebelum mencoba lagi.',
            ]);
        }

        return back()->with('success', 'Media berhasil dihapus.');
    }

    public function poster(Request $request, MediaAsset $media, MediaLibrary $library): JsonResponse
    {
        abort_unless($media->media_type === 'video', 404);
        $validated = $request->validate([
            'poster' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:8192'],
        ]);
        $poster = $request->file('poster');
        abort_unless($poster instanceof UploadedFile, 422);

        $library->attachPoster($media, $poster);
        $media->refresh();

        return response()->json([
            'poster' => app(SiteConfiguration::class)->imageUrl($media->poster_path),
        ]);
    }

    public function download(MediaAsset $media): StreamedResponse
    {
        abort_unless($media->media_type === 'image', 404);
        abort_unless(Storage::disk($media->disk)->exists($media->original_path), 404);

        return Storage::disk($media->disk)->download($media->original_path, $media->filename);
    }
}
