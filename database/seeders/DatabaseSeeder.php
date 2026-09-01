<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // --- Staff & Owner ---

        User::factory()->create([
            'name' => 'Karim',
            'email' => 'karim@salonyasmine.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $yasmine = User::factory()->create([
            'name' => 'Yasmine',
            'email' => 'yasmine@salonyasmine.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $sara = User::factory()->create([
            'name' => 'Sara',
            'email' => 'sara@salonyasmine.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        // Yasmine: closed Friday
        foreach ([0, 1, 2, 3, 4, 6] as $day) {
            $yasmine->staffSchedules()->create([
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '18:00',
            ]);
        }

        // Sara: closed Sunday, shorter days
        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            $sara->staffSchedules()->create([
                'day_of_week' => $day,
                'start_time' => '10:00',
                'end_time' => '17:00',
            ]);
        }

        // --- Services (photo is a stable seeded placeholder, not
        // a literally accurate photo — see chat notes on why) ---

        $haircut = Service::create([
            'name' => 'Haircut',
            'description' => 'A classic cut tailored to your face shape and style.',
            'photo' => 'https://picsum.photos/seed/salon-haircut/600/400',
            'base_price' => 1500,
            'duration_minutes' => 30,
        ]);

        $coloring = Service::create([
            'name' => 'Hair Coloring',
            'description' => 'Full color or highlights using professional-grade dye.',
            'photo' => 'https://picsum.photos/seed/salon-coloring/600/400',
            'base_price' => 4000,
            'duration_minutes' => 90,
        ]);

        $blowout = Service::create([
            'name' => 'Blowout',
            'description' => 'Wash, blow-dry, and style for a polished finish.',
            'photo' => 'https://picsum.photos/seed/salon-blowout/600/400',
            'base_price' => 1200,
            'duration_minutes' => 45,
        ]);

        $manicure = Service::create([
            'name' => 'Manicure',
            'description' => 'Nail shaping, cuticle care, and polish of your choice.',
            'photo' => 'https://picsum.photos/seed/salon-manicure/600/400',
            'base_price' => 1000,
            'duration_minutes' => 40,
        ]);

        // --- Add-ons ---

        ServiceAddon::create([
            'service_id' => $haircut->id,
            'name' => 'Beard Trim',
            'extra_price' => 300,
            'extra_duration_minutes' => 15,
        ]);

        ServiceAddon::create([
            'service_id' => $haircut->id,
            'name' => 'Hair Wash',
            'extra_price' => 200,
            'extra_duration_minutes' => 10,
        ]);

        ServiceAddon::create([
            'service_id' => $coloring->id,
            'name' => 'Deep Conditioning Treatment',
            'extra_price' => 800,
            'extra_duration_minutes' => 20,
        ]);

        ServiceAddon::create([
            'service_id' => $manicure->id,
            'name' => 'Gel Polish',
            'extra_price' => 500,
            'extra_duration_minutes' => 15,
        ]);

        // --- Test clients ---

        $amina = User::factory()->create(['name' => 'Amina', 'phone' => '0555111111']);
        $nadia = User::factory()->create(['name' => 'Nadia', 'phone' => '0555222222']);
        $leila = User::factory()->create(['name' => 'Leila', 'phone' => '0555333333']);
        $farid = User::factory()->create(['name' => 'Farid', 'phone' => '0555444444']);

        // --- Already-booked appointments, to exercise the availability engine ---

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $dayAfter = Carbon::today()->addDays(2);

        // Yasmine, today: a single appointment mid-day.
        Appointment::create([
            'client_id' => $amina->id,
            'staff_id' => $yasmine->id,
            'service_id' => $haircut->id,
            'start_time' => $today->copy()->setTime(10, 0),
            'end_time' => $today->copy()->setTime(10, 30),
            'total_price' => $haircut->base_price,
            'status' => AppointmentStatus::Confirmed->value,
        ]);

        // Yasmine, today: back-to-back pair, to test that no phantom
        // gap gets created between two adjacent appointments.
        Appointment::create([
            'client_id' => $nadia->id,
            'staff_id' => $yasmine->id,
            'service_id' => $blowout->id,
            'start_time' => $today->copy()->setTime(15, 0),
            'end_time' => $today->copy()->setTime(15, 45),
            'total_price' => $blowout->base_price,
            'status' => AppointmentStatus::Confirmed->value,
        ]);
        Appointment::create([
            'client_id' => $leila->id,
            'staff_id' => $yasmine->id,
            'service_id' => $haircut->id,
            'start_time' => $today->copy()->setTime(15, 45),
            'end_time' => $today->copy()->setTime(16, 15),
            'total_price' => $haircut->base_price,
            'status' => AppointmentStatus::Confirmed->value,
        ]);

        // Yasmine, tomorrow: a long coloring appointment.
        Appointment::create([
            'client_id' => $farid->id,
            'staff_id' => $yasmine->id,
            'service_id' => $coloring->id,
            'start_time' => $tomorrow->copy()->setTime(14, 0),
            'end_time' => $tomorrow->copy()->setTime(15, 30),
            'total_price' => $coloring->base_price,
            'status' => AppointmentStatus::Confirmed->value,
        ]);

        // Sara, today: one appointment.
        Appointment::create([
            'client_id' => $nadia->id,
            'staff_id' => $sara->id,
            'service_id' => $blowout->id,
            'start_time' => $today->copy()->setTime(11, 0),
            'end_time' => $today->copy()->setTime(11, 45),
            'total_price' => $blowout->base_price,
            'status' => AppointmentStatus::Confirmed->value,
        ]);

        // Sara, day after tomorrow: an early manicure.
        Appointment::create([
            'client_id' => $amina->id,
            'staff_id' => $sara->id,
            'service_id' => $manicure->id,
            'start_time' => $dayAfter->copy()->setTime(10, 0),
            'end_time' => $dayAfter->copy()->setTime(10, 40),
            'total_price' => $manicure->base_price,
            'status' => AppointmentStatus::Confirmed->value,
        ]);

        // A cancelled appointment, to confirm the availability engine
        // correctly ignores it and still offers this slot as free.
        Appointment::create([
            'client_id' => $leila->id,
            'staff_id' => $yasmine->id,
            'service_id' => $manicure->id,
            'start_time' => $tomorrow->copy()->setTime(9, 0),
            'end_time' => $tomorrow->copy()->setTime(9, 40),
            'total_price' => $manicure->base_price,
            'status' => AppointmentStatus::Cancelled->value,
        ]);
    }
}
