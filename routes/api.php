<?php

use App\Http\Controllers\API\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('otp')->group(function () {
    Route::post('/get_otp', [OtpController::class, 'getOtp'])->name('otp.get_otp');
    Route::post('/validate_otp', [OtpController::class, 'validateOtp'])->name('otp.validate_otp');
});
