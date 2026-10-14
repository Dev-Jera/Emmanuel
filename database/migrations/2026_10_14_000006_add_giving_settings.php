<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULTS = [
        'giving_prayer' => 'Lord, thank You for every good gift. As we give, quiet our hearts and help us to give with joy, trusting You to use these gifts for Your glory and the good of others. Amen.',
        'giving_verse' => 'Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver.',
        'giving_verse_ref' => '2 Corinthians 9:7',
        'giving_purposes' => 'Tithe, Offering, Missions, Building, Other',
    ];

    public function up(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            DB::table('site_settings')->insertOrIgnore(['key' => $key, 'value' => $value]);
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', array_keys(self::DEFAULTS))->delete();
    }
};
