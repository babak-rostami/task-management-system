<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

test('users can login and receive a sanctum token', function () {

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'status',
        'message',
        'data' => [
            'token'
        ]
    ]);

    expect(User::first()->tokens()->count())->toBe(1);
});

test('user can not access login and register route after login', function () {

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Sanctum::actingAs($user);

    $login_response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $register_response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $login_response->assertStatus(409);
    $register_response->assertStatus(409);
});

test('login fails with wrong password', function () {

    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
});

test('login fails when email is not found', function () {

    $response = postJson('/api/login', [
        'email' => 'notfound@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422);
});

test('users can logout', function () {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = postJson('/api/logout');

    $response->assertStatus(200);

    expect($user->tokens()->count())->toBe(0);
});
