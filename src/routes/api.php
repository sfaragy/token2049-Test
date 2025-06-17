<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\CheckoutController;
use App\Http\Controllers\API\V1\HealthController;
use App\Http\Controllers\API\V1\WebhookController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::post('/webhook', [WebhookController::class, 'handleCoinbase']);
    Route::get('/health', [HealthController::class, 'status']);
});

Route::fallback(function (Request $request) {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found.',
        'requested_url' => $request->fullUrl()
    ], 404);
});
