<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Entry;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChurchController extends Controller
{
    private const SECTIONS = ['ministries' => 'ministry', 'sermons' => 'sermon', 'events' => 'event', 'devotionals' => 'devotional'];

    public function home(): View
    {
        return view('site.home', [
            'page' => Entry::published()->where('type', 'page')->where('slug', 'home')->first(),
            'events' => Entry::published()->where('type', 'event')->orderBy('date')->take(3)->get(),
            'devotional' => Entry::published()->where('type', 'devotional')->orderByDesc('date')->first(),
        ]);
    }

    public function page(string $slug): View
    {
        $page = Entry::published()->where('type', 'page')->where('slug', $slug)->firstOrFail();
        return view('site.page', compact('page'));
    }

    public function listing(Request $request): View
    {
        $section = (string) $request->route('section');
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $type = self::SECTIONS[$section];
        $page = Entry::published()->where('type', 'page')->where('slug', $section)->first();
        $items = Entry::published()->where('type', $type)->orderByRaw('date IS NULL')->orderByDesc('date')->get();
        return view('site.listing', compact('section', 'type', 'page', 'items'));
    }

    public function detail(Request $request): View
    {
        $section = (string) $request->route('section');
        $slug = (string) $request->route('slug');
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $entry = Entry::published()->where('type', self::SECTIONS[$section])->where('slug', $slug)->firstOrFail();
        return view('site.detail', compact('entry', 'section'));
    }

    public function contact(): View
    {
        $page = Entry::published()->where('type', 'page')->where('slug', 'contact')->first();
        return view('site.contact', compact('page'));
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        ContactMessage::create($validated);
        return back()->with('sent', true);
    }
}
