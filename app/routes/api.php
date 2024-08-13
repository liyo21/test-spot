<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UrlController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('urls', [UrlController::class, 'index']);
Route::post('/url', [UrlController::class, 'create']);
Route::get('/urls/{shortenUrl}', [UrlController::class, 'show']);
Route::delete('/urls/{shortenUrl}', [UrlController::class, 'destroy']);
