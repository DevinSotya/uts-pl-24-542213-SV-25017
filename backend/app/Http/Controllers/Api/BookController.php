<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    /**
     * GET /api/books -> 200 + daftar buku.
     */
    public function index(): JsonResponse
    {
        return response()->json(Book::orderBy('id')->get());
    }

    /**
     * POST /api/books -> 201 + buku baru (422 bila validasi gagal, dari StoreBookRequest).
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        Log::info('Book created', [
            'event' => 'book.created',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'create_book',
            'reason' => 'user_request',
            'book_id' => $book->id,
        ]);

        return response()->json($book, 201);
    }

    /**
     * GET /api/books/{book} -> 200 (404 JSON bila id tidak ada, via route model binding).
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json($book);
    }

    /**
     * PUT|PATCH /api/books/{book} -> 200 + buku terbaru.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $validated = $request->validated();
        $book->update($validated);

        Log::info('Book updated', [
            'event' => 'book.updated',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'update_book',
            'reason' => 'user_request',
            'book_id' => $book->id,
            'changed_fields' => array_keys($validated),
        ]);

        return response()->json($book);
    }

    /**
     * DELETE /api/books/{book} -> 200 + pesan JSON (bukan 204, karena balasan harus JSON).
     */
    public function destroy(Request $request, Book $book): JsonResponse
    {
        $book->delete();

        Log::info('Book deleted', [
            'event' => 'book.deleted',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'delete_book',
            'reason' => 'user_request',
            'book_id' => $book->id,
        ]);

        return response()->json(['message' => 'Book deleted.']);
    }
}