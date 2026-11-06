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

    /** A real PNG built without the GD extension, so these tests run on any PHP install. */
    private function png(string $name, int $width, int $height): UploadedFile
    {
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $rows = str_repeat("\0".str_repeat("\x2c\x46\x32", $width), $height);
        $png = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($rows))
            .$chunk('IEND', '');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

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
                ->post(route('profile.photo.store'), ['photo' => $this->png('me.png', 300, 300)])
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

        $this->actingAs($user)->post(route('profile.photo.store'), ['photo' => $this->png('a.png', 200, 200)]);
        $first = $user->refresh()->profile_photo_path;
        $this->actingAs($user)->post(route('profile.photo.store'), ['photo' => $this->png('b.png', 200, 200)]);
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
            $this->png('big.png', 400, 400)->size(3000),
            $this->png('tiny.png', 40, 40),
        ] as $file) {
            $this->actingAs($user)->post(route('profile.photo.store'), ['photo' => $file])->assertSessionHasErrors('photo');
        }

        $this->assertNull($user->refresh()->profile_photo_path);
    }

    public function test_photos_are_only_served_to_signed_in_users(): void
    {
        Storage::fake('local');
        $owner = User::factory()->role('faculty')->create();
        $this->actingAs($owner)->post(route('profile.photo.store'), ['photo' => $this->png('me.png', 200, 200)]);
        $url = $owner->refresh()->profilePhotoUrl();

        $this->actingAs(User::factory()->create())->get($url)->assertOk();
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($owner)->get(route('profile.photo.show', User::factory()->create()))->assertNotFound();
    }
}
