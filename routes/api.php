<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
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

// route pasien yang dilindungi sanctum
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::get('/patients/search/{name}', [PatientController::class, 'search']);
    Route::get('/patients/status/{status}', [PatientController::class, 'status']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::put('/patients/{id}', [PatientController::class, 'update']);
    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

// route untuk login autentikasi sanctum
// data user login ada di seeder
Route::post('/login', [AuthController::class, 'login']);


