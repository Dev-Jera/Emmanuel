@extends('site.layout')
@section('title', $entry->title)
@section('content')
<main class="subpage article-page"><a class="back-link" href="{{ route('listing.'.$section) }}">← Back to {{ $section }}</a><span class="eyebrow">{{ strtoupper($entry->type) }}@if($entry->date) · {{ $entry->date->format('F j, Y') }}@endif</span><h1>{{ $entry->title }}</h1><p class="article-summary">{{ $entry->summary }}</p>@if($entry->image)<img class="article-image" src="{{ $entry->image }}" alt="">@endif<div class="article-body">{!! nl2br(e($entry->body)) !!}</div>@if($entry->type === 'sermon')<a class="button button-dark" href="{{ route('contact') }}">Ask about this message <span>→</span></a>@endif</main>
@endsection
