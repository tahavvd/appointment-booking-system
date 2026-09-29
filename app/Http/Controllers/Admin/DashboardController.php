<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $todayAppointments = Appointment::with(['client', 'staff', 'service'])
            ->whereDate('start_time', $today)
            ->orderBy('start_time')
            ->get();

        $todayCount = $todayAppointments
            ->where('status', '!=', AppointmentStatus::Cancelled->value)
            ->count();

        $activeStaffCount = User::where('role', 'staff')->count();
        $servicesCount = Service::count();

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);

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
            'todayAppointments' => $todayAppointments,
            'todayCount' => $todayCount,
            'activeStaffCount' => $activeStaffCount,
            'servicesCount' => $servicesCount,
            'revenueByDay' => $revenueByDay,
            'weekRevenueTotal' => $revenueByDay->sum(),
            'today' => $today,
        ]);
    }
}
