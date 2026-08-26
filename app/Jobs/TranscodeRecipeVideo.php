<?php

namespace App\Jobs;

use App\Models\RecipeMedia;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Transcode a non-web-safe recipe video (e.g. an iPhone `.mov`, usually HEVC)
 * into an H.264/AAC MP4 that plays in the HTML5 <video> element on every
 * browser, then repoint the media row at the new file and drop the original.
 */
class TranscodeRecipeVideo implements ShouldQueue
{
    use Queueable;

    /** Extensions that already play in-browser and need no transcoding. */
    private const array WEB_SAFE = ['mp4', 'webm'];

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(private readonly int $mediaId) {}

    public function handle(): void
    {
        $media = RecipeMedia::find($this->mediaId);

        // Gone, or already transcoded by an earlier run — nothing to do.
        if ($media === null || $media->type !== 'video') {
            return;
        }

        $extension = Str::lower(pathinfo($media->path, PATHINFO_EXTENSION));
        if (in_array($extension, self::WEB_SAFE, true)) {
            return;
        }

        $disk = Storage::disk('public');
        $source = $media->path;

        if (! $disk->exists($source)) {
            return;
        }

        $target = Str::beforeLast($source, '.').'.mp4';
        $sourcePath = $disk->path($source);
        $targetPath = $disk->path($target);

        $result = Process::timeout($this->timeout)->run([
            'ffmpeg', '-y',
            '-i', $sourcePath,
            '-c:v', 'libx264', '-preset', 'medium', '-crf', '23',
            '-pix_fmt', 'yuv420p',
            '-c:a', 'aac', '-b:a', '128k',
            '-movflags', '+faststart',
            $targetPath,
        ]);

        if ($result->failed()) {
            Log::error('Recipe video transcode failed', [
                'media_id' => $media->getKey(),
                'path' => $source,
                'stderr' => $result->errorOutput(),
            ]);

            // Clean up a partial output so a retry starts clean, then fail the
            // job so it retries / lands in failed_jobs.
            $disk->delete($target);

            throw new RuntimeException("ffmpeg exited with code {$result->exitCode()} for media {$media->getKey()}");
        }

        // Point the row at the MP4 (the `saved` hook skips web-safe paths, so
        // this update does not re-dispatch the job) and drop the original.
        $media->update(['path' => $target]);

        if ($target !== $source) {
            $disk->delete($source);
        }
    }
}
