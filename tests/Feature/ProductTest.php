<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seller = User::factory()->create(['role' => 'seller']);
    $this->buyer = User::factory()->create(['role' => 'buyer']);
});

test('seller can create product', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('product.jpg');

    $response = $this->actingAs($this->seller)
        ->postJson('/api/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => 1,
            'status' => 'published',
            'image' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'title', 'description', 'price', 'quantity',
        ]);

    $this->assertDatabaseHas('products', [
        'title' => 'Test Product',
        'seller_id' => $this->seller->id,
    ]);
});

test('buyer cannot create product', function () {
    $response = $this->actingAs($this->buyer)
        ->postJson('/api/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => 1,
        ]);

    $response->assertStatus(403);
});

test('anyone can view public products', function () {
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'price', 'seller'],
            ],
        ]);
});

test('seller can update their own product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    $response = $this->actingAs($this->seller)
        ->postJson("/api/products/{$product->id}", [
            '_method' => 'PUT',
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'price' => 149.99,
            'quantity' => 5,
            'category_id' => 1,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'title' => 'Updated Title',
    ]);
});

test('seller cannot update other seller product', function () {
    $otherSeller = User::factory()->create(['role' => 'seller']);
    $product = Product::factory()->create([
        'seller_id' => $otherSeller->id,
        'status' => 'published',
    ]);

    $response = $this->actingAs($this->seller)
        ->postJson("/api/products/{$product->id}", [
            '_method' => 'PUT',
            'title' => 'Updated Title',
        ]);

    $response->assertStatus(403);
});

test('seller can delete their own product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    $response = $this->actingAs($this->seller)
        ->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});
