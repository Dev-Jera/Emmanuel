@extends('site.layout')
@section('title', $selected?->title ?? $page?->title ?? 'Ministries')
@section('content')
<main class="ministries-page">
    <header class="ministries-page-heading">
        <span class="eyebrow">EMMANUEL MINISTRIES</span>
        <h1>{{ $page?->title ?? 'Our ministries' }}</h1>
        @if($page?->summary)<p>{{ $page->summary }}</p>@else<p>Find a place to grow, serve, and belong in our church family.</p>@endif
    </header>

    @if($items->isNotEmpty())
        <nav class="ministry-tabs" aria-label="Choose a ministry">
            @foreach($items as $ministry)
                <a href="{{ route('detail.ministry', ['slug' => $ministry->slug]) }}" class="ministry-tab {{ $selected && $selected->is($ministry) ? 'is-active' : '' }}" @if($selected && $selected->is($ministry)) aria-current="page" @endif>{{ $ministry->title }}</a>
            @endforeach
        </nav>

        @if($selected)
            @php($activityPhotos = array_slice(array_merge($selected->gallery ?? [], ['/css/event1.jfif', '/css/event2.jfif', '/css/event3.jfif']), 0, 3))
            <article class="ministry-detail-panel">
                <div class="ministry-feature">
                    <img class="ministry-feature-image" src="{{ $selected->image ?: '/css/background.jfif' }}" alt="{{ $selected->title }} ministry">
                    <div class="ministry-feature-copy">
                        <span class="eyebrow">OUR CHURCH DEPARTMENTS</span>
                        <h2>{{ $selected->title }}</h2>
                        @if($selected->summary)<p class="ministry-feature-summary">{{ $selected->summary }}</p>@endif
                        @if($selected->body)<div class="ministry-feature-body">{!! nl2br(e($selected->body)) !!}</div>@endif
                    </div>
                </div>

                <section class="ministry-gallery" data-ministry-gallery aria-label="{{ $selected->title }} activities">
                    <div class="ministry-gallery-slides">
                        @foreach($activityPhotos as $photo)
                            <img class="ministry-gallery-slide {{ $loop->first ? 'is-active' : '' }}" src="{{ $photo }}" alt="{{ $selected->title }} activity {{ $loop->iteration }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        @endforeach
                    </div>
                    <div class="ministry-gallery-controls">
                        <button type="button" data-gallery-prev aria-label="Previous activity photo">‹</button>
                        <span data-gallery-status aria-live="polite">1 / {{ count($activityPhotos) }}</span>
                        <button type="button" data-gallery-next aria-label="Next activity photo">›</button>
                    </div>
                </section>

                <section class="ministry-join" id="join-form" aria-labelledby="join-heading">
                    <div class="ministry-join-copy"><span class="eyebrow">TAKE YOUR NEXT STEP</span><h2 id="join-heading">Join {{ $selected->title }}</h2><p>Tell us a little about yourself and the ministry team will get in touch.</p></div>
                    @if(session('ministry_joined'))<p class="ministry-join-success" role="status">Thanks for reaching out. Your request has been sent to the church team.</p>@endif
                    <form class="ministry-join-form" method="post" action="{{ route('ministries.join', ['slug' => $selected->slug]) }}">
                        @csrf
                        <label>Your name<input name="name" value="{{ old('name') }}" maxlength="120" required>@error('name')<small>{{ $message }}</small>@enderror</label>
                        <label>Email address<input type="email" name="email" value="{{ old('email') }}" maxlength="180" required>@error('email')<small>{{ $message }}</small>@enderror</label>
                        <label>Phone number <span>(optional)</span><input name="phone" value="{{ old('phone') }}" maxlength="80">@error('phone')<small>{{ $message }}</small>@enderror</label>
                        <label>Anything you’d like us to know? <span>(optional)</span><textarea name="message" rows="4" maxlength="2000">{{ old('message') }}</textarea>@error('message')<small>{{ $message }}</small>@enderror</label>
                        <button class="button button-dark" type="submit">Send join request <span>→</span></button>
                    </form>
                </section>
            </article>
        @endif
    @else
        <p class="ministries-no-items">Our ministries will be listed here soon.</p>
    @endif
</main>
<script>
document.querySelectorAll('[data-ministry-gallery]').forEach(gallery => {
    const slides = [...gallery.querySelectorAll('.ministry-gallery-slide')];
    if (slides.length < 2) return;
    let active = 0;
    let timer;
    const show = index => {
        active = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === active);
            slide.setAttribute('aria-hidden', String(i !== active));
        });
        gallery.querySelector('[data-gallery-status]').textContent = `${active + 1} / ${slides.length}`;
    };
    const start = () => {
        if (!timer) timer = window.setInterval(() => show(active + 1), 4000);
    };
    gallery.querySelector('[data-gallery-prev]').addEventListener('click', () => show(active - 1));
    gallery.querySelector('[data-gallery-next]').addEventListener('click', () => show(active + 1));
    start();
});
</script>
@endsection
