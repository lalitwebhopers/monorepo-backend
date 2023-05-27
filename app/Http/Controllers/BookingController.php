<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::query();
        switch ($request->filter) {
            case 'today':
                $bookings->whereDate('date', '=', Carbon::today()->format('Y-m-d'));
                break;
            case 'past':
                $bookings->whereDate('date', '<', Carbon::today()->format('Y-m-d'));
                break;
            case 'future':
                $bookings->whereDate('date', '>', Carbon::today()->format('Y-m-d'));
                break;
        }
        $bookings = $bookings->get();
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:' . Carbon::today()->format('Y-m-d'),
            'reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Booking::create([
                'date' => $request->date,
                'reason' => $request->reason
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
