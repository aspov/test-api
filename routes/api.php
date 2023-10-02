<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
Route::get('/register', [ApiController::class, 'register'])->name('register');
Route::get('/login', [ApiController::class, 'login'])->name('login');
Route::get('/refresh', [ApiController::class, 'refresh'])->name('refresh');
Route::get('/save-location', [ApiController::class, 'saveLocation'])->name('saveLocation');
Route::get('/get-location', [ApiController::class, 'getLocation'])->name('getLocation');
