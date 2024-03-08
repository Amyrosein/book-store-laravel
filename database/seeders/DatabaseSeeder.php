<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Author;
use App\Models\Book;
use App\Models\City;
use App\Models\Genre;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        City::factory(5)->create();
//        Author::factory(10)->create();
//        Genre::factory()->create(['name' =>  'جنایی']);
//        Genre::factory()->create(['name' =>  'ترسناک']);
//        Genre::factory()->create(['name' =>  'پلیسی']);
//        Genre::factory()->create(['name' =>  'معمایی']);
//        Genre::factory()->create(['name' =>  'علمی']);
//        Genre::factory()->create(['name' =>  'اکشن']);
//        Genre::factory()->create(['name' =>  'رومان']);
//        Book::factory(20)->create();


        $start = Carbon::parse('2024-01-01');
        $end = Carbon::parse('2024-03-07');

        Reservation::factory(4)->create([
            'user_id' => 1,
            'book_id' => function () {
                $book_id = null;
                do {
                    $book_id = mt_rand(1, 20);
                } while (Reservation::where('book_id', $book_id)->exists());
                return $book_id;
            },
            'created_at' => function () use ($start, $end) {
                return Carbon::createFromTimestamp(mt_rand($start->timestamp, $end->timestamp));
            },
            'updated_at' => function () use ($start, $end) {
                return Carbon::createFromTimestamp(mt_rand($start->timestamp, $end->timestamp));
            },
            'expiration_date' => function () use ($start, $end) {
                $rand_date = Carbon::createFromTimestamp(mt_rand($start->timestamp, $end->timestamp));
                $rand_days = mt_rand(1, 7);
                return $rand_date->addDays($rand_days);
            },
            'is_paid' => rand(0, 1),
            'cost' => function () {
                return mt_rand(1, 7) * 1000;
            },
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
