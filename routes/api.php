<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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

require __DIR__ . '/auth.php';

Route::get('/posts', [ApiController::class, 'posts']);
Route::get('/posts/category/{category}', [ApiController::class, 'categoryPosts']);
Route::get('/posts/tag/{tag}', [ApiController::class, 'tagPosts']);
Route::get('/post/{slug}', [ApiController::class, 'post']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/dashboard/posts', [PostController::class, 'index']);
    Route::post('/dashboard/posts/store', [PostController::class, 'store']);
    Route::get('/dashboard/posts/{slug}/edit', [PostController::class, 'edit']);
    Route::post('/dashboard/posts/{slug}/update', [PostController::class, 'update']);
});

Route::get('/dashboard/posts/categories', [PostController::class, 'categories']);
Route::get('/dashboard/posts/tags', [PostController::class, 'tags']);

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::patch('/user/update', [UserController::class, 'update']);
    Route::patch('/user/change-password', [UserController::class, 'changePassword']);
});
