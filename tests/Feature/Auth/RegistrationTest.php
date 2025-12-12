<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

/*
|--------------------------------------------------------------------------
| Before Each Test
|--------------------------------------------------------------------------
| we need roles and permissions
| so we have to run seeder before each test
| use seed in beforeAll is not possible at the moment.
*/

beforeEach(function () {
    seed(RolePermissionSeeder::class);
});

test('new users can register', function () {
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(200);
});

test('user after register has user role', function () {
    postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->hasRole('user'))->toBeTrue();
});
