@extends('site.layout')
@section('title', $page?->title ?? 'Sermons')
@section('content')
<main class="subpage sermons-page">
    <div class="subpage-heading">
        <span class="eyebrow">{{ \App\Models\SiteSetting::value('church_name') }} &middot; SERMONS</span>
        <h1>{{ $page?->title ?? 'Messages for everyday life' }}</h1>
        <p>{{ $page?->summary ?? 'Watch or listen to biblical teaching from Emmanuel Ministries.' }}</p>
    </div>

    <div class="sermon-library">
        <aside class="sermon-sidebar" aria-label="Sermon library">
            <div class="sermon-tabs" role="tablist" aria-label="Sermon format">
                <a role="tab" aria-selected="{{ $media === 'video' ? 'true' : 'false' }}" class="{{ $media === 'video' ? 'active' : '' }}" href="{{ route('listing.sermons', ['media' => 'video']) }}">Video sermons</a>
                <a role="tab" aria-selected="{{ $media === 'audio' ? 'true' : 'false' }}" class="{{ $media === 'audio' ? 'active' : '' }}" href="{{ route('listing.sermons', ['media' => 'audio']) }}">Audio sermons</a>
            </div>
            <div class="sermon-list" role="list">
                @forelse($availableItems as $item)
                    <a role="listitem" class="sermon-item {{ $selected?->is($item) ? 'selected' : '' }}" href="{{ route('listing.sermons', ['media' => $media, 'sermon' => $item->slug]) }}">
                        <span class="sermon-thumb" @if($item->image) style="background-image:url('{{ $item->image }}')" @endif aria-hidden="true"><span>{{ $media === 'video' ? '▶' : '♫' }}</span></span>
                        <span class="sermon-item-copy"><strong>{{ $item->title }}</strong><small>{{ $item->date?->format('M j, Y') ?? 'Sermon' }}</small></span>
                    </a>
                @empty
                    <div class="sermon-list-empty"><strong>No {{ $media }} sermons yet</strong><p>{{ $media === 'video' ? 'New messages will appear here as soon as they are shared.' : 'Audio versions of our messages will be added here soon.' }}</p></div>
                @endforelse
            </div>
        </aside>

        <section class="sermon-stage" aria-live="polite">
            @if($selected)
                @if($media === 'video')
                    @php
                        $videoId = null;
                        if (preg_match('~(?:youtu\\.be/|youtube\\.com/(?:watch\\?v=|shorts/|embed/))([A-Za-z0-9_-]{6,})~i', $selected->video_url, $matches)) $videoId = $matches[1];
                    @endphp
                    @if($videoId)
                        <div class="sermon-player"><iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}" title="{{ $selected->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
                    @endif
                @else
                    <div class="sermon-audio-player"><span class="sermon-audio-icon" aria-hidden="true">♫</span><p>Now listening</p><h2>{{ $selected->title }}</h2><audio controls preload="none" src="{{ $selected->audio_url }}">Your browser does not support audio playback.</audio></div>
                @endif
                <div class="sermon-stage-copy"><span class="eyebrow">{{ $media === 'video' ? 'VIDEO SERMON' : 'AUDIO SERMON' }}@if($selected->date) &middot; {{ $selected->date->format('F j, Y') }}@endif</span><h2>{{ $selected->title }}</h2><p>{{ $selected->summary }}</p></div>
            @else
                <div class="sermon-placeholder">
                    <span class="sermon-placeholder-icon" aria-hidden="true">{{ $media === 'video' ? '▶' : '♫' }}</span>
                    <span class="eyebrow">{{ $media === 'video' ? 'WATCH TOGETHER' : 'LISTEN ANYWHERE' }}</span>
                    <h2>{{ $media === 'video' ? 'A place for the next message.' : 'Take the message with you.' }}</h2>
                    <p>{{ $media === 'video' ? 'Our sermon videos will play here. Check back soon for teaching from Emmanuel Ministries.' : 'Audio sermons will be available here to listen to on your own time.' }}</p>
                </div>
            @endif
        </section>
    </div>
</main>
@endsection
