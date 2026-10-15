<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MinistryUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (glob(public_path('uploads/ministries/*')) ?: [] as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_ministry_image_and_gallery_uploads_are_stored(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.entries.save'), [
                'type' => 'ministry',
                'slug' => 'youth',
                'title' => 'Youth Ministry',
                'published' => '1',
                'image_file' => UploadedFile::fake()->image('main.jpg', 800, 600),
                'gallery_files' => [
                    UploadedFile::fake()->image('a.jpg', 400, 300),
                    UploadedFile::fake()->image('b.jpg', 400, 300),
                ],
            ])
            ->assertRedirect(route('admin.dashboard', ['type' => 'ministry']));

        $entry = Entry::where('slug', 'youth')->firstOrFail();

        $this->assertStringStartsWith('/uploads/ministries/', $entry->image);
        $this->assertCount(2, $entry->gallery);
        $this->assertFileExists(public_path(ltrim($entry->image, '/')));
        foreach ($entry->gallery as $photo) {
            $this->assertFileExists(public_path(ltrim($photo, '/')));
        }

        $main = public_path(ltrim($entry->image, '/'));
        $oldGallery = $entry->gallery;

        $this->actingAs($admin)
            ->post(route('admin.entries.save'), [
                'id' => $entry->id,
                'type' => 'ministry',
                'slug' => 'youth',
                'title' => 'Youth Ministry',
                'published' => '1',
                'image_file' => UploadedFile::fake()->image('new.jpg', 800, 600),
            ])
            ->assertRedirect();

        $entry->refresh();
        $this->assertFileDoesNotExist($main);
        $this->assertCount(0, (array) $entry->gallery);
        foreach ($oldGallery as $photo) {
            $this->assertFileDoesNotExist(public_path(ltrim($photo, '/')));
        }
    }
}
