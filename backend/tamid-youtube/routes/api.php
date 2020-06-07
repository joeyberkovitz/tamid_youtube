<?php

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

// User registration, no need for auth
Route::post('/register', 'UserController@register');

// Requests that require authentication
Route::middleware('auth:api')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/search/history', 'SearchController@history');
});

// Search request, doesn't require auth, but uses it for logging if available
Route::middleware('optionalauth:api')->get('/search', 'SearchController@search');
