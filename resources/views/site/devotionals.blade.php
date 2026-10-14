@extends('site.layout')
@section('title', $entry?->title ?? $page?->title ?? 'Daily Devotionals')
@section('content')
<main class="devotionals-page">
    <aside class="devotional-sidebar" aria-label="Past devotionals">
        <span class="eyebrow">DAILY WORD</span>
        <h2>Past devotionals</h2>
        @forelse($pastDevotionals as $pastDevotional)
            <a class="past-devotional-link {{ $entry && $entry->is($pastDevotional) ? 'is-current' : '' }}" href="{{ route('detail.devotional', ['slug' => $pastDevotional->slug]) }}">
                @if($pastDevotional->date)<time>{{ $pastDevotional->date->format('M j, Y') }}</time>@endif
                <strong>{{ $pastDevotional->title }}</strong>
                <span>{{ $pastDevotional->summary }}</span>
            </a>
        @empty
            <p class="past-devotionals-empty">Previous devotionals will appear here.</p>
        @endforelse
    </aside>

    <section class="devotional-reading">
        @if($entry)
            <article class="current-devotional">
                <img class="devotional-cover" src="{{ $entry->image ?: '/css/background.jfif' }}" alt="" fetchpriority="high">
                <div class="devotional-reading-copy">
                    <span class="eyebrow">TODAY'S DEVOTIONAL</span>
                    @if($entry->date)<time class="devotional-date" datetime="{{ $entry->date->toDateString() }}">{{ $entry->date->format('F j, Y') }}</time>@endif
                    <h1>{{ $entry->title }}</h1>
                    @if($entry->summary)<p class="devotional-summary">{{ $entry->summary }}</p>@endif
                    <div class="devotional-body">{!! nl2br(e($entry->body)) !!}</div>
                </div>
            </article>

            <section class="devotional-comments" id="comments" aria-labelledby="comments-heading">
                <div class="comments-heading-row">
                    <div><span class="eyebrow">SHARE A THOUGHT</span><h2 id="comments-heading">Comments <span>({{ $comments->count() }})</span></h2></div>
                </div>
                @if(session('comment_posted'))<p class="comment-success" role="status">Your comment has been posted.</p>@endif

                <details class="comment-disclosure" @if($errors->any()) open @endif>
                    <summary>Click to comment</summary>
                    <form class="devotional-comment-form" method="post" action="{{ route('devotionals.comments.store', ['slug' => $entry->slug]) }}">
                        @csrf
                        <label for="comment-name">Your name</label>
                        <input id="comment-name" type="text" name="name" value="{{ old('name') }}" maxlength="120" required>
                        @error('name')<small class="comment-error">{{ $message }}</small>@enderror
                        <label for="comment-body">Your comment</label>
                        <textarea id="comment-body" name="body" rows="5" maxlength="3000" required>{{ old('body') }}</textarea>
                        @error('body')<small class="comment-error">{{ $message }}</small>@enderror
                        <button class="button button-dark" type="submit">Post comment <span>→</span></button>
                    </form>
                </details>

                <div class="devotional-comment-list">
                    @forelse($comments as $comment)
                        <article class="devotional-comment">
                            <div><strong>{{ $comment->name }}</strong><time>{{ $comment->created_at->format('M j, Y') }}</time></div>
                            <p>{!! nl2br(e($comment->body)) !!}</p>
                        </article>
                    @empty
                        <p class="no-comments">Be the first to leave a comment.</p>
                    @endforelse
                </div>
            </section>
        @else
            <div class="devotional-empty-state">
                <span class="eyebrow">A MOMENT WITH GOD, EVERY DAY</span>
                <h1>{{ $page?->title ?? 'Daily devotionals' }}</h1>
                <p>{{ $page?->summary ?? 'A new devotional will be shared here soon.' }}</p>
            </div>
        @endif
    </section>
</main>
@endsection
