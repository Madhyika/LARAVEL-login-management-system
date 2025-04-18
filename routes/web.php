<?php

// use Illuminate\Support\Facades\Route;

// This is needed for Sanctum to issue the CSRF cookie
// Route::get('/sanctum/csrf-cookie', function () {
//     return response()->json(['csrf_cookie' => true]);
// });
// require __DIR__.'/auth.php';

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});