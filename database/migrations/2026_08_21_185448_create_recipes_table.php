<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('blurb', 300);
            $table->string('note')->nullable();
            $table->string('time_label');
            $table->smallInteger('servings');
            $table->string('difficulty'); // Ușor / Mediu / Avansat
            $table->integer('calories');
            $table->string('photo')->nullable();
            $table->json('ingredients');
            $table->text('description'); // sanitized HTML — the method (an <ol>)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
