<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'year' => 2008,
            'status' => 'reading',
        ], $overrides);
    }

    public function test_books_endpoint_without_token_returns_401(): void
    {
        // Sengaja tanpa header Accept: application/json (seperti Postman default).
        $this->get('/api/books')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_returns_all_books(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Book::factory()->count(3)->create();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_store_valid_book_returns_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', $this->validPayload())
            ->assertCreated()
            ->assertJsonFragment(['title' => 'Clean Code']);

        $this->assertDatabaseHas('books', ['title' => 'Clean Code', 'status' => 'reading']);
    }

    public function test_store_invalid_book_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', ['status' => 'invalid'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'author', 'year', 'status']);
    }

    public function test_show_returns_book_and_404_when_missing(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create();

        $this->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $book->id]);

        $this->getJson('/api/books/999999')->assertNotFound();
    }

    public function test_update_book_returns_200(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create(['status' => 'to_read']);

        $this->putJson("/api/books/{$book->id}", ['status' => 'finished'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'finished']);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'status' => 'finished']);
    }

    public function test_destroy_book_returns_200_and_removes_it(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create();

        $this->deleteJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJson(['message' => 'Book deleted.']);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}