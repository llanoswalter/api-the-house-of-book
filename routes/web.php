<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::put('/api/user/', [UserController::class, 'update'])->middleware('api.auth');
Route::get('/api/user/{id}', [UserController::class, 'index'])->middleware('api.auth');
Route::delete('/api/user/{id}', [UserController::class, 'destroy'])->middleware('api.auth');