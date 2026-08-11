<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Show the initial booking form (name + phone).
     */
    public function create(): View
    {
        return view('booking.start');
    }

    /**
     * Handle the client identification step.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        // Normalize the phone number before storing/matching, so
        // "0555 12 34 56" and "0555123456" are treated as the same client.
        $phone = preg_replace('/\s+/', '', $validated['phone']);

        $client = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => $validated['name']],
        );

        // Keep the client tied to this booking session until we build
        // the next step (service selection).
        session(['booking.client_id' => $client->id]);

        return redirect()->route('booking.start')
            ->with('status', "Thanks {$client->name}, next step coming soon.");
    }
}
