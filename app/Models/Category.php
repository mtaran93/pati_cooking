<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

#[Fillable(['name'])]
class Category extends Model
{
    /**
     * The top-level taxonomy is capped at 6 categories.
     */
    public const MAX = 6;

    protected static function booted(): void
    {
        static::creating(function (): void {
            if (static::count() >= self::MAX) {
                throw new RuntimeException('Maximum of '.self::MAX.' categories reached.');
            }
        });
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
}
