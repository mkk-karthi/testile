<?php

use App\Http\Controllers\ProductsController;
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

Route::get('/', [ProductsController::class, "index"]);
Route::get('/create', [ProductsController::class, "create"])->name("product.create");
Route::post('/store', [ProductsController::class, "store"])->name("product.store");
Route::get('/edit/{id}', [ProductsController::class, "edit"])->name("product.edit");
Route::post('/update', [ProductsController::class, "update"]);
Route::post('/delete/{id}', [ProductsController::class, "destroy"]);
Route::get('/state_options/{id}', [ProductsController::class, "state_options"])->name("state.options");
