<?php

namespace App\Models;

use App\Jobs\TranscodeRecipeVideo;
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

    /** Video extensions that already play in-browser and need no transcoding. */
    private const array WEB_SAFE_VIDEO_EXTENSIONS = ['mp4', 'webm'];

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

        static::saved(function (RecipeMedia $media): void {
            // Convert non-web-safe videos (e.g. iPhone .mov/HEVC) to MP4 so they
            // play in every browser. The job rewrites `path` to .mp4, which is
            // web-safe, so it never re-dispatches itself.
            if ($media->type !== 'video') {
                return;
            }

            if (! $media->wasRecentlyCreated && ! $media->wasChanged('path')) {
                return;
            }

            $extension = Str::lower(pathinfo($media->path, PATHINFO_EXTENSION));
            if (in_array($extension, self::WEB_SAFE_VIDEO_EXTENSIONS, true)) {
                return;
            }

            dispatch(new TranscodeRecipeVideo($media->getKey()));
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
