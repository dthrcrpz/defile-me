<?php

namespace Tests\Feature;

use App\Models\OauthClient;
use App\Models\OauthPersonalAccessClient;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function test_aUserAccountCanBeCreated () {
        $this->withoutExceptionHandling();
 
        $user = User::factory()->make();

        $attributes = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->post('/api/users', $attributes);

        $response->assertSee($user->email);
    }

    public function test_aUserCanLoginAndFetchData () {
        $this->withoutExceptionHandling();

        # create a dummy user
        $user = User::factory()->create();

        # attempt to login the created dummy user
        Passport::actingAs($user);

        # get the details of the logged in user
        $this->get('/api/user')
        ->assertJsonStructure(['user', 'email']);
    }

    public function test_aUserCanLogout () {
        $this->withoutExceptionHandling();

        # create a dummy user
        $user = User::factory()->create();

        # attempt to login the created dummy user
        Passport::actingAs($user);

        # call the logout API
        $this->post('/api/user/logout')->assertSuccessful();
    }
}
