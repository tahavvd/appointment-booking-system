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
     * A day can have several working windows (e.g. a lunch break splits
     * the day into two), so each window is processed on its own.
     *
     * @return Carbon[]
     */
    public function slotsFor(User $staff, Carbon $date, int $serviceDurationMinutes): array
    {
        $windows = $staff->staffSchedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        // No schedule rows for this day at all = staff doesn't work this day.
        if ($windows->isEmpty()) {
            return [];
        }

        $busyRanges = $staff->staffAppointments()
            ->whereDate('start_time', $date->toDateString())
            ->where('status', '!=', AppointmentStatus::Cancelled->value)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $slots = [];

        foreach ($windows as $window) {
            $windowStart = Carbon::parse($date->toDateString() . ' ' . $window->start_time);
            $windowEnd = Carbon::parse($date->toDateString() . ' ' . $window->end_time);

            $freeGaps = $this->computeFreeGaps($windowStart, $windowEnd, $busyRanges);

            array_push($slots, ...$this->generateSlots($freeGaps, $serviceDurationMinutes));
        }

        return $slots;
    }

    /**
     * Given a working window and a list of busy ranges (sorted by start),
     * return the free ranges left over. Busy ranges are clamped to the
     * window, so an appointment outside the working hours can never
     * create a free gap outside them.
     *
     * @return array<array{0: Carbon, 1: Carbon}>
     */
    protected function computeFreeGaps(Carbon $windowStart, Carbon $windowEnd, $busyRanges): array
    {
        $gaps = [];
        $cursor = $windowStart->copy();

        foreach ($busyRanges as $busy) {
            // Entirely before the cursor: nothing to carve out.
            if ($busy->end_time->lte($cursor)) {
                continue;
            }

            // Starts after the window closes: nothing more can matter.
            if ($busy->start_time->gte($windowEnd)) {
                break;
            }

            if ($busy->start_time->gt($cursor)) {
                $gaps[] = [$cursor->copy(), $busy->start_time->copy()];
            }

            $cursor = $busy->end_time->copy();
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
