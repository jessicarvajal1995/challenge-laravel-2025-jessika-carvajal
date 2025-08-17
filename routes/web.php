<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas para documentación Swagger
Route::get('/api/documentation', function () {
    return view('swagger-ui');
});

Route::get('/api/docs', function () {
    $jsonPath = storage_path('api-docs/api-docs.json');
    if (file_exists($jsonPath)) {
        return response()->file($jsonPath, [
            'Content-Type' => 'application/json'
        ]);
    }
    return response()->json(['error' => 'Documentation not found'], 404);
});
