<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recipes', 'photo')) {
            return;
        }

        // The gallery is now the single source of truth for a recipe's images.
        // Fold each existing cover photo in as the first gallery item (sort_order 0),
        // pushing any already-present media down.
        DB::table('recipes')->whereNotNull('photo')->orderBy('id')->each(function (object $recipe): void {
            DB::table('recipe_media')->where('recipe_id', $recipe->id)->increment('sort_order');

            DB::table('recipe_media')->insert([
                'recipe_id' => $recipe->id,
                'path' => $recipe->photo,
                'type' => 'image',
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn('photo');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->string('photo')->nullable();
        });
    }
};
