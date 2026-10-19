<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/expenses', [ExpenseController::class, 'index']);
Route::post('/expenses', [ExpenseController::class, 'store']);
Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);
Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'receipt']);
