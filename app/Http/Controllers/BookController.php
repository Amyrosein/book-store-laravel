<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with(['genre', 'author.city']);
        if ($request->filled('s')) {
            $search = $request->get('s');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('lp') || $request->filled('hp')) {
            $lp = $request->get('lp', 0);
            $hp = $request->get('hp', PHP_INT_MAX);
            $query->havingBetween('price', [$lp, $hp]);
        }

        if ($request->filled('genre')) {
            $genre = $request->get('genre');
            $query->whereHas('genre', function (Builder $query) use ($genre) {
                $query->where('name', $genre);
            });
        }

        if ($request->filled('city')) {
            $cityId = $request->get('city');
            $query->whereHas('author.city', function ($query) use ($cityId) {
                $query->where('id', $cityId);
            });
        }

        if ($request->filled('sort')) {
            $sorted = $request->get('sort');
            $query->orderBy('price', $sorted);
        }

        $books = $query->paginate(5);

        $books->appends($request->except('page'));

        return new BookCollection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();
        $new_book = Book::create($validated);
        return response()->json(
            data:[
                'message' => "Book {$new_book->id} has been created successfully"
            ],
            status:201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return new BookResource($book->load(['genre', 'author.city']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();
        $book->update($validated);

        return new BookResource($book->load(['genre', 'author.city']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(status:204);
    }
}
