<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/design-system', function () {
    return Inertia::render('DesignSystemTest');
})->name('design-system');

Route::fallback(function () {
    return Inertia::render('Error', [
        'status' => 404,
        'message' => 'Halaman Tidak Ditemukan',
    ])->toResponse(request())->setStatusCode(404);
});
