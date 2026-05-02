<?php

use App\Http\Controllers\Api\PessoaResourceController;
use Illuminate\Support\Facades\Route;

Route::get('pessoas-list', [PessoaResourceController::class, 'index']);
Route::get('pessoas-list/{id}', [PessoaResourceController::class, 'show']);
Route::get('pessoas-sgm', [PessoaResourceController::class, 'indexSgm']);
