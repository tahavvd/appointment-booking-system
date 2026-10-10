<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin account
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'admin',
                'phone' => null,
                'password' => 'testing-password',
                'role' => 'admin',
            ]
        );

        // Staff accounts
        $aymen = User::updateOrCreate(
            ['email' => 'aymen@gmail.com'],
            [
                'name' => 'aymen bouchlaghm',
                'phone' => null,
                'password' => 'password',
                'role' => 'staff',
            ]
        );

        $mohammed = User::updateOrCreate(
            ['email' => 'mohammed@gmail.com'],
            [
                'name' => 'bouras mohammed nadir',
                'phone' => null,
                'password' => 'password',
                'role' => 'staff',
            ]
        );

        // Rebuild schedules so repeated seeding doesn't duplicate rows.
        $aymen->staffSchedules()->delete();
        $mohammed->staffSchedules()->delete();

        // 0 = Sunday ... 6 = Saturday
        $this->seedSchedule($aymen, [6, 0, 1, 2, 3, 4]);    // Off Friday
        $this->seedSchedule($mohammed, [6, 0, 1, 3, 4, 5]); // Off Tuesday

        // Seed services and their photos.
        $this->seedServices();
        $this->call(DemoAppointmentSeeder::class);
    }

    private function seedServices(): void
    {
        $services = [
            [
                'name' => 'Buzz Cut',
                'description' => 'One even length all over with the clippers. Fast, fresh, and needs almost no styling.',
                'photo_file' => 'buzz-cut.jpg',
                'base_price' => 600,
                'duration_minutes' => 20,
                'addons' => [],
            ],
            [
                'name' => 'Classic Taper Cut',
                'description' => 'A clean, timeless cut with sides that get gradually shorter toward the neckline, and a neat finish.',
                'photo_file' => 'classic-taper.jpg',
                'base_price' => 1000,
                'duration_minutes' => 30,
                'addons' => [
                    [
                        'name' => 'Beard Trim',
                        'extra_price' => 300,
                        'extra_duration_minutes' => 10,
                    ],
                    [
                        'name' => 'Hot Towel Finish',
                        'extra_price' => 200,
                        'extra_duration_minutes' => 5,
                    ],
                ],
            ],
            [
                'name' => 'Skin Fade',
                'description' => 'The sides are blended down to the skin with clippers and a razor finish, leaving a sharp, clean look.',
                'photo_file' => 'skin-fade.jpg',
                'base_price' => 1400,
                'duration_minutes' => 45,
                'addons' => [
                    [
                        'name' => 'Beard Shape-up',
                        'extra_price' => 200,
                        'extra_duration_minutes' => 10,
                    ],
                    [
                        'name' => 'Eyebrow Clean-up',
                        'extra_price' => 100,
                        'extra_duration_minutes' => 5,
                    ],
                ],
            ],
        ];

        foreach ($services as $data) {
            $source = database_path(
                'seeders/assets/services/' . $data['photo_file']
            );

            if (! is_file($source)) {
                throw new RuntimeException(
                    "Missing service photo: {$source}"
                );
            }

            $photoPath = 'services/' . $data['photo_file'];

            // Copy the committed seed image into public storage.
            Storage::disk('public')->put(
                $photoPath,
                file_get_contents($source)
            );

            $service = Service::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'photo' => $photoPath,
                    'base_price' => $data['base_price'],
                    'duration_minutes' => $data['duration_minutes'],
                    'is_active' => true,
                ]
            );

            // Synchronize the service's demo add-ons.
            $addonNames = collect($data['addons'])
                ->pluck('name')
                ->all();

            $service->addons()
                ->whereNotIn('name', $addonNames)
                ->delete();

            foreach ($data['addons'] as $addon) {
                $service->addons()->updateOrCreate(
                    ['name' => $addon['name']],
                    [
                        'extra_price' => $addon['extra_price'],
                        'extra_duration_minutes' => $addon['extra_duration_minutes'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * Work 09:00-17:00 with a 12:00-13:00 break.
     * The break is represented by two schedule rows.
     *
     * @param array<int, int> $days
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
