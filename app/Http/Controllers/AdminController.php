<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Entry;
use App\Models\GivingOrder;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
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

        $givingSummary = [
            'today' => $this->givingTotal(GivingOrder::query()->whereDate('paid_at', now()->toDateString())),
            'month' => $this->givingTotal(GivingOrder::query()->where('paid_at', '>=', now()->startOfMonth())),
            'all' => $this->givingTotal(),
            'count' => GivingOrder::completed()->count(),
            'pending' => GivingOrder::whereIn('status', ['pending', 'processing'])->count(),
        ];
        $orders = GivingOrder::latest()->limit(200)->get();

        return view('admin.dashboard', compact('tab', 'type', 'entries', 'editing', 'messages', 'orders', 'givingSummary'));
    }

    private function givingTotal($query = null): int
    {
        $query = $query ? $query->completed() : GivingOrder::completed();

        return (int) $query->sum(DB::raw('COALESCE(net_amount, amount)'));
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
            'image' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                $isWebUrl = filter_var($value, FILTER_VALIDATE_URL)
                    && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
                $isPublicPath = str_starts_with($value, '/') && ! str_starts_with($value, '//') && ! str_contains($value, '..');

                if (! $isWebUrl && ! $isPublicPath) {
                    $fail('Use an http/https image URL or a path starting with /, such as /images/devotional.jpg.');
                }
            }],
            'gallery' => ['nullable', 'string', 'max:6144', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || trim($value) === '') {
                    return;
                }

                $images = array_values(array_filter(array_map('trim', preg_split('/\R/', $value))));
                if (count($images) > 3) {
                    $fail('Add up to three activity images, one per line.');

                    return;
                }

                foreach ($images as $image) {
                    $isWebUrl = filter_var($image, FILTER_VALIDATE_URL)
                        && in_array(parse_url($image, PHP_URL_SCHEME), ['http', 'https'], true);
                    $isPublicPath = str_starts_with($image, '/') && ! str_starts_with($image, '//') && ! str_contains($image, '..');
                    if (! $isWebUrl && ! $isPublicPath) {
                        $fail('Each activity image must be an http/https URL or a public path starting with /.');

                        return;
                    }
                }
            }],
            'published' => ['nullable', 'boolean'],
        ]);
        $validated['gallery'] = array_values(array_filter(array_map('trim', preg_split('/\R/', $validated['gallery'] ?? '')))) ?: null;
        $validated['published'] = $request->boolean('published');
        $entry = $id ? Entry::findOrFail($id) : new Entry;
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
        $publicImageRule = function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $isWebUrl = filter_var($value, FILTER_VALIDATE_URL)
                && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
            $isPublicPath = str_starts_with($value, '/') && !str_starts_with($value, '//') && !str_contains($value, '..');

            if (! $isWebUrl && ! $isPublicPath) {
                $fail('Use an http/https image URL or a public path starting with /.');
            }
        };

        $validated = $request->validate([
            'church_name' => ['required', 'string', 'max:160'],
            'tagline' => ['required', 'string', 'max:240'],
            'service_time' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:240'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'hero_image' => ['nullable', 'url', 'max:2048'],
            'giving_link' => ['nullable', 'url', 'max:2048'],
            'giving_prayer' => ['nullable', 'string', 'max:3000'],
            'giving_verse' => ['nullable', 'string', 'max:600'],
            'giving_verse_ref' => ['nullable', 'string', 'max:120'],
            'giving_purposes' => ['nullable', 'string', 'max:600'],
            'pastor_name' => ['nullable', 'string', 'max:160'],
            'pastor_title' => ['nullable', 'string', 'max:160'],
            'pastor_image' => ['nullable', 'string', 'max:2048', $publicImageRule],
            'pastor_bio' => ['nullable', 'string', 'max:5000'],
            'pastor_education' => ['nullable', 'string', 'max:3000'],
            'pastor_ministry' => ['nullable', 'string', 'max:3000'],
            'pastor_calling' => ['nullable', 'string', 'max:3000'],
            'pastor_previous' => ['nullable', 'string', 'max:3000'],
            'church_founded' => ['nullable', 'string', 'max:240'],
            'church_mission' => ['nullable', 'string', 'max:2000'],
            'church_vision' => ['nullable', 'string', 'max:2000'],
            'admin_email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore(Auth::id())],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        foreach (['church_name', 'tagline', 'service_time', 'location', 'email', 'phone', 'hero_image', 'giving_link', 'giving_prayer', 'giving_verse', 'giving_verse_ref', 'giving_purposes', 'pastor_name', 'pastor_title', 'pastor_image', 'pastor_bio', 'pastor_education', 'pastor_ministry', 'pastor_calling', 'pastor_previous', 'church_founded', 'church_mission', 'church_vision'] as $key) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $validated[$key] ?? '']);
        }
        $user = User::findOrFail(Auth::id());
        $user->email = $validated['admin_email'];
        if (! empty($validated['new_password'])) {
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
