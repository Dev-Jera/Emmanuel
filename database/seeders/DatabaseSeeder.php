<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::query()->exists()) {
            User::create([
                'name' => 'Church Admin',
                'email' => env('CHURCH_ADMIN_EMAIL', 'admin@emmanuelministries.org'),
                'password' => env('CHURCH_ADMIN_PASSWORD', 'Welcome123!'),
            ]);
        }

        // Seed the starter site once. Render starts a fresh container on each
        // deploy, so running this seeder at startup must not reset edited content.
        if (SiteSetting::whereKey('_initial_content_seeded')->exists()) {
            return;
        }

        foreach ([
            'church_name' => 'Emmanuel Ministries',
            'tagline' => 'A life saving ministry.',
            'service_time' => 'Sundays at 10:00 AM',
            'location' => 'Kampala, Uganda',
            'email' => 'hello@emmanuelministries.org',
            'phone' => '',
            'hero_image' => '',
            'giving_link' => '',
            'giving_prayer' => 'Lord, thank You for every good gift. As we give, quiet our hearts and help us to give with joy, trusting You to use these gifts for Your glory and the good of others. Amen.',
            'giving_verse' => 'Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver.',
            'giving_verse_ref' => '2 Corinthians 9:7',
            'giving_purposes' => 'Tithe, Offering, Missions, Building, Other',
        ] as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $entries = [
            ['page', 'home', 'Emmanuel Ministries', 'A life saving ministry.', 'Welcome to Emmanuel Ministries. We are a community learning to love God, serve our neighbors, and walk through life together.', null],
            ['page', 'about', 'We are Emmanuel Ministries', 'We are a Christ-centered church committed to worship, growing in faith, and serving our community with compassion. There is a place for you here, wherever you are on your journey.', 'Our story is one of grace, community, and hope. Together, we seek to make the love of Jesus visible in our neighborhood.', null],
            ['page', 'ministries', 'Find your community', 'Faith grows when we grow together. Explore a ministry and discover a meaningful way to connect, learn, and serve.', '', null],
            ['page', 'sermons', 'Messages for everyday life', 'Explore biblical teaching to encourage your faith throughout the week. Watch, listen, or revisit a recent message.', '', null],
            ['page', 'events', 'Life is better together', 'There is always something happening at Emmanuel. Join us for worship, fellowship, and service.', '', null],
            ['page', 'devotionals', 'A moment with God, every day', 'Pause, reflect, and carry a little hope into your day with our daily devotionals.', '', null],
            ['page', 'giving', 'Give with purpose', 'Your generosity helps us care for our church family and serve our wider community. Thank you for being part of the work.', 'For now, please contact the church office for giving details. Add an online giving link in dashboard settings when you are ready.', null],
            ['page', 'contact', 'We would love to hear from you', 'Have a question, prayer request, or just want to say hello? Reach out and our team will be glad to connect.', "Sunday worship · 10:00 AM\nEmmanuel Ministries\nKampala, Uganda\nhello@emmanuelministries.org", null],
            ['ministry', 'kids', 'Emmanuel Kids', 'A joyful, safe place for children to learn about Jesus, build friendships, and grow in faith.', "Sundays during the 10:00 AM service. All children are welcome.\n\nOur team creates a warm, age-appropriate space where children sing, hear Bible stories, play, and make friends. Every volunteer is trained and screened, and every child is checked in and cared for. Come a little early and our welcome team will help you get settled.", null],
            ['ministry', 'youth', 'Youth & Young Adults', 'A community for young people to ask big questions, build real friendships, and put faith into action.', "Meet every Friday at 6:00 PM. Bring a friend.\n\nWe gather for worship, honest conversation, food, and games, and we serve together in the community. Whether you are exploring faith or have followed Jesus for years, there is room for you here.", null],
            ['ministry', 'community-care', 'Community Care', 'Practical care and compassionate support for neighbors in every season of life.', "Volunteer, request support, or learn how to contribute.\n\nWe coordinate practical help for neighbors in need — meals, visits, transport, and prayer. If you or someone you know needs support, reach out and our team will follow up. If you would like to serve, we will help you find a good fit.", null],
            ['sermon', 'hope-for-the-journey', 'Hope for the Journey', 'Finding steady hope in seasons of change.', 'Pastor Daniel · Sunday teaching', null],
            ['event', 'sunday-worship', 'Sunday Worship', 'Come as you are for worship, teaching, and community.', 'Everyone is welcome. Children’s ministry is available.', '2026-10-18'],
            ['event', 'community-lunch', 'Community Lunch', 'Share a meal and meet someone new after the service.', 'Bring a dish to share if you are able. All are welcome.', '2026-10-25'],
            ['devotional', 'grace-for-today', 'Grace for today', 'A new beginning', "“His mercies are new every morning; great is your faithfulness.” — Lamentations 3:23\n\nSome mornings arrive with a long list and a tired heart. Before you hurry into the day, remember: God meets you here, not only after you have everything figured out. Receive today as a gift, one step at a time.\n\nPrayer: God, help me receive your grace for this day and share it with someone I meet. Amen.", '2026-10-14'],
            ['devotional', 'small-acts-of-love', 'Small acts of love', 'Faithfulness in the ordinary', '“Let us not love with words or speech but with actions and in truth.” — 1 John 3:18'."\n\nA listening ear, a thoughtful message, a meal shared: small acts can carry great love. Ask God to show you one simple way to encourage someone today.\n\nPrayer: Lord, open my eyes to the people around me and give me a willing heart. Amen.", '2026-10-13'],
        ];

        foreach ($entries as [$type, $slug, $title, $summary, $body, $date]) {
            Entry::firstOrCreate(['type' => $type, 'slug' => $slug], compact('type', 'slug', 'title', 'summary', 'body', 'date'));
        }

        SiteSetting::create(['key' => '_initial_content_seeded', 'value' => '1']);
    }
}
