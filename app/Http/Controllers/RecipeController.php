<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;

class RecipeController extends Controller
{
    /**
     * Hero + card grid, all recipes newest first.
     */
    public function index(): View
    {
        $recipes = Recipe::query()
            ->with('category:id,name')->latest()
            ->get(['id', 'title', 'slug', 'category_id', 'blurb', 'time_label', 'servings', 'difficulty', 'calories', 'photo']);

        return view('recipes.index', ['recipes' => $recipes]);
    }

    /**
     * Full detail page. Route-model bound on slug.
     */
    public function show(Recipe $recipe): View
    {
        return view('recipes.show', ['recipe' => $recipe]);
    }
}
