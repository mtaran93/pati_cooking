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
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'slug', 'category_id', 'blurb', 'time_label', 'servings', 'difficulty', 'calories', 'photo']);

        return view('recipes.index', compact('recipes'));
    }

    /**
     * Full detail page. Route-model bound on slug.
     */
    public function show(Recipe $recipe): View
    {
        return view('recipes.show', compact('recipe'));
    }
}
