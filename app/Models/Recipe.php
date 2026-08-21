<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class Recipe extends Model
{
    protected $fillable = [
        'title', 'slug', 'category_id', 'blurb', 'note', 'time_label',
        'servings', 'difficulty', 'calories', 'photo', 'ingredients', 'description',
    ];

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
