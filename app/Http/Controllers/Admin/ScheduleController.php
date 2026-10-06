<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /** Salon week order. Key = day_of_week as stored (0 = Sunday). */
    private const DAYS = [
        6 => 'Saturday',
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
    ];

    public function index(Request $request): View
    {
        $staffList = User::where('role', 'staff')
            ->with('staffSchedules')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $selected = $staffList->firstWhere('id', (int) $request->query('staff'))
            ?? $staffList->first();

        $weeks = $staffList->mapWithKeys(fn(User $s) => [$s->id => $this->weekFor($s)]);

        $week = null;
        if ($selected) {
            // After a failed save, show what the admin typed instead of the DB version.
            $oldDays = old('days');
            $week = is_array($oldDays) ? $this->weekFromInput($oldDays) : $weeks[$selected->id];
        }

        return view('admin.schedules.index', [
            'staffList' => $staffList,
            'selected' => $selected,
            'weeks' => $weeks,
            'week' => $week,
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $request->validate(['days' => ['required', 'array']]);

        $rows = [];
        $errors = [];

        foreach (self::DAYS as $day => $name) {
            $d = $request->input("days.$day", []);

            if (! filter_var($d['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue; // day off = no rows
            }

            $start = (string) ($d['start'] ?? '');
            $end = (string) ($d['end'] ?? '');

            if (! $this->isTime($start) || ! $this->isTime($end)) {
                $errors["days.$day"] = "$name: set a valid opening and closing time.";
                continue;
            }

            if ($end <= $start) {
                $errors["days.$day"] = "$name: closing time must be after opening time.";
                continue;
            }

            if (filter_var($d['has_break'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $breakStart = (string) ($d['break_start'] ?? '');
                $breakEnd = (string) ($d['break_end'] ?? '');

                if (! $this->isTime($breakStart) || ! $this->isTime($breakEnd)) {
                    $errors["days.$day"] = "$name: set a valid break start and end.";
                    continue;
                }

                if (! ($start < $breakStart && $breakStart < $breakEnd && $breakEnd < $end)) {
                    $errors["days.$day"] = "$name: the break must sit inside working hours.";
                    continue;
                }

                $rows[] = ['day_of_week' => $day, 'start_time' => $start, 'end_time' => $breakStart];
                $rows[] = ['day_of_week' => $day, 'start_time' => $breakEnd, 'end_time' => $end];
            } else {
                $rows[] = ['day_of_week' => $day, 'start_time' => $start, 'end_time' => $end];
            }
        }

        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }

        DB::transaction(function () use ($staff, $rows) {
            $staff->staffSchedules()->delete();
            $staff->staffSchedules()->createMany($rows);
        });

        // Heads-up: confirmed future appointments that no longer fit the new hours.
        $outside = $staff->staffAppointments()
            ->where('status', AppointmentStatus::Confirmed->value)
            ->where('start_time', '>=', now())
            ->get()
            ->filter(fn($a) => ! $this->fits(
                $a->start_time->dayOfWeek,
                $a->start_time->format('H:i'),
                $a->end_time->format('H:i'),
                $rows
            ))
            ->count();

        $redirect = redirect()
            ->route('admin.schedules.index', ['staff' => $staff->id])
            ->with('status', "Schedule saved for {$staff->name}.");

        if ($outside > 0) {
            $redirect->with('warning', $outside . ' upcoming appointment' . ($outside === 1 ? '' : 's')
                . ' now fall outside these hours. They stay booked, so review them in Appointments.');
        }

        return $redirect;
    }

    /** Turn a stylist's schedule rows into an ordered list of 7 editable days. */
    private function weekFor(User $staff): array
    {
        $byDay = $staff->staffSchedules->groupBy('day_of_week');
        $week = [];

        foreach (self::DAYS as $day => $name) {
            $rows = $byDay->get($day, collect())->sortBy('start_time')->values();

            if ($rows->isEmpty()) {
                $week[] = $this->makeDay($day, false, '09:00', '18:00', false, '12:00', '13:00');
                continue;
            }

            // Two rows in one day = a break between them.
            $hasBreak = $rows->count() >= 2;

            $week[] = $this->makeDay(
                $day,
                true,
                substr($rows->first()->start_time, 0, 5),
                substr($rows->last()->end_time, 0, 5),
                $hasBreak,
                $hasBreak ? substr($rows[0]->end_time, 0, 5) : '12:00',
                $hasBreak ? substr($rows[1]->start_time, 0, 5) : '13:00',
            );
        }

        return $week;
    }

    private function weekFromInput(array $input): array
    {
        $week = [];

        foreach (self::DAYS as $day => $name) {
            $d = $input[$day] ?? [];

            $week[] = $this->makeDay(
                $day,
                filter_var($d['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                (string) ($d['start'] ?? '09:00'),
                (string) ($d['end'] ?? '18:00'),
                filter_var($d['has_break'] ?? false, FILTER_VALIDATE_BOOLEAN),
                (string) ($d['break_start'] ?? '12:00'),
                (string) ($d['break_end'] ?? '13:00'),
            );
        }

        return $week;
    }

    private function makeDay(int $day, bool $on, string $start, string $end, bool $hasBreak, string $breakStart, string $breakEnd): array
    {
        return [
            'day' => $day,
            'label' => self::DAYS[$day],
            'on' => $on,
            'start' => $start,
            'end' => $end,
            'has_break' => $hasBreak,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }

    private function isTime(string $value): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value);
    }

    private function fits(int $dayOfWeek, string $start, string $end, array $rows): bool
    {
        foreach ($rows as $row) {
            if ($row['day_of_week'] === $dayOfWeek && $start >= $row['start_time'] && $end <= $row['end_time']) {
                return true;
            }
        }

        return false;
    }
}
