<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;

Route::post('/quotes', [QuoteController::class, 'calculate']);
Route::get('/quotes', [QuoteController::class, 'index']);
