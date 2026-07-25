<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Stateless, token-based (Laravel Sanctum) routes for the Android app.
| Entirely separate from the web session-based auth used by the browser
| app and the admin panel — a token issued here has no relationship to a
| browser session, and vice versa.
|
*/

// ── PUBLIC ──
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ── REQUIRES A VALID BEARER TOKEN ──
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::get('/dashboard', [ServiceRequestController::class, 'dashboard']);

    Route::get('/printing/options', [ServiceRequestController::class, 'printingOptions']);
    Route::post('/printing',        [ServiceRequestController::class, 'storePrinting']);

    Route::get('/photocopy/options', [ServiceRequestController::class, 'photocopyOptions']);
    Route::post('/photocopy',        [ServiceRequestController::class, 'storePhotocopy']);

    Route::get('/research/options', [ServiceRequestController::class, 'researchOptions']);
    Route::post('/research',        [ServiceRequestController::class, 'storeResearch']);

    Route::get('/requests',                    [ServiceRequestController::class, 'history']);
    Route::post('/requests/{serviceRequest}/extend', [ServiceRequestController::class, 'requestExtend']);
    Route::get('/requests/{serviceRequest}/receipt', [ServiceRequestController::class, 'downloadReceipt']);
    Route::post('/detect-pages',               [ServiceRequestController::class, 'detectPages']);

    Route::get('/profile',            [ProfileController::class, 'show']);
    Route::post('/profile',           [ProfileController::class, 'update']);
    Route::put('/profile/password',   [ProfileController::class, 'updatePassword']);
    Route::post('/profile/deactivate',[ProfileController::class, 'requestDeactivation']);
    Route::post('/profile/delete',    [ProfileController::class, 'requestDeletion']);
});