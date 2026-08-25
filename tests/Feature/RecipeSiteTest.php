<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Recipes\Pages\CreateRecipe;
use App\Filament\Resources\Recipes\Pages\EditRecipe;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeSiteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<array{0: string, 1: string}>  $subcategories  [category, subcategory] pairs
     */
    private function makeRecipe(array $overrides = [], array $subcategories = [['Fel principal', 'Paste']]): Recipe
    {
        $recipe = Recipe::create(array_merge([
            'title' => 'Spaghetti alla carbonara',
            'blurb' => 'Ouă, guanciale, pecorino și piper.',
            'note' => 'Fără smântână.',
            'time_label' => '25 min',
            'servings' => 4,
            'difficulty' => 'Ușor',
            'calories' => 640,
            'ingredients' => ['400 g spaghete', '150 g guanciale'],
            'description' => '<ol><li>Primul pas</li><li>Al doilea</li></ol>',
        ], $overrides));

        $recipe->subcategories()->sync($this->subcategoryIds($subcategories));

        return $recipe;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $pairs
     * @return list<int>
     */
    private function subcategoryIds(array $pairs): array
    {
        return array_map(fn (array $pair): int => Subcategory::firstOrCreate([
            'category_id' => Category::firstOrCreate(['name' => $pair[0]])->id,
            'name' => $pair[1],
        ])->id, $pairs);
    }

    public function test_index_lists_recipes_with_calories(): void
    {
        $this->makeRecipe();

        $this->get('/')
            ->assertOk()
            ->assertSee('Spaghetti alla carbonara')
            ->assertSee('640')
            ->assertSee('kcal');
    }

    public function test_show_renders_method_and_note(): void
    {
        $recipe = $this->makeRecipe();

        $this->get("/recipes/{$recipe->slug}")
            ->assertOk()
            ->assertSee('recipe-method', escape: false)
            ->assertSee('Primul pas')
            ->assertSee('— Pati');
    }

    public function test_note_pull_quote_omitted_when_null(): void
    {
        $recipe = $this->makeRecipe(['note' => null]);

        // The pull-quote (a <blockquote> with the "— Pati" figcaption) must be
        // absent. Note: "— Pati" also appears in the page <title>, so assert on
        // the blockquote element specifically.
        $this->get("/recipes/{$recipe->slug}")
            ->assertOk()
            ->assertDontSee('<blockquote', escape: false);
    }

    public function test_slug_is_auto_generated_and_unique(): void
    {
        $a = $this->makeRecipe(['title' => 'Tort de ciocolată']);
        $b = $this->makeRecipe(['title' => 'Tort de ciocolată']);

        $this->assertSame('tort-de-ciocolata', $a->slug);
        $this->assertSame('tort-de-ciocolata-2', $b->slug);
    }

    public function test_description_is_sanitized_on_save(): void
    {
        $recipe = $this->makeRecipe([
            'description' => '<ol><li>Ok</li></ol><script>alert(1)</script>',
        ]);

        $this->assertStringNotContainsString('<script>', $recipe->fresh()->description);
    }

    public function test_recipe_media_type_inferred_from_extension(): void
    {
        $recipe = $this->makeRecipe();

        $video = $recipe->media()->create(['path' => 'recipes/media/a.mp4', 'sort_order' => 0]);
        $image = $recipe->media()->create(['path' => 'recipes/media/b.jpg', 'sort_order' => 1]);

        $this->assertSame('video', $video->fresh()->type);
        $this->assertSame('image', $image->fresh()->type);
    }

    public function test_cover_is_first_image_in_gallery_order(): void
    {
        $recipe = $this->makeRecipe();
        $first = $recipe->media()->create(['path' => 'recipes/media/a.jpg', 'sort_order' => 0]);
        $second = $recipe->media()->create(['path' => 'recipes/media/b.jpg', 'sort_order' => 1]);

        $this->assertSame($first->path, $recipe->load('media')->coverMedia()->path);

        // Reorder: b becomes first → it becomes the cover.
        $first->update(['sort_order' => 1]);
        $second->update(['sort_order' => 0]);

        $this->assertSame($second->path, $recipe->load('media')->coverMedia()->path);
    }

    public function test_card_and_hero_use_first_gallery_image(): void
    {
        $recipe = $this->makeRecipe();
        $recipe->media()->create(['path' => 'recipes/media/main.jpg', 'sort_order' => 0]);

        $this->get('/')->assertOk()->assertSee('recipes/media/main.jpg');
        $this->get("/recipes/{$recipe->slug}")->assertOk()->assertSee('recipes/media/main.jpg');
    }

    public function test_card_falls_back_to_placeholder_without_media(): void
    {
        $this->makeRecipe(['title' => 'Fără poză']);

        $this->get('/')
            ->assertOk()
            ->assertSee('plate-empty', escape: false);
    }

    public function test_show_gallery_excludes_cover_and_renders_video(): void
    {
        $recipe = $this->makeRecipe();
        $recipe->media()->create(['path' => 'recipes/media/cover.jpg', 'sort_order' => 0]);
        $recipe->media()->create(['path' => 'recipes/media/clip.mp4', 'sort_order' => 1]);

        // Cover shows in the hero; the video shows in the gallery/lightbox.
        $this->get("/recipes/{$recipe->slug}")
            ->assertOk()
            ->assertSee('Galerie')
            ->assertSee('recipes/media/clip.mp4')
            ->assertSee('<video', escape: false)
            ->assertSee('id="lightbox"', escape: false);
    }

    public function test_show_omits_gallery_when_only_a_cover_image(): void
    {
        $recipe = $this->makeRecipe();
        $recipe->media()->create(['path' => 'recipes/media/only.jpg', 'sort_order' => 0]);

        // The single image is the hero; there is nothing left for the gallery grid.
        $this->get("/recipes/{$recipe->slug}")
            ->assertOk()
            ->assertDontSee('id="lightbox"', escape: false);
    }

    public function test_admin_panel_requires_auth(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_recipe_resource_pages(): void
    {
        $admin = User::factory()->create();
        $recipe = $this->makeRecipe();

        $this->actingAs($admin);

        $this->get(ListRecipes::getUrl())->assertOk();
        $this->get(CreateRecipe::getUrl())->assertOk();
        $this->get(EditRecipe::getUrl(['record' => $recipe]))->assertOk();
    }

    public function test_admin_can_view_category_resource_pages(): void
    {
        $admin = User::factory()->create();
        $category = Category::create(['name' => 'Supe']);

        $this->actingAs($admin);

        $this->get(ListCategories::getUrl())->assertOk();
        $this->get(CreateCategory::getUrl())->assertOk();
        $this->get(EditCategory::getUrl(['record' => $category]))->assertOk();
    }

    public function test_index_renders_category_name(): void
    {
        $this->makeRecipe([], [['Deserturi speciale', 'Cremoase']]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Deserturi speciale');
    }

    public function test_index_renders_all_categories_of_a_multi_subcategory_recipe(): void
    {
        $this->makeRecipe([], [['Salate', 'Reci'], ['Aperitive', 'Reci']]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Salate')
            ->assertSee('Aperitive');
    }

    public function test_categories_are_capped_at_six(): void
    {
        foreach (['Unu', 'Doi', 'Trei', 'Patru', 'Cinci', 'Șase'] as $name) {
            Category::create(['name' => $name]);
        }

        $this->assertSame(Category::MAX, Category::count());

        $this->expectException(\RuntimeException::class);
        Category::create(['name' => 'Șapte']);
    }
}
