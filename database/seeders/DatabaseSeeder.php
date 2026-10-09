<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'phone' => null,
            'password' => 'testing-password',
            'role' => 'admin',
        ]);

        $aymen = User::factory()->create([
            'name' => 'aymen bouchlaghm',
            'email' => 'aymen@gmail.com',
            'phone' => null,
            'password' => 'password',
            'role' => 'staff',
        ]);

        $mohammed = User::factory()->create([
            'name' => 'bouras mohammed nadir',
            'email' => 'mohammed@gmail.com',
            'phone' => null,
            'password' => 'password',
            'role' => 'staff',
        ]);

        // day_of_week: 0 = Sunday ... 6 = Saturday
        $this->seedSchedule($aymen, [6, 0, 1, 2, 3, 4]);    // off Friday
        $this->seedSchedule($mohammed, [6, 0, 1, 3, 4, 5]); // off Tuesday
    }

    /**
     * Work 09:00-17:00 with a 12:00-13:00 break on each given day.
     * A break is stored as two rows: before it and after it.
     *
     * @param  array<int, int>  $days
     */
    private function seedSchedule(User $staff, array $days): void
    {
        foreach ($days as $day) {
            $staff->staffSchedules()->create([
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '12:00',
            ]);

            $staff->staffSchedules()->create([
                'day_of_week' => $day,
                'start_time' => '13:00',
                'end_time' => '17:00',
            ]);
        }
    }
}
