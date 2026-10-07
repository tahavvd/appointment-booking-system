<?php

namespace App\Http\Controllers\Staff;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    private const FILTERS = ['completed', 'no_show', 'cancelled', 'unmarked'];

    public function index(Request $request): View
    {
        $staff = $request->user();
        $filter = in_array($request->query('filter'), self::FILTERS, true) ? $request->query('filter') : 'all';

        // History = appointments that are over, plus anything cancelled.
        $query = $staff->staffAppointments()
            ->with(['client', 'service', 'addons'])
            ->where(fn($q) => $q
                ->where('end_time', '<', now())
                ->orWhere('status', AppointmentStatus::Cancelled->value));

        match ($filter) {
            'completed' => $query->where('status', AppointmentStatus::Completed->value),
            'no_show' => $query->where('status', AppointmentStatus::NoShow->value),
            'cancelled' => $query->where('status', AppointmentStatus::Cancelled->value),
            'unmarked' => $query->where('status', AppointmentStatus::Confirmed->value),
            default => null,
        };

        $appointments = $query->orderByDesc('start_time')->simplePaginate(15)->withQueryString();

        $month = $staff->staffAppointments()
            ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as done,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_shows,
                SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as value
            ")
            ->first();

        return view('staff.history', [
            'appointments' => $appointments,
            'filter' => $filter,
            'done' => (int) ($month->done ?? 0),
            'noShows' => (int) ($month->no_shows ?? 0),
            'value' => (float) ($month->value ?? 0),
        ]);
    }
}
