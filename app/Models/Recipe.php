<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

#[Fillable([
    'title', 'slug', 'blurb', 'note', 'time_label',
    'servings', 'difficulty', 'calories', 'ingredients', 'description',
])]
class Recipe extends Model
{
    protected $casts = [
        'ingredients' => 'array',
    ];

    /**
     * Difficulty is a 3-value enum.
     */
    public const DIFFICULTIES = ['Ușor', 'Mediu', 'Avansat'];

    protected static function booted(): void
    {
        static::saving(function (Recipe $recipe): void {
            // Auto-generate a unique slug from the title when one isn't set.
            if (blank($recipe->slug) && filled($recipe->title)) {
                $recipe->slug = static::uniqueSlug($recipe->title, $recipe->getKey());
            }

            // Sanitize the rich-text method before it is ever stored. It is later
            // rendered with {!! !!}, so this sanitization is non-negotiable.
            if ($recipe->isDirty('description') && filled($recipe->description)) {
                $recipe->description = Purifier::clean($recipe->description);
            }
        });
    }

    protected static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class);
    }

    /**
     * Gallery photos and videos, in display order. The first image is the
     * recipe's main/cover image (see coverMedia()).
     *
     * @return HasMany<RecipeMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(RecipeMedia::class)->orderBy('sort_order');
    }

    /**
     * The main image: the first photo in gallery order. Used as the card
     * thumbnail and the show-page hero. Null when the gallery has no images.
     */
    public function coverMedia(): ?RecipeMedia
    {
        return $this->media->firstWhere('type', 'image');
    }
}
