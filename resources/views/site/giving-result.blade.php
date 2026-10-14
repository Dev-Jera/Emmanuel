@extends('site.layout')
@section('title', 'Thank you')
@section('content')
<main class="subpage giving-page">
    @if($order->status === 'completed')
        <div class="subpage-heading"><span class="eyebrow">THANK YOU</span><h1>Your gift<br>was received.</h1><p>Thank you for giving with joy. Your generosity helps us care for our church family and serve our community.</p></div>
        <div class="giving-result completed">
            <div class="giving-result-mark">&#10003;</div>
            <dl class="giving-receipt">
                <div><dt>Gift</dt><dd>{{ number_format($order->amount) }} {{ $order->currency }}</dd></div>
                @if($order->purpose)<div><dt>Designation</dt><dd>{{ $order->purpose }}</dd></div>@endif
                <div><dt>Paid with</dt><dd>{{ $order->providerLabel() }}</dd></div>
                <div><dt>Reference</dt><dd>{{ $order->reference }}</dd></div>
            </dl>
            <p class="giving-verse-line">&ldquo;{{ \App\Models\SiteSetting::value('giving_verse') }}&rdquo; @if(\App\Models\SiteSetting::value('giving_verse_ref'))<cite>{{ \App\Models\SiteSetting::value('giving_verse_ref') }}</cite>@endif</p>
            <a class="button button-dark" href="{{ route('home') }}">Return home <span>&rarr;</span></a>
        </div>
    @elseif($order->status === 'failed' || $order->status === 'cancelled')
        <div class="subpage-heading"><span class="eyebrow">NOT COMPLETED</span><h1>This gift<br>did not go through.</h1><p>Your mobile money request was not completed. No charge was made, and you are welcome to try again whenever you are ready.</p></div>
        <div class="giving-result failed">
            <div class="giving-result-mark">!</div>
            <p>If you entered the wrong PIN or ran out of time, simply start again.</p>
            <a class="button button-dark" href="{{ route('giving.show') }}">Try again <span>&rarr;</span></a>
        </div>
    @else
        <div class="subpage-heading"><span class="eyebrow">STILL WAITING</span><h1>We are still<br>waiting.</h1><p>We have not received a final confirmation for this gift yet. Please check again in a moment.</p></div>
        <div class="giving-result waiting">
            <div class="giving-result-mark">&hellip;</div>
            <a class="button button-dark" href="{{ route('giving.pending', $order->reference) }}">Check status <span>&rarr;</span></a>
        </div>
    @endif
</main>
@endsection
