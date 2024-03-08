<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function reserve_book(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'book'         => ['required', 'integer', 'exists:books,id'],
            'reserve_days' => ['required', 'integer', 'min:1', 'max:14'],
        ]);
        $book_is_reserved = Reservation::where('book_id', $validated['book'])
            ->where('expiration_date', '>=', now())
            ->where('is_paid', true)
            ->exists();
        if ($book_is_reserved) {
            return response()->json([
                'message' => 'This book is reserved',
                'status'  => false,
            ], 409);
        }

        // Delete unpaid user reservations for the same book
         Reservation::where('book_id', $validated['book'])
            ->where('user_id', $user->id)
            ->where('is_paid', false)
            ->delete();

        if ( ! $user->is_vip() && $validated['reserve_days'] > 7) {
            return response()->json([
                'message' => 'regular users can only reserve books for maximum 7 days',
                'status'  => false,
            ], 409);
        }

        $exp_date = now()->addDays($validated['reserve_days']);

        if ($user->is_vip()) {
            // if user is vip => free reservation
            $cost = 0;
        } else {
            // if user paid +300,000 toman in last 2 month => free reservation
            $query    = Reservation::where('user_id', $user->id)
                ->where('is_paid', '=', true);
            $cost_sum = $query->where('created_at', '>=', now()->subMonths(2))->sum('cost');
            if ($cost_sum > 300000) {
                $cost = 0;
            } else {
                // if user has reserved +3 books in last month => get 30 % discount
                $reservations_count = $query
                    ->where('created_at', '>=', now()->subMonth())
                    ->count();
                $cost               = $validated['reserve_days'] * 1000;
                $cost               = $reservations_count > 3 ? $cost * 70 / 100 : $cost;
            }
        }

        $reserve = Reservation::create([
            'user_id'         => $user->id,
            'book_id'         => $validated['book'],
            'expiration_date' => $exp_date,
            'cost'            => $cost,
            'is_paid'         => $cost == 0,
        ]);
        if ($reserve->is_paid){
            return response()->json([
                'message' => 'Book reserved successfully',
                'status'  => true,
            ], 201);
        }
        // send to payment gateway

        // sample data for payment gateway
        return response()->json([
            'data' => [
                'reserve_id' => $reserve->id,
                'cost' => $reserve->cost,
            ],
            'status' => true
        ], 201);
    }
}
