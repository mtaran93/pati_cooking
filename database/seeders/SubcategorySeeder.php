<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    /**
     * Named subcategories under each canonical category. Keyed by category name;
     * self-sufficient if CategorySeeder was skipped.
     */
    public function run(): void
    {
        $tree = [
            'Aperitive' => ['Bruschete', 'Reci'],
            'Fel principal' => ['Paste', 'Risotto'],
            'Salate' => ['Reci'],
            'Desert' => ['Cremoase'],
        ];

        foreach ($tree as $categoryName => $names) {
            $categoryId = Category::firstOrCreate(['name' => $categoryName])->id;

            foreach ($names as $name) {
                Subcategory::firstOrCreate([
                    'category_id' => $categoryId,
                    'name' => $name,
                ]);
            }
        }
    }
}
