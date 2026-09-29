<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todayCount = Appointment::whereDate('start_time', today())
            ->where('status', '!=', AppointmentStatus::Cancelled->value)
            ->count();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Revenue only counts appointments that were actually honoured —
        // cancellations and no-shows never generated income.
        $weekAppointments = Appointment::whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->whereIn('status', [AppointmentStatus::Confirmed->value, AppointmentStatus::Completed->value])
            ->get(['start_time', 'total_price']);

        $revenueByDay = collect(range(0, 6))->mapWithKeys(function ($i) use ($startOfWeek, $weekAppointments) {
            $day = $startOfWeek->copy()->addDays($i);

            $total = $weekAppointments
                ->filter(fn($appointment) => $appointment->start_time->isSameDay($day))
                ->sum('total_price');

            return [$day->format('D') => (float) $total];
        });

        return view('admin.dashboard', [
            'todayCount' => $todayCount,
            'revenueByDay' => $revenueByDay,
            'weekRevenueTotal' => $revenueByDay->sum(),
        ]);
    }
}
