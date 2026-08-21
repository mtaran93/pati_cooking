<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * The canonical categories used by the design's sample recipes.
     */
    public function run(): void
    {
        foreach (['Aperitive', 'Fel principal', 'Salate', 'Desert'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
