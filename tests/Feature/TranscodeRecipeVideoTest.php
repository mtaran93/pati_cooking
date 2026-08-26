<?php

namespace Tests\Feature;

use App\Jobs\TranscodeRecipeVideo;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TranscodeRecipeVideoTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecipe(): Recipe
    {
        return Recipe::create([
            'title' => 'Spaghetti alla carbonara',
            'time_label' => '25 min',
            'servings' => 4,
            'difficulty' => 'Ușor',
            'calories' => 640,
            'ingredients' => ['400 g spaghete'],
            'description' => '<ol><li>Primul pas</li></ol>',
        ]);
    }

    public function test_mov_upload_dispatches_transcode_job(): void
    {
        Bus::fake();
        $recipe = $this->makeRecipe();

        $media = $recipe->media()->create(['path' => 'recipes/media/clip.mov', 'sort_order' => 0]);

        Bus::assertDispatched(
            TranscodeRecipeVideo::class,
            fn (TranscodeRecipeVideo $job): bool => $this->jobTargets($job, $media->getKey()),
        );
    }

    public function test_web_safe_video_and_images_do_not_dispatch(): void
    {
        Bus::fake();
        $recipe = $this->makeRecipe();

        $recipe->media()->create(['path' => 'recipes/media/clip.mp4', 'sort_order' => 0]);
        $recipe->media()->create(['path' => 'recipes/media/photo.jpg', 'sort_order' => 1]);

        Bus::assertNotDispatched(TranscodeRecipeVideo::class);
    }

    public function test_job_transcodes_to_mp4_and_removes_original(): void
    {
        Storage::fake('public');
        Bus::fake(); // don't auto-dispatch on create; run the job by hand below
        Process::fake();

        $recipe = $this->makeRecipe();
        Storage::disk('public')->put('recipes/media/clip.mov', 'fake-mov-bytes');
        $media = $recipe->media()->create(['path' => 'recipes/media/clip.mov', 'sort_order' => 0]);

        // Process::fake() returns success by default; simulate ffmpeg writing the output.
        Storage::disk('public')->put('recipes/media/clip.mp4', 'fake-mp4-bytes');

        (new TranscodeRecipeVideo($media->getKey()))->handle();

        $this->assertSame('recipes/media/clip.mp4', $media->fresh()->path);
        Storage::disk('public')->assertMissing('recipes/media/clip.mov');
        Storage::disk('public')->assertExists('recipes/media/clip.mp4');

        Process::assertRan(function ($process): bool {
            $cmd = $process->command;
            $cmd = is_array($cmd) ? implode(' ', $cmd) : (string) $cmd;

            return str_contains($cmd, 'ffmpeg')
                && str_contains($cmd, 'libx264')
                && str_contains($cmd, 'yuv420p')
                && str_contains($cmd, '+faststart');
        });
    }

    public function test_job_is_noop_for_already_web_safe_media(): void
    {
        Storage::fake('public');
        Bus::fake();
        Process::fake();

        $recipe = $this->makeRecipe();
        $media = $recipe->media()->create(['path' => 'recipes/media/clip.mp4', 'sort_order' => 0]);

        (new TranscodeRecipeVideo($media->getKey()))->handle();

        Process::assertNothingRan();
        $this->assertSame('recipes/media/clip.mp4', $media->fresh()->path);
    }

    private function jobTargets(TranscodeRecipeVideo $job, int $expectedId): bool
    {
        $reflected = new \ReflectionProperty($job, 'mediaId');

        return $reflected->getValue($job) === $expectedId;
    }
}
