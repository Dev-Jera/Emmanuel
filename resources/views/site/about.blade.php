@extends('site.layout')
@section('title', $page->title ?: 'About Emmanuel Ministries')
@section('content')
<main class="about-page">
    @php($pastorName = \App\Models\SiteSetting::value('pastor_name') ?: 'Pastor John Doe')
    @php($pastorTitle = \App\Models\SiteSetting::value('pastor_title') ?: 'Senior Pastor')
    @php($pastorImage = \App\Models\SiteSetting::value('pastor_image') ?: '/css/pastor.png')
    @php($pastorBio = \App\Models\SiteSetting::value('pastor_bio') ?: 'Pastor John Doe is committed to sharing the gospel, encouraging believers, and serving families across Kampala. His ministry centers on prayer, biblical teaching, and compassionate care for the community.')
    <header class="about-page-heading">
        <span class="eyebrow">ABOUT US</span>
        <h1>{{ $page->title ?: 'About Emmanuel Ministries' }}</h1>
        @if($page->summary)<p>{{ $page->summary }}</p>@endif
    </header>

    <div class="about-profile-layout">
        <aside class="about-pastor-profile">
            <img src="{{ $pastorImage }}" alt="{{ $pastorName }}">
            <div class="about-pastor-caption">
                <strong>{{ $pastorName }}</strong>
                <span>{{ $pastorTitle }}</span>
            </div>
        </aside>

        <section class="about-information">
            <div class="about-tabs" role="tablist" aria-label="About Emmanuel Ministries">
                <button id="pastor-tab" type="button" role="tab" aria-selected="true" aria-controls="pastor-panel">Our Senior Pastor</button>
                <button id="church-tab" type="button" role="tab" aria-selected="false" aria-controls="church-panel" tabindex="-1">About Emmanuel Ministries</button>
            </div>

            <section class="about-tab-panel" id="pastor-panel" role="tabpanel" aria-labelledby="pastor-tab">
                <span class="eyebrow">OUR PASTOR</span>
                <h2>{{ $pastorName }}</h2>
                <div class="pastor-biography">
                    <section class="about-fact"><h3>About our pastor</h3><p>{!! nl2br(e($pastorBio)) !!}</p></section>
                    @foreach([
                        'pastor_education' => 'Education',
                        'pastor_ministry' => 'Ministry journey',
                        'pastor_calling' => 'How Emmanuel Ministries began',
                        'pastor_previous' => 'Previous church or ministry',
                    ] as $key => $heading)
                        @php($sample = [
                            'pastor_education' => 'Sample: Bachelor of Theology, East Africa School of Theology (2010), and a Certificate in Pastoral Leadership (2013).',
                            'pastor_ministry' => 'Sample: More than 15 years of service in worship, discipleship, community outreach, and pastoral care.',
                            'pastor_calling' => 'Sample: Emmanuel Ministries began in 2015 as a small prayer fellowship with a vision to bring hope to the Kampala community.',
                            'pastor_previous' => 'Sample: Before Emmanuel Ministries, he served as an associate pastor at Grace Community Church in Kampala.',
                        ][$key])
                        <section class="about-fact"><h3>{{ $heading }}</h3><p>{!! nl2br(e(\App\Models\SiteSetting::value($key) ?: $sample)) !!}</p></section>
                    @endforeach
                </div>
            </section>

            <section class="about-tab-panel" id="church-panel" role="tabpanel" aria-labelledby="church-tab" hidden>
                <span class="eyebrow">OUR CHURCH</span>
                <h2>About Emmanuel Ministries</h2>
                <div class="about-story"><h3>Our story</h3><p>{!! nl2br(e($page->body ?: 'Sample: Emmanuel Ministries started as a small gathering of people seeking to grow in faith and serve their neighbors. Today, the church welcomes individuals and families from across Kampala.')) !!}</p></div>
                <div class="about-fact"><h3>Founded</h3><p>{{ \App\Models\SiteSetting::value('church_founded') ?: 'Sample: 2015 in Kampala, Uganda' }}</p></div>
                <div class="about-fact"><h3>Our mission</h3><p>{!! nl2br(e(\App\Models\SiteSetting::value('church_mission') ?: 'Sample: To share the saving message of Jesus Christ, help people grow in faith, and serve our community with compassion.')) !!}</p></div>
                <div class="about-fact"><h3>Our vision</h3><p>{!! nl2br(e(\App\Models\SiteSetting::value('church_vision') ?: 'Sample: To be a welcoming, Christ-centered church where lives are restored, faith grows, and every person can find a place to belong and serve.')) !!}</p></div>
            </section>
        </section>
    </div>
</main>
<script>
(() => {
    const tabs = [...document.querySelectorAll('.about-tabs [role="tab"]')];
    const activate = tab => {
        tabs.forEach(item => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
            document.getElementById(item.getAttribute('aria-controls')).hidden = !selected;
        });
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab));
        tab.addEventListener('keydown', event => {
            if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
                event.preventDefault();
                const next = tabs[(index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
                activate(next);
                next.focus();
            }
        });
    });
})();
</script>
@endsection
