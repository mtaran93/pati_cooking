<article class="card" style="gap:9px" role="listitem">
    <a href="{{ route('recipes.show', $recipe) }}" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:9px" aria-label="Deschide rețeta: {{ $recipe->title }}">
        <figure class="plate" style="margin:0 0 4px;aspect-ratio:4/3">
            @if ($recipe->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($recipe->photo) }}" alt="{{ $recipe->title }} — fotografia preparatului">
            @else
                <div class="plate-empty">{{ $recipe->title }}</div>
            @endif
        </figure>
        <div style="display:flex;align-items:center;gap:8px"><span class="card-kicker">{{ $recipe->category?->name }}</span></div>
        <h3 class="card-title" style="margin:0">{{ $recipe->title }}</h3>
        <p class="card-body">{{ $recipe->blurb }}</p>
        <div class="card-meta" style="font-feature-settings:'tnum' 1">{{ $recipe->time_label }}&nbsp;·&nbsp;{{ $recipe->servings }}&nbsp;porții&nbsp;·&nbsp;{{ $recipe->difficulty }}&nbsp;·&nbsp;{{ $recipe->calories }}&nbsp;kcal</div>
    </a>
</article>
