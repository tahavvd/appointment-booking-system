<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\User;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Granularity of bookable start times, in minutes.
     */
    protected int $slotInterval = 30;

    /**
     * Get every bookable start time for a staff member, on a given date,
     * for a service of the given duration.
     *
     * @return Carbon[]
     */
    public function slotsFor(User $staff, Carbon $date, int $serviceDurationMinutes): array
    {
        $schedule = $staff->staffSchedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        // No schedule row for this day at all = staff doesn't work this day.
        if (! $schedule) {
            return [];
        }

        $windowStart = Carbon::parse($date->toDateString() . ' ' . $schedule->start_time);
        $windowEnd = Carbon::parse($date->toDateString() . ' ' . $schedule->end_time);

        $busyRanges = $staff->staffAppointments()
            ->whereDate('start_time', $date->toDateString())
            ->where('status', '!=', AppointmentStatus::Cancelled->value)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $freeGaps = $this->computeFreeGaps($windowStart, $windowEnd, $busyRanges);

        return $this->generateSlots($freeGaps, $serviceDurationMinutes);
    }

    /**
     * Given a working window and a list of busy ranges inside it,
     * return the free ranges left over.
     *
     * @return array<array{0: Carbon, 1: Carbon}>
     */
    protected function computeFreeGaps(Carbon $windowStart, Carbon $windowEnd, $busyRanges): array
    {
        $gaps = [];
        $cursor = $windowStart->copy();

        foreach ($busyRanges as $busy) {
            if ($busy->start_time->gt($cursor)) {
                $gaps[] = [$cursor->copy(), $busy->start_time->copy()];
            }

            if ($busy->end_time->gt($cursor)) {
                $cursor = $busy->end_time->copy();
            }
        }

        if ($cursor->lt($windowEnd)) {
            $gaps[] = [$cursor->copy(), $windowEnd->copy()];
        }

        return $gaps;
    }

    /**
     * Turn free gaps into actual bookable start times, spaced by
     * $slotInterval, only where the full service duration still fits.
     *
     * @return Carbon[]
     */
    protected function generateSlots(array $freeGaps, int $serviceDurationMinutes): array
    {
        $slots = [];

        foreach ($freeGaps as [$gapStart, $gapEnd]) {
            $slotStart = $gapStart->copy();

            while ($slotStart->copy()->addMinutes($serviceDurationMinutes)->lte($gapEnd)) {
                if ($slotStart->isFuture()) {
                    $slots[] = $slotStart->copy();
                }

                $slotStart->addMinutes($this->slotInterval);
            }
        }

        return $slots;
    }
}
