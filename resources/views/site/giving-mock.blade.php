@extends('site.layout')
@section('title', 'Test gateway')
@section('content')
<main class="subpage giving-page"><div class="subpage-heading"><span class="eyebrow">TEST GATEWAY &middot; MOCK</span><h1>Simulate the<br>phone prompt.</h1><p>This page stands in for the mobile money prompt during testing. No real money moves. In production, your giver approves the request on their own phone.</p></div>
    <div class="giving-mock">
        <div class="mock-phone">
            <span class="mock-network">{{ $order->providerLabel() }}</span>
            <p class="mock-message">{{ \App\Models\SiteSetting::value('church_name') }} would like to collect <strong>{{ number_format($order->amount) }} {{ $order->currency }}</strong> from {{ $order->phone }}.</p>
            <p class="mock-sub">Enter your mobile money PIN to authorise.</p>
            <div class="mock-pin" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
            <div class="mock-actions">
                <form method="post" action="{{ route('giving.mock.complete', $order->reference) }}">@csrf<input type="hidden" name="outcome" value="approve"><button class="button button-dark" type="submit">Approve</button></form>
                <form method="post" action="{{ route('giving.mock.complete', $order->reference) }}">@csrf<input type="hidden" name="outcome" value="decline"><button class="button button-light mock-decline" type="submit">Decline</button></form>
            </div>
        </div>
        <p class="giving-note">Reference {{ $order->reference }}</p>
    </div>
</main>
@endsection
