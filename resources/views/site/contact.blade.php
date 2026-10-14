@extends('site.layout')
@section('title', 'Contact')
@section('content')
<main class="subpage"><div class="subpage-heading"><span class="eyebrow">LET’S CONNECT</span><h1>We’d love to<br>meet you.</h1><p>{{ $page?->summary ?? 'Have a question, prayer request, or just want to say hello? We’re listening.' }}</p></div>
    @if(session('sent'))<div class="success">Thank you for reaching out. Your message has been received.</div>@endif
    <div class="contact-layout"><div class="contact-details"><div><span class="detail-icon">◷</span><small>JOIN US SUNDAY</small><strong>{{ \App\Models\SiteSetting::value('service_time') }}</strong></div><div><span class="detail-icon">⌖</span><small>FIND US</small><strong>{{ \App\Models\SiteSetting::value('location') }}</strong></div><div><span class="detail-icon">✉</span><small>SAY HELLO</small><strong>{{ \App\Models\SiteSetting::value('email') }}</strong></div></div>
        <form method="post" action="{{ route('contact.send') }}" class="contact-form">@csrf<h2>Send us a note</h2><div class="form-pair"><label>Your name<input name="name" value="{{ old('name') }}" required>@error('name')<small>{{ $message }}</small>@enderror</label><label>Email address<input type="email" name="email" value="{{ old('email') }}" required>@error('email')<small>{{ $message }}</small>@enderror</label></div><label>Subject<input name="subject" value="{{ old('subject') }}" required>@error('subject')<small>{{ $message }}</small>@enderror</label><label>Your message<textarea name="message" rows="5" required>{{ old('message') }}</textarea>@error('message')<small>{{ $message }}</small>@enderror</label><button class="button button-dark" type="submit">Send message <span>→</span></button></form>
    </div>
</main>
@endsection
