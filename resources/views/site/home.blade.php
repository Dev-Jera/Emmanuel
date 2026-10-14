@extends('site.layout')
@section('title', $page?->title ?? \App\Models\SiteSetting::value('church_name'))
@section('content')
<main>
    <section class="hero"><div class="hero-copy"><span class="eyebrow"><i></i> {{ \App\Models\SiteSetting::value('service_time') }} · {{ \App\Models\SiteSetting::value('location') }}</span>
        <h1>{{ $page?->title ?? \App\Models\SiteSetting::value('church_name') }}</h1><p>{{ $page?->summary ?? \App\Models\SiteSetting::value('tagline', 'A life saving ministry.') }}</p>
        <div class="hero-actions"><a href="{{ route('page', 'about') }}" class="button button-dark">Discover our church <span>→</span></a><a href="{{ route('contact') }}" class="button button-light">Plan your visit <span>↗</span></a></div>
    </div></section>
    <section class="pastor-section" aria-labelledby="pastor-heading">
        <div class="pastor-photo-frame">
            <img src="{{ \App\Models\SiteSetting::value('pastor_image') ?: '/css/pastor.png' }}" alt="{{ \App\Models\SiteSetting::value('pastor_name') ?: 'Our pastor' }}">
        </div>
        <div class="pastor-copy">
            <span class="eyebrow">A WORD FROM OUR PASTOR</span>
            <h2 id="pastor-heading">Welcome to Emmanuel Ministries</h2>
            <p>We believe church should be a place where you are welcomed, encouraged, and able to grow in faith. Whether this is your first visit or you have been part of our church family for years, there is a place for you here.</p>
            <span class="pastor-signoff">A life saving ministry.</span>
        </div>
    </section>
    <section class="upcoming-section" aria-labelledby="upcoming-heading">
        <div class="section-head">
            <div><span class="eyebrow">WHAT'S COMING UP</span><h2 id="upcoming-heading">Upcoming church events</h2></div>
            <a class="arrow-link" href="{{ route('listing.events') }}">See all events <span>→</span></a>
        </div>
        @if($events->isNotEmpty())
            <div class="event-marquee" aria-label="Upcoming church events">
                <div class="event-track">
                    <div class="event-track-group">
                        @foreach($events as $event)
                            @php($bannerImage = '/css/event'.(($loop->index % 3) + 1).'.jfif')
                            <a class="event-marquee-card" href="{{ route('listing.events') }}#event-{{ $event->slug }}">
                                <img src="{{ $bannerImage }}" alt="" loading="lazy">
                                <span class="event-marquee-copy">
                                    <time datetime="{{ $event->date->toDateString() }}">{{ $event->date->format('D, M j') }}</time>
                                    <strong>{{ $event->title }}</strong>
                                    <span>{{ $event->summary }}</span>
                                    <b>Event details <span aria-hidden="true">→</span></b>
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <div class="event-track-group" aria-hidden="true" inert>
                        @foreach($events as $event)
                            @php($bannerImage = '/css/event'.(($loop->index % 3) + 1).'.jfif')
                            <a class="event-marquee-card" href="{{ route('listing.events') }}#event-{{ $event->slug }}" tabindex="-1">
                                <img src="{{ $bannerImage }}" alt="" loading="lazy">
                                <span class="event-marquee-copy">
                                    <time>{{ $event->date->format('D, M j') }}</time>
                                    <strong>{{ $event->title }}</strong>
                                    <span>{{ $event->summary }}</span>
                                    <b>Event details <span aria-hidden="true">→</span></b>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <p class="no-upcoming-events">New church events will appear here soon.</p>
        @endif
    </section>
    <section class="ministries-section" aria-labelledby="ministries-heading">
        <div class="ministries-heading">
            <span class="eyebrow">OUR CHURCH DEPARTMENTS</span>
            <h2 id="ministries-heading">Ministries</h2>
            <p>Explore the departments that make up our church family. Find a place to serve, grow, and belong.</p>
        </div>
        <div class="ministries-grid">
            @forelse($ministries as $ministry)
                <a class="home-ministry-card" href="{{ route('detail.ministry', ['slug' => $ministry->slug]) }}">
                    <span class="ministry-number">{{ $loop->iteration }}</span>
                    <h3>{{ $ministry->title }}</h3>
                    <p>{{ $ministry->summary }}</p>
                    @if(filled($ministry->body))
                        <span class="ministry-details-preview">{{ \Illuminate\Support\Str::limit(trim(strip_tags($ministry->body)), 220) }}</span>
                    @endif
                    <span class="ministry-link">Explore ministry <span aria-hidden="true">→</span></span>
                </a>
            @empty
                <p class="ministries-empty">Our ministries will be listed here soon.</p>
            @endforelse
        </div>
    </section>
</main>
@endsection
