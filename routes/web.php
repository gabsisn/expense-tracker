<?php

use App\Models\Expense;
use Illuminate\Support\Facades\Route;

// Server-renders the rows into the page so `curl /` shows real data, then Vue takes over.
Route::get('/', fn () => view('app', [
    'expenses' => Expense::orderByDesc('spent_on')->orderByDesc('id')->get(),
]));
