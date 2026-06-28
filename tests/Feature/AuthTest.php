<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
});

test('user can register', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'buyer',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'role'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
    ]);
});

test('user cannot register with invalid email', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'buyer',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'role'],
            'token',
        ]);
});

test('user cannot login with wrong password', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can get profile', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $this->user->id,
            'email' => 'test@example.com',
        ]);
});

test('unauthenticated user cannot get profile', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});

test('user can logout', function () {
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/auth/logout');

    $response->assertStatus(200);
});
