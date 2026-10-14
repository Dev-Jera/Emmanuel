<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Entry;
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
            'events' => Entry::published()
                ->where('type', 'event')
                ->whereNotNull('date')
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->take(6)
                ->get(),
            'ministries' => Entry::published()
                ->where('type', 'ministry')
                ->orderBy('title')
                ->take(3)
                ->get(),
        ]);
    }

    public function page(string $slug): View
    {
        $page = Entry::published()->where('type', 'page')->where('slug', $slug)->firstOrFail();

        if ($slug === 'about') {
            return view('site.about', compact('page'));
        }

        return view('site.page', compact('page'));
    }

    public function listing(Request $request): View
    {
        $section = (string) $request->route('section');
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $type = self::SECTIONS[$section];
        $page = Entry::published()->where('type', 'page')->where('slug', $section)->first();

        if ($section === 'ministries') {
            $items = Entry::published()->where('type', 'ministry')->orderBy('title')->get();
            $selected = $items->firstWhere('slug', $request->query('ministry')) ?? $items->first();

            return view('site.ministries', compact('page', 'items', 'selected'));
        }

        if ($section === 'devotionals') {
            $devotionals = Entry::published()
                ->where('type', 'devotional')
                ->orderByRaw('date IS NULL')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
            $entry = $devotionals->first();
            $pastDevotionals = $devotionals->skip(1)->values();
            $comments = $entry?->comments()->get() ?? collect();

            return view('site.devotionals', compact('page', 'entry', 'pastDevotionals', 'comments'));
        }

        $items = Entry::published()->where('type', $type)->orderByRaw('date IS NULL')->orderByDesc('date')->get();

        return view('site.listing', compact('section', 'type', 'page', 'items'));
    }

    public function detail(Request $request): View
    {
        $section = (string) $request->route('section');
        $slug = (string) $request->route('slug');
        abort_unless(isset(self::SECTIONS[$section]), 404);

        if ($section === 'devotionals') {
            $page = Entry::published()->where('type', 'page')->where('slug', 'devotionals')->first();
            $devotionals = Entry::published()
                ->where('type', 'devotional')
                ->orderByRaw('date IS NULL')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
            $entry = $devotionals->firstWhere('slug', $slug);
            abort_unless($entry, 404);
            $pastDevotionals = $devotionals->reject(fn (Entry $devotional) => $devotional->is($entry))->values();
            $comments = $entry->comments()->get();

            return view('site.devotionals', compact('page', 'entry', 'pastDevotionals', 'comments'));
        }

        if ($section === 'ministries') {
            $page = Entry::published()->where('type', 'page')->where('slug', 'ministries')->first();
            $items = Entry::published()->where('type', 'ministry')->orderBy('title')->get();
            $selected = $items->firstWhere('slug', $slug);
            abort_unless($selected, 404);

            return view('site.ministries', compact('page', 'items', 'selected'));
        }

        $entry = Entry::published()->where('type', self::SECTIONS[$section])->where('slug', $slug)->firstOrFail();

        return view('site.detail', compact('entry', 'section'));
    }

    public function joinMinistry(Request $request, string $slug): RedirectResponse
    {
        $ministry = Entry::published()->where('type', 'ministry')->where('slug', $slug)->firstOrFail();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $details = ['Ministry: '.$ministry->title];
        if (filled($validated['phone'] ?? null)) {
            $details[] = 'Phone: '.$validated['phone'];
        }
        if (filled($validated['message'] ?? null)) {
            $details[] = '';
            $details[] = $validated['message'];
        }

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => 'Join ministry: '.$ministry->title,
            'message' => implode("\n", $details),
        ]);

        return redirect()->to(route('detail.ministry', ['slug' => $ministry->slug]).'#join-form')
            ->with('ministry_joined', true);
    }

    public function storeDevotionalComment(Request $request, string $slug): RedirectResponse
    {
        $entry = Entry::published()->where('type', 'devotional')->where('slug', $slug)->firstOrFail();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $entry->comments()->create($validated);

        return redirect()->to(route('detail.devotional', ['slug' => $entry->slug]).'#comments')
            ->with('comment_posted', true);
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
