<?php

declare(strict_types=1);

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/subcategorii/{subcategory}', [RecipeController::class, 'subcategory'])->name('recipes.subcategory');
Route::get('/recipes/{recipe:slug}', [RecipeController::class, 'show'])->name('recipes.show');
