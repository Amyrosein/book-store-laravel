<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationCollection;
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

        // check if user is not vip and want to reserve more than 7 days
        if ( ! $user->is_vip() && $validated['reserve_days'] > 7) {
            return response()->json([
                'message' => 'regular users can only reserve books for maximum 7 days',
                'status'  => false,
            ], 409);
        }
        // check book is already reserved
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


        // change reserve days to datetime format
        $exp_date = now()->addDays($validated['reserve_days']);

        // if user is vip => free reservation
        if ($user->is_vip()) {
            $cost = 0;
        } else {
            // if regular user paid +300,000 tomans in last 2 month => free reservation
            $query    = Reservation::where('user_id', $user->id)
                ->where('is_paid', '=', true);
            $cost_sum = $query->where('created_at', '>=', now()->subMonths(2))->sum('cost');
            if ($cost_sum > 300000) {
                $cost = 0;
            } else {
                // if regular user has reserved +3 books in last month => get 30 % discount
                $reservations_count = $query
                    ->where('created_at', '>=', now()->subMonth())
                    ->count();
                $cost               = $validated['reserve_days'] * 1000;
                $cost               = $reservations_count > 3 ? $cost * 70 / 100 : $cost;
            }
        }

        // create reservation
        $reserve = Reservation::create([
            'user_id'         => $user->id,
            'book_id'         => $validated['book'],
            'expiration_date' => $exp_date,
            'cost'            => $cost,
            'is_paid'         => $cost == 0,
        ]);

        // check users paid or not ( if cost is free : its paid )
        if ($reserve->is_paid) {
            return response()->json([
                'message' => 'Book reserved successfully',
                'status'  => true,
            ], 201);
        }
        // if not paid : send to payment gateway

        // after pay :
        $reserve->is_paid = true;

        return response()->json([
            'message' => 'Book reserved successfully',
            'status'  => true,
        ], 201);
    }

    public function reserved_books(Request $request)
    {
        $user      = $request->user();
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // check if non Admin user want to access another user reserves
        if ( ! $user->is_admin() && $validated['user_id'] != $user->id) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        };

        // get user paid reservations
        $reservations = Reservation::where('user_id', '=', $validated['user_id'])
            ->where('is_paid', '=', true)
            ->with(['user', 'book.genre', 'book.author.city'])
            ->get();

        return new ReservationCollection($reservations);
    }
}
