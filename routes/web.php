<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;


Route::get('/', [ImageController::class, 'show'])->name("home");

Route::get("/create", function () {
    return view("create");
});

Route::post('/create', [ImageController::class, 'store']);



Route::get('/images/downloadZip/{id}/{timestamp}', [ImageController::class, 'downloadZip'])
    ->name('images.downloadZip');



Route::get('/images/downloadImg/{id}/{timestamp}', [ImageController::class, 'downloadImg'])
    ->name('images.downloadImg');
