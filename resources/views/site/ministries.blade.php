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
            @php($featurePhotos = array_slice(array_values(array_unique(array_filter(array_merge([$selected->image ?: '/css/background.jfif'], $selected->gallery ?? [], ['/css/event1.jfif', '/css/event2.jfif', '/css/event3.jfif'])))), 0, 3))
            <article class="ministry-detail-panel">
                <div class="ministry-feature">
                    <div class="ministry-feature-media" data-ministry-feature>
                        <img class="ministry-feature-sizer" src="{{ $featurePhotos[0] }}" alt="" aria-hidden="true">
                        @foreach($featurePhotos as $photo)
                            <img class="ministry-feature-slide {{ $loop->first ? 'is-active' : '' }}" src="{{ $photo }}" alt="{{ $selected->title }} photo {{ $loop->iteration }}">
                        @endforeach
                    </div>
                    <div class="ministry-feature-copy">
                        <span class="eyebrow">OUR CHURCH DEPARTMENTS</span>
                        <h2>{{ $selected->title }}</h2>
                        @if($selected->summary)<p class="ministry-feature-summary">{{ $selected->summary }}</p>@endif
                        @if($selected->body)<div class="ministry-feature-body">{!! nl2br(e($selected->body)) !!}</div>@endif
                    </div>
                </div>

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
document.querySelectorAll('[data-ministry-feature]').forEach(feature => {
    const slides = [...feature.querySelectorAll('.ministry-feature-slide')];
    if (slides.length < 2) return;
    let active = 0;
    window.setInterval(() => {
        active = (active + 1) % slides.length;
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === active));
    }, 4000);
});
</script>
@endsection
