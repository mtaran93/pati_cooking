@extends('layouts.app')

@section('title', $recipe->title . ' — Pati')

@section('content')
    <div style="padding:clamp(24px,4vw,44px) 0 clamp(40px,6vw,72px)">
        <a href="{{ route('recipes.index') }}" class="btn btn-ghost" style="margin-bottom:24px">← Toate rețetele</a>

        <header style="max-width:60ch">
            <span class="card-kicker" style="font-size:12px;display:block;margin-bottom:12px">{{ $recipe->category?->name }}</span>
            <h1 style="font-weight:400;font-size:clamp(34px,5vw,58px);line-height:1.1;margin:0 0 12px;margin-left:-0.042em">{{ $recipe->title }}</h1>
            <p style="font-size:16px;line-height:1.7;margin:0 0 14px;color:color-mix(in srgb,var(--color-text) 78%, transparent)">{{ $recipe->blurb }}</p>

            @if ($recipe->note)
                <figure style="margin:0 0 18px;padding-left:16px;border-left:2px solid var(--color-accent);max-width:52ch">
                    <blockquote style="font-family:var(--font-heading);font-weight:400;font-size:21px;line-height:1.35;margin:0;text-indent:-0.34em">“{{ $recipe->note }}”</blockquote>
                    <figcaption style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--color-accent-700);margin-top:6px">— Pati</figcaption>
                </figure>
            @endif

            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
                <span class="tag tag-outline" style="font-feature-settings:'tnum' 1">{{ $recipe->time_label }}</span>
                <span class="tag tag-accent" style="font-feature-settings:'tnum' 1">{{ $recipe->servings }}&nbsp;porții</span>
                <span class="tag tag-neutral">{{ $recipe->difficulty }}</span>
            </div>
        </header>

        <hr class="hr" />

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(20px,4vw,44px);align-items:start;margin-top:clamp(20px,3vw,36px)">
            <figure class="plate" style="margin:0;aspect-ratio:4/3">
                @if ($recipe->photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($recipe->photo) }}" alt="{{ $recipe->title }} — fotografia preparatului">
                @else
                    <div class="plate-empty">{{ $recipe->title }}</div>
                @endif
            </figure>
            <aside class="card" style="gap:0">
                <h6 style="margin:0 0 10px;color:var(--color-accent-700)">Ingrediente</h6>
                <ul style="list-style:none;margin:0;padding:0;font-size:14px;line-height:1.5">
                    @foreach ($recipe->ingredients ?? [] as $ingredient)
                        <li style="padding:8px 0;border-top:1px solid var(--color-divider)">{{ $ingredient }}</li>
                    @endforeach
                </ul>
            </aside>
        </div>

        <section style="max-width:64ch;margin-top:clamp(28px,5vw,52px)">
            <h6 style="margin:0 0 6px;color:var(--color-accent-700)">Preparare</h6>
            <div class="recipe-method">{!! $recipe->description !!}</div>
        </section>
    </div>
@endsection
