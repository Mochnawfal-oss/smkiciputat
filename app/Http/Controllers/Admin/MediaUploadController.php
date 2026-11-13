<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Support\MediaUploadService;
use App\Support\SiteConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaUploadController extends Controller
{
    public function store(Request $request, MediaUploadService $uploads): JsonResponse
    {
        $maxBytes = config('media.max_upload_mb') * 1024 * 1024;
        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:100'],
            'size' => ['required', 'integer', 'min:1', 'max:'.$maxBytes],
            'media_type' => ['required', Rule::in(['image', 'video'])],
            'category' => ['required', Rule::in(array_keys(config('media.categories')))],
            'quality' => ['required', Rule::in(['original', 'optimized', 'high_quality'])],
            'duration_seconds' => ['nullable', 'integer', 'between:0,86400'],
            'width' => ['nullable', 'integer', 'between:1,100000'],
            'height' => ['nullable', 'integer', 'between:1,100000'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'replace_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'source_filename' => ['nullable', 'required_with:source_size', 'string', 'max:255'],
            'source_mime_type' => ['nullable', 'string', 'max:100'],
            'source_size' => ['nullable', 'integer', 'min:1', 'max:'.$maxBytes],
        ]);
        if ($request->user('admin')?->isEditor()) {
            abort_if(
                $validated['media_type'] !== 'image' || filled($validated['replace_asset_id'] ?? null),
                403,
                'Editor hanya dapat mengunggah foto baru untuk konten.',
            );
        }
        abort_if(
            ($validated['media_type'] ?? null) === 'video' && isset($validated['source_size']),
            422,
            'File video tidak menerima file sumber tambahan.',
        );
        if (filled($validated['replace_asset_id'] ?? null)) {
            $previousAsset = MediaAsset::query()->findOrFail($validated['replace_asset_id']);
            abort_if(
                $previousAsset->media_type !== $validated['media_type'],
                422,
                'Jenis media pengganti harus sama dengan media saat ini.',
            );
        }

        return response()->json(
            $uploads->start($validated, (int) $request->user('admin')->id),
            201,
        );
    }

    public function show(Request $request, string $uploadId, MediaUploadService $uploads): JsonResponse
    {
        return response()->json($uploads->status($uploadId, (int) $request->user('admin')->id));
    }

    public function storeChunk(
        Request $request,
        string $uploadId,
        string $fileKey,
        int $chunkIndex,
        MediaUploadService $uploads,
    ): JsonResponse {
        abort_unless(in_array($fileKey, ['file', 'source_file'], true), 404);
        $validated = $request->validate([
            'chunk' => ['required', 'file', 'max:'.$uploads->chunkSizeKilobytes($uploadId, (int) $request->user('admin')->id)],
        ]);

        $uploads->storeChunk(
            $uploadId,
            $fileKey,
            $chunkIndex,
            $validated['chunk'],
            (int) $request->user('admin')->id,
        );

        return response()->json(['stored' => true]);
    }

    public function complete(Request $request, string $uploadId, MediaUploadService $uploads): JsonResponse
    {
        $asset = $uploads->complete($uploadId, (int) $request->user('admin')->id);

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
            'replacement_cleanup_pending' => $asset->replacement_cleanup_pending ?? false,
            'poster_url' => route('admin.media.poster', $asset),
        ], 201);
    }

    public function destroy(Request $request, string $uploadId, MediaUploadService $uploads): JsonResponse
    {
        $uploads->cancel($uploadId, (int) $request->user('admin')->id);

        return response()->json([], 204);
    }
}
