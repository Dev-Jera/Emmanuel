<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071a35">
    <title>@yield('title') &middot; {{ \App\Models\SiteSetting::value('church_name', 'Emmanuel Ministries') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/site.css?v=20261014-14">
    <link rel="stylesheet" href="/css/theme.css?v=20261015-39">
</head>
<body>
<header class="site-header">
    <a class="brand" href="{{ route('home') }}"><span class="brand-mark">e</span><span>{{ \App\Models\SiteSetting::value('church_name', 'Emmanuel Ministries') }}</span></a>
    <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav class="main-nav"><a class="{{ request()->routeIs('home') ? 'nav-active' : '' }}" href="{{ route('home') }}">Home</a><a href="{{ route('page', 'about') }}">About</a><a href="{{ route('listing.ministries') }}">Ministries</a><a href="{{ route('listing.sermons') }}">Sermons</a><a href="{{ route('listing.events') }}">Events</a><a href="{{ route('listing.devotionals') }}">Devotionals</a><a href="{{ route('page', 'giving') }}">Giving</a></nav>
    <a class="button button-dark header-cta" href="{{ route('contact') }}">Plan a visit <span aria-hidden="true">&rarr;</span></a>
</header>
@yield('content')
<footer class="site-footer"><div class="footer-main">
    <div class="footer-intro"><a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark">e</span><span>{{ \App\Models\SiteSetting::value('church_name', 'Emmanuel Ministries') }}</span></a><p>{{ \App\Models\SiteSetting::value('tagline', 'A life saving ministry.') }}<br>Come as you are. There is a place for you here.</p></div>
    <div><small>FIND YOUR WAY</small><a href="{{ route('page', 'about') }}">Our church</a><a href="{{ route('listing.ministries') }}">Ministries</a><a href="{{ route('listing.sermons') }}">Messages</a><a href="{{ route('page', 'giving') }}">Giving</a></div>
    <div><small>STAY CONNECTED</small><a href="{{ route('listing.events') }}">Events</a><a href="{{ route('listing.devotionals') }}">Daily devotionals</a><a href="{{ route('contact') }}">Contact us</a>@if(\App\Models\SiteSetting::value('email'))<a href="mailto:{{ \App\Models\SiteSetting::value('email') }}">{{ \App\Models\SiteSetting::value('email') }}</a>@endif</div>
    <div class="footer-service"><small>YOU'RE INVITED</small><strong>{{ \App\Models\SiteSetting::value('service_time', 'Sundays at 10:00 AM') }}</strong><span>{{ \App\Models\SiteSetting::value('location', 'Kampala, Uganda') }}</span><a href="{{ route('contact') }}" class="footer-visit">Plan a visit <span aria-hidden="true">&rarr;</span></a></div>
</div><div class="footer-bottom"><span>© {{ date('Y') }} {{ \App\Models\SiteSetting::value('church_name', 'Emmanuel Ministries') }}. Made with care.</span><a href="{{ route('admin.login') }}">Church admin</a></div></footer>
<script>document.querySelectorAll('.menu-toggle').forEach(b=>b.addEventListener('click',()=>{const nav=document.querySelector('.main-nav');const open=nav.classList.toggle('open');b.setAttribute('aria-expanded',String(open));b.setAttribute('aria-label',open?'Close menu':'Open menu')}))</script>
</body></html>
