<?php

namespace App\Http\Controllers;

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
//        if($request->filled('s') ){
//            $search = $request->get('s');
//            return new BookCollection(Book::with(['genre', 'author.city'])
//                ->where('title', 'like', "%{$search}%")
//                ->paginate(5)
//                ->appends('s', $search)
//            );
//        }
//        return new BookCollection(Book::with(['genre', 'author.city'])->paginate(5));

        $query = Book::with(['genre', 'author.city']);
        if($request->filled('s') ) {
            $search = $request->get('s');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('lp') || $request->filled('hp')){
            $lp = $request->get('lp', 0);
            $hp = $request->get('hp', PHP_INT_MAX);
            $query->havingBetween('price', [$lp, $hp]);

        }

        if ($request->filled('genre')){
            $genre = $request->get('genre');
            $query->whereHas('genre', function (Builder $query) use ($genre) {
                $query->where('name', $genre);
            })->get();
        }

        $books = $query->paginate(5);

        $books->appends($request->except('page'));

        return new BookCollection($books);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }
}
