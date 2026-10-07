<?php

namespace App\Http\Controllers\Staff;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        // Staff can only touch their own appointments.
        abort_unless((int) $appointment->staff_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                AppointmentStatus::Completed->value,
                AppointmentStatus::NoShow->value,
                AppointmentStatus::Cancelled->value,
            ])],
        ]);

        if ($appointment->status !== AppointmentStatus::Confirmed) {
            return back()->with('error', 'That appointment was already updated.');
        }

        $status = AppointmentStatus::from($validated['status']);

        if ($status !== AppointmentStatus::Cancelled && $appointment->start_time->isFuture()) {
            return back()->with('error', 'You can mark it completed or no-show once it has started.');
        }

        $appointment->update(['status' => $status]);

        return back()->with('status', match ($status) {
            AppointmentStatus::Completed => 'Marked as completed.',
            AppointmentStatus::NoShow => 'Marked as no-show.',
            default => 'Appointment cancelled. Remember to let the client know.',
        });
    }
}
