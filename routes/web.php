<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ContentEntryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaManagerController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/program/kuliner', [ProgramController::class, 'legacy'])->defaults('programKey', 'kuliner')->name('program.kuliner');
Route::get('/program/dkv', [ProgramController::class, 'legacy'])->defaults('programKey', 'dkv')->name('program.dkv');
Route::get('/program/tkj', [ProgramController::class, 'legacy'])->defaults('programKey', 'tkj')->name('program.tkj');
Route::get('/program/ak', [ProgramController::class, 'legacy'])->defaults('programKey', 'ak')->name('program.ak');
Route::get('/program/mp', [ProgramController::class, 'legacy'])->defaults('programKey', 'mp')->name('program.mp');
Route::get('/program/bd', [ProgramController::class, 'legacy'])->defaults('programKey', 'bd')->name('program.bd');
Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('program.show');

Route::get('/tenaga-kependidikan', [ContentController::class, 'teachers'])->name('teachers');
Route::get('/berita', [ContentController::class, 'news'])->name('news.index');
Route::get('/berita/{slug}', [ContentController::class, 'article'])->name('news.show');
Route::get('/galeri', [ContentController::class, 'gallery'])->name('gallery');
Route::get('/bkk/lowongan-pekerjaan', [ContentController::class, 'jobs'])->name('bkk.jobs');
Route::get('/bkk/tracer-study', [ContentController::class, 'tracerStudy'])->name('bkk.tracer-study');
Route::get('/spmb', [ContentController::class, 'spmb'])->name('spmb');
Route::get('/tentang-kami/informasi-sekolah', [ContentController::class, 'about'])->name('about');

Route::prefix('adminsmkiciputat')->name('admin.')->group(function (): void {
    Route::get('/', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/', [AdminLoginController::class, 'store'])->middleware('throttle:admin-login')->name('login.submit');

    Route::middleware('auth:admin')->group(function (): void {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/media', [MediaManagerController::class, 'index'])->name('media.index');
        Route::post('/media/uploads', [MediaUploadController::class, 'store'])->name('media.uploads.store');
        Route::get('/media/uploads/{uploadId}', [MediaUploadController::class, 'show'])->whereUuid('uploadId')->name('media.uploads.show');
        Route::post('/media/uploads/{uploadId}/chunks/{fileKey}/{chunkIndex}', [MediaUploadController::class, 'storeChunk'])
            ->whereUuid('uploadId')
            ->where('fileKey', 'file|source_file')
            ->whereNumber('chunkIndex')
            ->name('media.uploads.chunk');
        Route::post('/media/uploads/{uploadId}/complete', [MediaUploadController::class, 'complete'])->whereUuid('uploadId')->name('media.uploads.complete');
        Route::delete('/media/uploads/{uploadId}', [MediaUploadController::class, 'destroy'])->whereUuid('uploadId')->name('media.uploads.destroy');
        Route::post('/media', [MediaManagerController::class, 'store'])->name('media.store');
        Route::put('/media/{media}/poster', [MediaManagerController::class, 'poster'])->whereNumber('media')->name('media.poster');
        Route::put('/media/{media}', [MediaManagerController::class, 'update'])->whereNumber('media')->name('media.update');
        Route::delete('/media/{media}', [MediaManagerController::class, 'destroy'])->whereNumber('media')->name('media.destroy');
        Route::get('/media/{media}/original', [MediaManagerController::class, 'download'])->whereNumber('media')->name('media.original');

        Route::get('/tenaga-kependidikan', [TeacherController::class, 'index'])->name('teachers.index');
        Route::post('/tenaga-kependidikan/bulk-delete', [TeacherController::class, 'bulkDestroy'])->name('teachers.bulk-destroy');
        Route::post('/tenaga-kependidikan/empty', [TeacherController::class, 'empty'])->name('teachers.empty');
        Route::get('/tenaga-kependidikan/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/tenaga-kependidikan', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/tenaga-kependidikan/{teacher}/edit', [TeacherController::class, 'edit'])->whereNumber('teacher')->name('teachers.edit');
        Route::put('/tenaga-kependidikan/{teacher}', [TeacherController::class, 'update'])->whereNumber('teacher')->name('teachers.update');
        Route::delete('/tenaga-kependidikan/{teacher}', [TeacherController::class, 'destroy'])->whereNumber('teacher')->name('teachers.destroy');

        Route::get('/content/{type}', [ContentEntryController::class, 'index'])->name('content.index');
        Route::post('/content/{type}/bulk-delete', [ContentEntryController::class, 'bulkDestroy'])->name('content.bulk-destroy');
        Route::post('/content/{type}/empty', [ContentEntryController::class, 'empty'])->name('content.empty');
        Route::get('/content/{type}/create', [ContentEntryController::class, 'create'])->name('content.create');
        Route::post('/content/{type}', [ContentEntryController::class, 'store'])->name('content.store');
        Route::get('/content/{type}/{entry}/edit', [ContentEntryController::class, 'edit'])->whereNumber('entry')->name('content.edit');
        Route::put('/content/{type}/{entry}', [ContentEntryController::class, 'update'])->whereNumber('entry')->name('content.update');
        Route::delete('/content/{type}/{entry}', [ContentEntryController::class, 'destroy'])->whereNumber('entry')->name('content.destroy');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::post('/admins/bulk-delete', [AdminUserController::class, 'bulkDestroy'])->name('admins.bulk-destroy');
        Route::resource('/admins', AdminUserController::class)
            ->except(['show'])
            ->parameters(['admins' => 'admin']);
    });
});
