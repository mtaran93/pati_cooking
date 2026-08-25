@extends('layouts.app')

@section('title', 'Rețetele lui Pati')

@section('content')
    <header style="padding:clamp(36px,7vw,84px) 0 clamp(24px,4vw,48px)">
        <span style="display:block;font-size:12px;letter-spacing:0.1em;text-transform:uppercase;color:var(--color-accent-700);font-feature-settings:'tnum' 1;margin-bottom:14px">Din bucătăria familiei</span>
        <h1 style="font-weight:400;font-size:clamp(40px,6vw,74px);line-height:1.08;letter-spacing:-0.01em;margin:0 0 16px;margin-left:-0.042em">Rețetele lui Pati.</h1>
        <p style="font-size:16px;line-height:1.7;max-width:58ch;margin:0;color:color-mix(in srgb,var(--color-text) 78%, transparent)">Sunt Pati și gătesc rețetele clasice așa cum vreau să le mănânc mereu — ingrediente adevărate, ulei de măsline bun, mai multe legume decât ar recunoaște bunica și un avocado oriunde își merită locul. Mâncare sănătoasă care nu se laudă că e sănătoasă.</p>
    </header>
    <hr class="hr" style="margin:0 0 clamp(24px,4vw,40px)" />

    @if ($recipes->isEmpty())
        <p style="padding:clamp(20px,4vw,48px) 0;color:color-mix(in srgb,var(--color-text) 60%, transparent)">Încă nu există rețete. Revino în curând.</p>
    @else
        <div role="list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:clamp(18px,3vw,28px);padding-bottom:clamp(40px,6vw,72px)">
            @foreach ($recipes as $recipe)
                @include('recipes.partials.card', ['recipe' => $recipe])
            @endforeach
        </div>
    @endif
@endsection
