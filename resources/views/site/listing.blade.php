@extends('site.layout')
@section('title', $page?->title ?? ucfirst($section))
@section('content')
<main class="subpage listing-page"><div class="subpage-heading"><span class="eyebrow">{{ \App\Models\SiteSetting::value('church_name') }} · {{ strtoupper($section) }}</span><h1>{{ $page?->title ?? ucfirst($section) }}</h1><p>{{ $page?->summary }}</p></div>
    <div class="listing-grid">@forelse($items as $item)<a class="listing-card" href="{{ route(['ministries'=>'detail.ministry','sermons'=>'detail.sermon','events'=>'detail.event','devotionals'=>'detail.devotional'][$section], ['slug' => $item->slug]) }}">
        @if($item->image)<div class="listing-image" style="background-image:url('{{ $item->image }}')"></div>@else<div class="listing-image {{ $type }}-image"><span>{{ $type === 'devotional' ? '✦' : ($type === 'event' ? '◷' : ($type === 'ministry' ? '✳' : '◉')) }}</span>@if($item->date)<small>{{ $item->date->format('M j') }}</small>@endif</div>@endif
        <div class="listing-card-body">@if($item->date)<small class="card-date">{{ $item->date->format('F j, Y') }}</small>@endif<h2>{{ $item->title }}</h2><p>{{ $item->summary }}</p><span class="arrow-link">Read more <span>→</span></span></div>
    </a>@empty<div class="empty-state">New items will be shared here soon. Check back again.</div>@endforelse</div>
</main>
@endsection
