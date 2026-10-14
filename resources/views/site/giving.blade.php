@extends('site.layout')
@section('title', 'Giving')
@section('content')
<main class="subpage giving-page"><div class="subpage-heading"><span class="eyebrow">GIVING</span><h1>Give from<br>the heart.</h1><p>{{ $page?->summary ?: 'Every gift is an act of worship. Thank you for partnering with the work of this ministry.' }}</p></div>
    <div class="giving-layout">
        <div class="giving-aside">
            @if($prayer)<div class="giving-prayer"><span class="eyebrow">A PRAYER FOR THE GIVER</span><p>{!! nl2br(e($prayer)) !!}</p></div>@endif
            @if($verse)<blockquote class="giving-verse"><p>&ldquo;{{ $verse }}&rdquo;</p>@if($verseReference)<cite>{{ $verseReference }}</cite>@endif</blockquote>@endif
        </div>
        <form method="post" action="{{ route('giving.initiate') }}" class="contact-form giving-form">@csrf
            <h2>Give from the heart</h2>
            <label>Your gift (UGX)<input type="number" name="amount" inputmode="numeric" min="{{ $minAmount }}" max="{{ $maxAmount }}" step="1" value="{{ old('amount') }}" placeholder="Enter any amount" required><small>Any amount from {{ number_format($minAmount) }} to {{ number_format($maxAmount) }}.</small>@error('amount')<small class="field-error">{{ $message }}</small>@enderror</label>
            @if(count($purposes))
                <label>Where would you like it to go?<select name="purpose"><option value="">Where it is needed most</option>@foreach($purposes as $purpose)<option value="{{ $purpose }}" @selected(old('purpose') === $purpose)>{{ $purpose }}</option>@endforeach</select>@error('purpose')<small class="field-error">{{ $message }}</small>@enderror</label>
            @endif
            <div class="form-pair">
                <label>Your name<input name="name" value="{{ old('name') }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
                <label>Mobile money number<input name="phone" value="{{ old('phone') }}" inputmode="tel" placeholder="0772 123 456" required>@error('phone')<small class="field-error">{{ $message }}</small>@enderror</label>
            </div>
            <fieldset class="network-choice"><legend>Which network?</legend>
                <label class="network-option"><input type="radio" name="network" value="mtn" @checked(old('network') === 'mtn')><img src="/css/MTNLOGO.png" alt="" loading="lazy"><span>MTN MoMo</span></label>
                <label class="network-option"><input type="radio" name="network" value="airtel" @checked(old('network') === 'airtel')><img src="/css/airtellogo.png" alt="" loading="lazy"><span>Airtel Money</span></label>
            </fieldset>
            <label>Email address <span class="optional">(optional)</span><input type="email" name="email" value="{{ old('email') }}">@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label>Leave a note <span class="optional">(optional)</span><textarea name="message" rows="3">{{ old('message') }}</textarea>@error('message')<small class="field-error">{{ $message }}</small>@enderror</label>
            <button class="button button-dark" type="submit">Give with joy <span>&rarr;</span></button>
            <p class="giving-note">After you submit, a prompt will be sent to your phone. Approve it with your mobile money PIN to complete your gift.</p>
        </form>
    </div>
</main>
@endsection
