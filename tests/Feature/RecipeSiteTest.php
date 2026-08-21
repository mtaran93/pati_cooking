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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeSiteTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecipe(array $overrides = []): Recipe
    {
        $categoryId = Category::firstOrCreate(['name' => 'Fel principal'])->id;

        return Recipe::create(array_merge([
            'title' => 'Spaghetti alla carbonara',
            'category_id' => $categoryId,
            'blurb' => 'Ouă, guanciale, pecorino și piper.',
            'note' => 'Fără smântână.',
            'time_label' => '25 min',
            'servings' => 4,
            'difficulty' => 'Ușor',
            'calories' => 640,
            'ingredients' => ['400 g spaghete', '150 g guanciale'],
            'description' => '<ol><li>Primul pas</li><li>Al doilea</li></ol>',
        ], $overrides));
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
        $category = Category::create(['name' => 'Deserturi speciale']);
        $this->makeRecipe(['category_id' => $category->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Deserturi speciale');
    }
}
