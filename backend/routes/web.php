<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', static fn (): JsonResponse => response()->json([
    'name' => 'To-Do List API',
    'status' => 'ok',
]));
