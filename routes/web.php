<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/player-photo/{filename}', function ($filename) {
    $filePath = base_path(
        'storage/app/public/players/' . $filename
    );

    if (!file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath);
});