<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoAppointmentSeeder extends Seeder
{
    /** How many days from today to fill with upcoming appointments. */
    private const DAYS_AHEAD = 100;

    /** Past days to fill too, so the dashboard has history and revenue. */
    private const DAYS_BACK = 14;

    private const MIN_PER_DAY = 1;
    private const MAX_PER_DAY = 5;

    private const CLIENT_NAMES = [
        'Yacine Benali',
        'Amine Boudiaf',
        'Walid Merabet',
        'Karim Hamidi',
        'Sofiane Zerrouki',
        'Riad Belkacem',
        'Nassim Cherif',
        'Hichem Bouzid',
        'Fouad Mebarki',
        'Mehdi Khelifi',
        'Islam Saadi',
        'Anis Bensalem',
        'Zakaria Ouali',
        'Oussama Larbi',
        'Bilal Haddad',
        'Ayoub Meziane',
        'Lotfi Boukhalfa',
        'Salim Brahimi',
        'Rachid Slimani',
        'Abdelhak Ferhat',
    ];

    public function run(): void
    {
        $availability = app(AvailabilityService::class);

        // Demo clients: name + phone only, phones 0711111101 ... 0711111120.
        $clients = collect(self::CLIENT_NAMES)->map(
            fn(string $name, int $i) => User::updateOrCreate(
                ['phone' => sprintf('07111111%02d', $i + 1)],
                ['name' => $name, 'role' => 'client'],
            )
        );

        // Re-seeding replaces the demo bookings and leaves everyone else's alone.
        Appointment::whereIn('client_id', $clients->pluck('id'))->delete();

        $staff = User::where('role', 'staff')->where('is_active', true)->get();

        $services = Service::where('is_active', true)
            ->with(['addons' => fn($q) => $q->where('is_active', true)])
            ->get();

        if ($staff->isEmpty() || $services->isEmpty()) {
            $this->command?->warn('No staff or services found, skipping demo appointments.');

            return;
        }

        $today = Carbon::today();
        $created = 0;

        try {
            for ($offset = -self::DAYS_BACK; $offset < self::DAYS_AHEAD; $offset++) {
                $day = $today->copy()->addDays($offset);
                $isPast = $offset < 0;

                // The availability engine only offers future slots, so for past
                // days we pretend "now" is that morning.
                Carbon::setTestNow($isPast ? $day->copy() : null);

                $dayClients = $clients->shuffle()->values();
                $target = random_int(self::MIN_PER_DAY, self::MAX_PER_DAY);

                for ($i = 0; $i < $target; $i++) {
                    $created += $this->book(
                        $availability,
                        $dayClients[$i],
                        $staff,
                        $services->random(),
                        $day,
                        $isPast,
                    );
                }
            }
        } finally {
            Carbon::setTestNow();
        }

        $this->command?->info("Seeded {$created} demo appointments for {$clients->count()} clients.");
    }

    /** Book one random free slot for the client. Returns 1 if booked, 0 if the day was full. */
    private function book(
        AvailabilityService $availability,
        User $client,
        $staff,
        Service $service,
        Carbon $day,
        bool $isPast,
    ): int {
        // About 40% of bookings include some of the service's add-ons.
        $addons = ($service->addons->isNotEmpty() && random_int(1, 100) <= 40)
            ? $service->addons->random(random_int(1, $service->addons->count()))
            : collect();

        $duration = $service->duration_minutes + $addons->sum('extra_duration_minutes');
        $price = $service->base_price + $addons->sum('extra_price');

        foreach ($staff->shuffle() as $member) {
            // Same engine the real booking flow uses: respects breaks,
            // days off and existing appointments.
            $slots = $availability->slotsFor($member, $day->copy(), $duration);

            // The availability engine ignores cancelled appointments, but the
            // unique (staff_id, start_time) index does not, so skip any start
            // time that already has a row, whatever its status.
            $used = Appointment::where('staff_id', $member->id)
                ->whereDate('start_time', $day->toDateString())
                ->pluck('start_time')
                ->map(fn($t) => Carbon::parse($t)->format('Y-m-d H:i'))
                ->all();

            $slots = array_values(array_filter(
                $slots,
                fn($slot) => ! in_array($slot->format('Y-m-d H:i'), $used, true)
            ));

            if ($slots === []) {
                continue;
            }

            $start = $slots[array_rand($slots)];

            $start = $slots[array_rand($slots)];

            $appointment = Appointment::create([
                'client_id' => $client->id,
                'staff_id' => $member->id,
                'service_id' => $service->id,
                'start_time' => $start,
                'end_time' => $start->copy()->addMinutes($duration),
                'total_price' => $price,
                'status' => $this->statusFor($isPast),
            ]);

            $appointment->addons()->attach($addons->pluck('id')->all());

            return 1;
        }

        return 0;
    }

    private function statusFor(bool $isPast): AppointmentStatus
    {
        if (! $isPast) {
            return AppointmentStatus::Confirmed;
        }

        $roll = random_int(1, 100);

        return match (true) {
            $roll <= 80 => AppointmentStatus::Completed,
            $roll <= 90 => AppointmentStatus::NoShow,
            default => AppointmentStatus::Cancelled,
        };
    }
}
