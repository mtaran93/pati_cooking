<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Feed the header menu (categories + their subcategories) to every page.
        View::composer('layouts.app', function ($view): void {
            $view->with('navCategories', Category::with('subcategories')->orderBy('id')->get());
        });
    }
}
