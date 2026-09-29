<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->startOfDay()
            : today();

        $appointments = Appointment::with(['client', 'staff', 'service'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'date' => $date,
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
        ]);

        $appointment->update(['status' => $validated['status']]);

        return back()->with('status', 'Appointment updated.');
    }
}
