<?php

namespace App\Http\Controllers\Staff;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $staff = $request->user();
        $date = $this->resolveDate($request->query('date'));

        $appointments = $staff->staffAppointments()
            ->with(['client', 'service', 'addons'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        // "Next up" only makes sense when looking at today.
        $next = $date->isToday()
            ? $appointments->first(fn($a) => $a->status === AppointmentStatus::Confirmed && $a->end_time->isFuture())
            : null;

        $stats = [
            'todo' => $appointments->filter(fn($a) => $a->status === AppointmentStatus::Confirmed)->count(),
            'done' => $appointments->filter(fn($a) => $a->status === AppointmentStatus::Completed)->count(),
            'total' => $appointments->filter(fn($a) => $a->status !== AppointmentStatus::Cancelled)->count(),
        ];

        // Date strip: next 14 days, with how many bookings each has.
        $days = collect(range(0, 13))->map(fn($i) => today()->addDays($i));

        $counts = $staff->staffAppointments()
            ->where('status', '!=', AppointmentStatus::Cancelled->value)
            ->whereBetween('start_time', [today(), today()->addDays(14)])
            ->get(['start_time'])
            ->countBy(fn($a) => $a->start_time->toDateString());

        $hours = $staff->staffSchedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->orderBy('start_time')
            ->get()
            ->map(fn($r) => substr($r->start_time, 0, 5) . ' – ' . substr($r->end_time, 0, 5));

        $unmarkedCount = $staff->staffAppointments()
            ->where('status', AppointmentStatus::Confirmed->value)
            ->where('end_time', '<', now())
            ->count();

        return view('staff.dashboard', compact(
            'date',
            'appointments',
            'next',
            'stats',
            'days',
            'counts',
            'hours',
            'unmarkedCount'
        ));
    }

    private function resolveDate(?string $value): Carbon
    {
        if ($value) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable) {
                // fall through to today
            }
        }

        return today();
    }
}
