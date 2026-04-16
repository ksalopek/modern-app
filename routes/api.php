<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// This is the public endpoint for getting an API token.
Route::post('/login', [LoginController::class, 'store'])->name('api.login');

// This is a default route that comes with Laravel. It returns the currently
// authenticated user's data if they are logged in via Sanctum.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// We protect all our note API routes with the 'auth:sanctum' middleware.
// This ensures that only users with a valid API token can access them.
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('notes', NoteController::class);
});
