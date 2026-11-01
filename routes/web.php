<?php

use Illuminate\Support\Facades\Route;

// Beranda
Route::view('/', 'pages.home')->name('home');

// Program Keahlian
Route::view('/program/kuliner', 'pages.programs.kuliner')->name('program.kuliner');
Route::view('/program/dkv', 'pages.programs.dkv')->name('program.dkv');
Route::view('/program/tkj', 'pages.programs.tkj')->name('program.tkj');
Route::view('/program/ak', 'pages.programs.ak')->name('program.ak');
Route::view('/program/mp', 'pages.programs.mp')->name('program.mp');
Route::view('/program/bd', 'pages.programs.bd')->name('program.bd');

// Tenaga Kependidikan
Route::view('/tenaga-kependidikan', 'pages.teachers')->name('teachers');

// BKK
Route::view('/bkk/lowongan-pekerjaan', 'pages.bkk.jobs')->name('bkk.jobs');
Route::view('/bkk/tracer-study', 'pages.bkk.tracer-study')->name('bkk.tracer-study');

// SPMB
Route::view('/spmb', 'pages.spmb')->name('spmb');

// Tentang Kami
Route::view('/tentang-kami/informasi-sekolah', 'pages.about')->name('about');
