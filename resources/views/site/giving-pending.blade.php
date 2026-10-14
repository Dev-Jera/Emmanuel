@extends('site.layout')
@section('title', 'Complete your gift')
@section('content')
<main class="subpage giving-page"><div class="subpage-heading"><span class="eyebrow">ALMOST THERE</span><h1>Check your<br>phone.</h1><p>We sent a mobile money request to {{ $order->phone }}. Approve it with your PIN to complete your gift.</p></div>
    <div class="giving-status-card" data-status-endpoint="{{ route('giving.status', $order->reference) }}">
        <div class="giving-spinner" aria-hidden="true"></div>
        <h2>Waiting for your approval&hellip;</h2>
        <p>Enter your {{ $order->providerLabel() }} PIN on the prompt that appeared on your phone.</p>
        <dl class="giving-receipt">
            <div><dt>Gift</dt><dd>{{ number_format($order->amount) }} {{ $order->currency }}</dd></div>
            @if($order->purpose)<div><dt>Designation</dt><dd>{{ $order->purpose }}</dd></div>@endif
            <div><dt>Number</dt><dd>{{ $order->phone }}</dd></div>
            <div><dt>Reference</dt><dd>{{ $order->reference }}</dd></div>
        </dl>
        <p class="giving-note">Didn&rsquo;t get a prompt? Keep this page open and we will keep checking. You can also approve the message from your phone directly.</p>
    </div>
</main>
<script>
(function () {
    const card = document.querySelector('.giving-status-card');
    if (!card) return;
    const endpoint = card.dataset.statusEndpoint;
    let attempts = 0;
    const poll = async () => {
        attempts += 1;
        try {
            const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (data.final) { window.location.assign(data.redirect); return; }
        } catch (error) { /* keep waiting */ }
        if (attempts < 150) { setTimeout(poll, 4000); }
    };
    setTimeout(poll, 3000);
})();
</script>
@endsection
