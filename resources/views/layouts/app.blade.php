<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Rețetele lui Pati')</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>body { margin: 0; text-wrap: pretty; }</style>
</head>
<body>
    <nav class="nav" style="flex-wrap:wrap;row-gap:9px;position:sticky;top:0;background:var(--color-bg);z-index:5">
        <a href="{{ route('recipes.index') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;margin-right:auto" aria-label="Către toate rețetele">
            <svg width="34" height="34" viewBox="0 0 32 32" fill="none" aria-hidden="true"><ellipse cx="16" cy="17" rx="9.5" ry="12.5" stroke="var(--color-accent)" stroke-width="1.5"></ellipse><circle cx="16" cy="20.5" r="4.5" fill="var(--color-accent)"></circle></svg>
            <span style="display:flex;flex-direction:column;line-height:1">
                <span class="nav-brand" style="margin:0;font-size:22px;line-height:1">Pati</span>
                <span style="font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:var(--color-accent-700);margin-top:3px">Rețete sănătoase</span>
            </span>
        </a>
        @if (($navCategories ?? collect())->isNotEmpty())
            <div class="nav-menu">
                @foreach ($navCategories as $category)
                    @continue ($category->subcategories->isEmpty())
                    <div class="nav-item">
                        <button type="button" class="nav-link" aria-haspopup="true">{{ $category->name }}
                            <svg width="9" height="9" viewBox="0 0 10 10" fill="none" aria-hidden="true"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </button>
                        <div class="megamenu">
                            @foreach ($category->subcategories as $sub)
                                <a href="{{ route('recipes.subcategory', $sub) }}" class="mega-recipe">{{ $sub->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </nav>

    <div style="max-width:1160px;margin:0 auto;padding:0 clamp(18px,5vw,64px)">
        @yield('content')
    </div>

    <footer style="border-top:1px solid var(--color-divider);padding:24px clamp(18px,5vw,64px);font-size:13px;color:color-mix(in srgb,var(--color-text) 60%, transparent)">© Pati. <span style="color:color-mix(in srgb,var(--color-text) 45%, transparent)">Avocado nu e tradițional. Pati știe și a hotărât să trăiască cu asta.</span></footer>
</body>
</html>
