<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['recipe_id', 'path', 'type', 'sort_order'])]
class RecipeMedia extends Model
{
    /**
     * Extensions treated as video; everything else is an image.
     */
    private const array VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'ogg', 'ogv'];

    protected static function booted(): void
    {
        static::saving(function (RecipeMedia $media): void {
            // The admin form only uploads a file — derive the type from its
            // extension so no separate type field is needed (seeders get it free).
            if (blank($media->type) && filled($media->path)) {
                $extension = Str::lower(pathinfo($media->path, PATHINFO_EXTENSION));
                $media->type = in_array($extension, self::VIDEO_EXTENSIONS, true) ? 'video' : 'image';
            }
        });
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Public URL of the stored file.
     */
    public function url(): string
    {
        return Storage::url($this->path);
    }
}
