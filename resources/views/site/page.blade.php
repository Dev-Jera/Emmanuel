@extends('site.layout')
@section('title', $page->title)
@section('content')
<main class="subpage listing-page"><div class="subpage-heading"><span class="eyebrow">{{ \App\Models\SiteSetting::value('church_name') }} · {{ strtoupper($page->slug) }}</span><h1>{{ $page->title }}</h1><p>{{ $page->summary }}</p></div>
    @if($page->slug === 'giving')<div class="giving-card"><div class="giving-symbol">✳</div><div><h2>Generosity changes lives.</h2><p>{!! nl2br(e($page->body)) !!}</p>@if(\App\Models\SiteSetting::value('giving_link'))<a class="button button-dark" target="_blank" rel="noopener" href="{{ \App\Models\SiteSetting::value('giving_link') }}">Give online <span>↗</span></a>@else<a class="button button-dark" href="{{ route('contact') }}">Get giving details <span>→</span></a>@endif</div></div>
    @else<div class="about-body article-body">{!! nl2br(e($page->body)) !!}</div><div class="about-cta"><h2>There is room for you here.</h2><a class="button button-dark" href="{{ route('contact') }}">Come meet us <span>→</span></a></div>@endif
</main>
@endsection
