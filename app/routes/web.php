<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'code'          => 200,
        'status'        => 'OK',
        'timestamp'     => new \DateTime('NOW'),
        'response'      => 'Hiola mundo!!!',
    ], 200);
});
