<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_theMediasOfAUserCanBeFetched () {
        $this->withoutExceptionHandling();

        # generate a dummy image file
        $file = UploadedFile::fake()->image('avatar.jpg');

        # create a media
        $this->createMedia($file);

        # call the index API
        $response = $this->get('/api/medias');
        $response->assertSuccessful();
    }

    public function test_aMediaWithImageCanBeCreated () {
        $this->withoutExceptionHandling();

        # generate a dummy image file
        $file = UploadedFile::fake()->image('avatar.jpg');

        # create a media
        $this->createMedia($file);
    }

    public function test_aMediaWithVideoCanBeCreated () {
        $this->withoutExceptionHandling();

        # generate a dummy video file
        $file = UploadedFile::fake()->create('dummy-video.mp4', 50, 'mp4');

        # create a media
        $this->createMedia($file);
    }

    public function test_aMediaOfAUserCanBeFetched () {
        $this->withoutExceptionHandling();

        # generate a dummy image file
        $file = UploadedFile::fake()->image('avatar.jpg');

        # create a media
        $media = $this->createMedia($file);

        # call the show API
        $response = $this->get("/api/medias/$media->id");
        $response->assertSee($media->temporary_id);
    }

    public function test_aMediaOfAUserCanBeDeleted () {
        $this->withoutExceptionHandling();

        # generate a dummy image file
        $file = UploadedFile::fake()->image('avatar.jpg');

        # create a media
        $media = $this->createMedia($file);

        # call the delete API
        $response = $this->delete("/api/medias/$media->id");
        $this->assertSoftDeleted('medias', [
            'id' => $media->id
        ]);
    }

    protected function createUser () {
        $this->withoutExceptionHandling();
 
        $user = User::factory()->make();

        $attributes = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->post('/api/users', $attributes);

        $response->assertSee($user->email);

        $createdUser = User::where('email', $attributes['email'])->first();
        return $createdUser;
    }

    protected function createMedia ($file) {
        # create a user first. that user will own this media
        $user = $this->createUser();
        Passport::actingAs($user);

        # generate media data to submit
        $temporaryID = str_random(15);

        $attributes = [
            'file' => $file,
            'type' => 'image',
            'temporary_id' => $temporaryID
        ];

        # call store API
        $response = $this->post('/api/medias', $attributes);
        $response->assertSee($temporaryID);

        $createdMedia = Media::where('temporary_id', $temporaryID)->first();
        return $createdMedia;
    }
}
