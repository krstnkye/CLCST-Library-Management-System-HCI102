<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_can_upload_a_profile_photo_that_replaces_their_initials(): void
    {
        Storage::fake('local');
        $pages = [
            'student' => ['student.section', 'account'],
            'faculty' => ['staff.section', 'profile'],
            'librarian' => ['librarian.section', 'profile'],
            'admin' => ['admin.section', 'profile'],
            'superadmin' => ['superadmin.section', 'profile'],
        ];

        foreach ($pages as $role => [$route, $section]) {
            $user = User::factory()->role($role)->create();
            $page = route($route, $section);

            $this->actingAs($user)->get($page)->assertOk()->assertSee('Profile photo')->assertSee($user->initials());

            $this->actingAs($user)->from($page)
                ->post(route('profile.photo.store'), ['photo' => UploadedFile::fake()->image('me.jpg', 300, 300)])
                ->assertRedirect($page)->assertSessionHas('status');

            $user->refresh();
            Storage::disk('local')->assertExists($user->profile_photo_path);
            $this->actingAs($user)->get($page)->assertOk()->assertSee($user->profilePhotoUrl(), false)->assertSee('Remove photo');
        }
    }

    public function test_a_new_photo_replaces_the_old_file_and_can_be_removed(): void
    {
        Storage::fake('local');
        $user = User::factory()->role('librarian')->create();

        $this->actingAs($user)->post(route('profile.photo.store'), ['photo' => UploadedFile::fake()->image('a.png', 200, 200)]);
        $first = $user->refresh()->profile_photo_path;
        $this->actingAs($user)->post(route('profile.photo.store'), ['photo' => UploadedFile::fake()->image('b.png', 200, 200)]);
        $second = $user->refresh()->profile_photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);

        $this->actingAs($user)->delete(route('profile.photo.destroy'))->assertSessionHas('status');
        $this->assertNull($user->refresh()->profile_photo_path);
        Storage::disk('local')->assertMissing($second);
    }

    public function test_uploads_must_be_reasonable_images(): void
    {
        Storage::fake('local');
        $user = User::factory()->role('admin')->create();

        foreach ([
            UploadedFile::fake()->createWithContent('photo.jpg', '<?php echo 1;'),
            UploadedFile::fake()->create('doc.pdf', 20, 'application/pdf'),
            UploadedFile::fake()->image('big.jpg', 400, 400)->size(3000),
            UploadedFile::fake()->image('tiny.png', 40, 40),
        ] as $file) {
            $this->actingAs($user)->post(route('profile.photo.store'), ['photo' => $file])->assertSessionHasErrors('photo');
        }

        $this->assertNull($user->refresh()->profile_photo_path);
    }

    public function test_photos_are_only_served_to_signed_in_users(): void
    {
        Storage::fake('local');
        $owner = User::factory()->role('faculty')->create();
        $this->actingAs($owner)->post(route('profile.photo.store'), ['photo' => UploadedFile::fake()->image('me.jpg', 200, 200)]);
        $url = $owner->refresh()->profilePhotoUrl();

        $this->actingAs(User::factory()->create())->get($url)->assertOk();
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($owner)->get(route('profile.photo.show', User::factory()->create()))->assertNotFound();
    }
}
