<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Entry;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public const TYPES = ['page' => 'Pages', 'devotional' => 'Devotionals', 'event' => 'Events', 'sermon' => 'Sermons', 'ministry' => 'Ministries'];

    public function login(): View
    {
        return view('admin.login', ['email' => User::query()->value('email') ?? env('CHURCH_ADMIN_EMAIL', 'admin@emmanuelministries.org')]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those sign-in details did not match.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function dashboard(Request $request): View
    {
        $tab = $request->query('tab', 'content');
        $type = $request->query('type', 'page');
        abort_unless(isset(self::TYPES[$type]), 404);
        $entries = Entry::where('type', $type)->orderByDesc('updated_at')->get();
        $editing = $request->integer('edit') ? Entry::findOrFail($request->integer('edit')) : new Entry(['type' => $type, 'published' => true]);
        $messages = ContactMessage::latest()->get();

        return view('admin.dashboard', compact('tab', 'type', 'entries', 'editing', 'messages'));
    }

    public function saveEntry(Request $request): RedirectResponse
    {
        $id = $request->integer('id');
        $validated = $request->validate([
            'id' => ['nullable', 'integer', Rule::exists('entries', 'id')->where(fn ($q) => $q->where('id', $id))],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'slug' => ['required', 'alpha_dash:ascii', 'max:140', Rule::unique('entries', 'slug')->where(fn ($q) => $q->where('type', $request->input('type')))->ignore($id ?: null)],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:30000'],
            'date' => ['nullable', 'date'],
            'image' => ['nullable', 'url', 'max:2048'],
            'published' => ['nullable', 'boolean'],
        ]);
        $validated['published'] = $request->boolean('published');
        $entry = $id ? Entry::findOrFail($id) : new Entry();
        $entry->fill($validated)->save();
        return redirect()->route('admin.dashboard', ['type' => $entry->type])->with('saved', 'Content saved.');
    }

    public function deleteEntry(Entry $entry): RedirectResponse
    {
        $type = $entry->type;
        $entry->delete();
        return redirect()->route('admin.dashboard', ['type' => $type])->with('saved', 'Content deleted.');
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'church_name' => ['required', 'string', 'max:160'],
            'tagline' => ['required', 'string', 'max:240'],
            'service_time' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:240'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'hero_image' => ['nullable', 'url', 'max:2048'],
            'giving_link' => ['nullable', 'url', 'max:2048'],
            'admin_email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore(Auth::id())],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        foreach (['church_name', 'tagline', 'service_time', 'location', 'email', 'phone', 'hero_image', 'giving_link'] as $key) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $validated[$key] ?? '']);
        }
        $user = User::findOrFail(Auth::id());
        $user->email = $validated['admin_email'];
        if (!empty($validated['new_password'])) {
            $user->password = $validated['new_password'];
        }
        $user->save();
        return redirect()->route('admin.dashboard', ['tab' => 'settings'])->with('saved', 'Settings updated.');
    }

    public function deleteMessage(ContactMessage $message): RedirectResponse
    {
        $message->delete();
        return redirect()->route('admin.dashboard', ['tab' => 'messages'])->with('saved', 'Message deleted.');
    }
}
