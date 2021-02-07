<?php

namespace Tests\Feature;

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
        $file = UploadedFile::fake()->image('avatar.jpg');

        # create a media
        $this->createMedia($file);
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
        $attributes = [
            'file' => $file,
            'type' => 'image'
        ];

        # call store API
        $response = $this->post('/api/medias', $attributes);
        $response->assertSee($user->id);
    }
}
