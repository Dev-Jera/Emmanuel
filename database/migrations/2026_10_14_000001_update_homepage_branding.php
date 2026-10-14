<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('site_settings')->where('key', 'church_name')->update(['value' => 'Emmanuel Ministries']);
        DB::table('site_settings')->where('key', 'tagline')->update(['value' => 'A life saving ministry.']);
        DB::table('entries')->where('type', 'page')->where('slug', 'home')->update([
            'title' => 'Emmanuel Ministries',
            'summary' => 'A life saving ministry.',
        ]);
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'tagline')->update(['value' => 'A place to belong. A faith to grow.']);
        DB::table('entries')->where('type', 'page')->where('slug', 'home')->update([
            'title' => 'A place to belong. A faith to grow.',
            'summary' => 'Welcome to Emmanuel Ministries. We are a community learning to love God, serve our neighbors, and walk through life together.',
        ]);
    }
};
