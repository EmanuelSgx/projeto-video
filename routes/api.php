<?php

use App\Http\Controllers\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Video routes
Route::prefix('videos')->group(function () {
    Route::get('/', [VideoController::class, 'index']);
    Route::post('/', [VideoController::class, 'store']);
    Route::get('/{uuid}', [VideoController::class, 'show']);
    Route::delete('/{uuid}', [VideoController::class, 'destroy']);
    Route::get('/validate/s3', [VideoController::class, 'validateS3']);
});
