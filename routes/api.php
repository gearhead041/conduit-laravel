<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/user', [AuthController::class, 'getUser']);
Route::put('/user', [AuthController::class, 'update']);
Route::group(['prefix' => '/users'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/', [AuthController::class, 'register']);
});

Route::group(['prefix' => '/profiles/{username}'], function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::post('/follow', [ProfileController::class, 'follow']);
    Route::delete('/follow', [ProfileController::class, 'unfollow']);
});

Route::group(['prefix' => '/articles'], function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/feed', [ArticleController::class, 'feed']);
    Route::get('/{slug}', [ArticleController::class, 'show']);
    Route::post('/', [ArticleController::class, 'create']);
    //TODO 
    Route::put('/{slug}', [ArticleController::class, 'update']);
    Route::delete('/{slug}', [ArticleController::class, 'delete']);
    Route::post('/{slug}/favorite', [ArticleController::class, 'favorite']);
    Route::delete('/{slug}/favorite', [ArticleController::class, 'unfavorite']);
    Route::get('/{slug}/comments', [ArticleController::class, 'comments']);
    Route::post('/{slug}/comments', [ArticleController::class, 'addComment']);
    Route::delete('/{slug}/comments/{id}', [ArticleController::class, 'deleteComment']);
});

Route::group(['prefix' => '/tags'], function () {
    Route::get('/', [ArticleController::class, 'tags']);
});
