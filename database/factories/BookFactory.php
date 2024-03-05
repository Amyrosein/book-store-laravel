<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => fake()->realText(10),
            'release_date' => fake()->date(),
            'isbn' => fake()->isbn13(),
            'price' => fake()->numberBetween(50000, 250000),
            'genre_id' => fake()->numberBetween(1, 7),
            'author_id' => fake()->numberBetween(1, 10),
        ];
    }
}
