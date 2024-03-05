<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Author;
use App\Models\Book;
use App\Models\City;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        City::factory(5)->create();
        Author::factory(10)->create();
        Genre::factory()->create(['name' =>  'جنایی']);
        Genre::factory()->create(['name' =>  'ترسناک']);
        Genre::factory()->create(['name' =>  'پلیسی']);
        Genre::factory()->create(['name' =>  'معمایی']);
        Genre::factory()->create(['name' =>  'علمی']);
        Genre::factory()->create(['name' =>  'اکشن']);
        Genre::factory()->create(['name' =>  'رومان']);
        Book::factory(20)->create();

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
