@extends('layouts.app')

@section('title', $recipe->title . ' — Pati')

@section('content')
    @php($cover = $recipe->coverMedia())
    @php($gallery = $recipe->media->where('id', '!==', optional($cover)->id)->values())
    <div style="padding:clamp(24px,4vw,44px) 0 clamp(40px,6vw,72px)">
        <a href="{{ route('recipes.index') }}" class="btn btn-ghost" style="margin-bottom:24px">← Toate rețetele</a>

        <header style="max-width:60ch">
            <span class="card-kicker" style="font-size:12px;display:block;margin-bottom:12px">{{ $recipe->subcategories->pluck('category.name')->unique()->join(' · ') }}</span>
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
                @if ($cover)
                    <img src="{{ $cover->url() }}" alt="{{ $recipe->title }} — fotografia preparatului">
                @else
                    <div class="plate-empty">{{ $recipe->title }}</div>
                @endif
            </figure>
            <aside class="card" style="gap:0" data-base-servings="{{ $recipe->servings }}">
                <h6 style="margin:0 0 10px;color:var(--color-accent-700)">Ingrediente</h6>
                <div class="portion-picker">
                    <label for="portion-select">Porții</label>
                    <input type="number" id="portion-select" min="0.5" max="100" step="0.5" value="{{ $recipe->servings }}" inputmode="decimal" style="font-feature-settings:'tnum' 1">
                </div>
                <ul style="list-style:none;margin:0;padding:0;font-size:14px;line-height:1.5">
                    @foreach ($recipe->ingredients ?? [] as $ingredient)
                        <li style="padding:8px 0;border-top:1px solid var(--color-divider)" data-ingredient="{{ $ingredient }}">{{ $ingredient }}</li>
                    @endforeach
                </ul>

                <style>
                    .portion-picker{display:flex;align-items:center;gap:10px;margin:0 0 6px;padding-bottom:10px}
                    .portion-picker label{font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:var(--color-accent-700)}
                    .portion-picker input{font:inherit;font-size:14px;width:84px;color:var(--color-text);background:var(--color-surface,transparent);border:1px solid var(--color-divider);border-radius:8px;padding:6px 10px}
                    .portion-picker input:focus-visible{outline:2px solid var(--color-accent);outline-offset:1px}
                </style>

                <script>
                    (function () {
                        const card = document.querySelector('[data-base-servings]');
                        if (!card) return;
                        const input = card.querySelector('#portion-select');
                        const items = card.querySelectorAll('li[data-ingredient]');
                        const base = parseFloat(card.dataset.baseServings);
                        if (!input || !base || isNaN(base)) return;

                        function scaleNumber(raw, factor) {
                            const value = parseFloat(raw.replace(',', '.')) * factor;
                            if (isNaN(value)) return raw;
                            const rounded = Math.round(value * 100) / 100;
                            return String(rounded).replace('.', ',');
                        }

                        function update() {
                            const portions = parseFloat(String(input.value).replace(',', '.'));
                            if (isNaN(portions) || portions <= 0) return;
                            const factor = portions / base;
                            items.forEach(function (li) {
                                const original = li.dataset.ingredient;
                                li.textContent = original.replace(/\d+(?:[.,]\d+)?/g, function (m) {
                                    return scaleNumber(m, factor);
                                });
                            });
                        }

                        input.addEventListener('input', update);
                        update();
                    })();
                </script>
            </aside>
        </div>

        <section style="max-width:64ch;margin-top:clamp(28px,5vw,52px)">
            <h6 style="margin:0 0 6px;color:var(--color-accent-700)">Preparare</h6>
            <div class="recipe-method">{!! $recipe->description !!}</div>
        </section>

        @if ($gallery->isNotEmpty())
            <section style="margin-top:clamp(28px,5vw,52px)">
                <h6 style="margin:0 0 12px;color:var(--color-accent-700)">Galerie</h6>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:clamp(8px,1.5vw,14px)">
                    @foreach ($gallery as $item)
                        <button type="button" class="gallery-thumb" data-media-src="{{ $item->url() }}" data-media-type="{{ $item->type }}" aria-label="Deschide media {{ $loop->iteration }}">
                            @if ($item->type === 'video')
                                <video src="{{ $item->url() }}#t=0.1" muted preload="metadata" playsinline></video>
                                <span class="gallery-play" aria-hidden="true">▶</span>
                            @else
                                <img src="{{ $item->url() }}" alt="{{ $recipe->title }} — media {{ $loop->iteration }}" loading="lazy">
                            @endif
                        </button>
                    @endforeach
                </div>
            </section>

            <div id="lightbox" class="lightbox" hidden role="dialog" aria-modal="true" aria-label="Vizualizare media">
                <button type="button" class="lightbox-close" data-lightbox-close aria-label="Închide">×</button>
                <div class="lightbox-stage" data-lightbox-stage></div>
            </div>

            <style>
                .gallery-thumb{position:relative;display:block;padding:0;border:0;margin:0;cursor:pointer;background:var(--color-divider);border-radius:12px;overflow:hidden;aspect-ratio:1/1;width:100%}
                .gallery-thumb img,.gallery-thumb video{width:100%;height:100%;object-fit:cover;display:block}
                .gallery-thumb:focus-visible{outline:2px solid var(--color-accent);outline-offset:2px}
                .gallery-play{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:34px;color:#fff;text-shadow:0 1px 6px rgba(0,0,0,.55);background:rgba(0,0,0,.18);pointer-events:none}
                .lightbox{position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:clamp(16px,4vw,48px);background:rgba(0,0,0,.82)}
                .lightbox[hidden]{display:none}
                .lightbox-stage{max-width:100%;max-height:100%;display:flex;align-items:center;justify-content:center}
                .lightbox-stage img,.lightbox-stage video{max-width:100%;max-height:88vh;border-radius:10px;display:block}
                .lightbox-close{position:absolute;top:clamp(10px,2vw,20px);right:clamp(10px,2vw,20px);width:44px;height:44px;border:0;border-radius:50%;background:rgba(255,255,255,.14);color:#fff;font-size:26px;line-height:1;cursor:pointer}
                .lightbox-close:hover{background:rgba(255,255,255,.26)}
            </style>

            <script>
                (function () {
                    const lightbox = document.getElementById('lightbox');
                    if (!lightbox) return;
                    const stage = lightbox.querySelector('[data-lightbox-stage]');

                    function close() {
                        lightbox.hidden = true;
                        stage.replaceChildren();
                        document.body.style.overflow = '';
                    }

                    function open(src, type) {
                        const el = document.createElement(type === 'video' ? 'video' : 'img');
                        el.src = src;
                        if (type === 'video') {
                            el.controls = true;
                            el.autoplay = true;
                            el.playsInline = true;
                        } else {
                            el.alt = '';
                        }
                        stage.replaceChildren(el);
                        lightbox.hidden = false;
                        document.body.style.overflow = 'hidden';
                    }

                    document.querySelectorAll('.gallery-thumb').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            open(btn.dataset.mediaSrc, btn.dataset.mediaType);
                        });
                    });

                    lightbox.addEventListener('click', function (e) {
                        if (e.target === lightbox || e.target.hasAttribute('data-lightbox-close')) close();
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && !lightbox.hidden) close();
                    });
                })();
            </script>
        @endif
    </div>
@endsection
