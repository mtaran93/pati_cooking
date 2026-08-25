<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Subcategory;
use Illuminate\Contracts\View\View;

class RecipeController extends Controller
{
    /**
     * Hero + card grid, all recipes newest first.
     */
    public function index(): View
    {
        $recipes = Recipe::query()
            ->with(['subcategories.category', 'media'])->latest()
            ->get(['id', 'title', 'slug', 'blurb', 'time_label', 'servings', 'difficulty', 'calories']);

        return view('recipes.index', ['recipes' => $recipes]);
    }

    /**
     * Card grid filtered to a single subcategory. Reuses the index view.
     */
    public function subcategory(Subcategory $subcategory): View
    {
        $recipes = $subcategory->recipes()
            ->with(['subcategories.category', 'media'])->latest()
            ->get(['recipes.id', 'title', 'slug', 'blurb', 'time_label', 'servings', 'difficulty', 'calories']);

        return view('recipes.index', [
            'recipes' => $recipes,
            'pageTitle' => $subcategory->name.' — Rețetele lui Pati',
            'kicker' => $subcategory->category->name,
            'heading' => $subcategory->name,
            'intro' => null,
        ]);
    }

    /**
     * Full detail page. Route-model bound on slug.
     */
    public function show(Recipe $recipe): View
    {
        $recipe->load('subcategories.category', 'media');

        return view('recipes.show', ['recipe' => $recipe]);
    }
}
