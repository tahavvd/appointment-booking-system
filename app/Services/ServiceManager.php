<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceAddon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ServiceManager
{
    /**
     * Create or update a service, its photo and its add-ons in one go.
     *
     * @param  array<int, array<string, mixed>>  $addons
     */
    public function save(?Service $service, array $attributes, ?UploadedFile $photo, array $addons): Service
    {
        $newPath = $photo?->store('services', 'public');
        $oldPhoto = $service?->photo;

        try {
            $service = DB::transaction(function () use ($service, $attributes, $newPath, $addons) {
                if ($newPath) {
                    $attributes['photo'] = $newPath;
                }

                $service = $service
                    ? tap($service)->update($attributes)
                    : Service::create($attributes);

                $this->syncAddons($service, $addons);

                return $service;
            });
        } catch (Throwable $e) {
            // Don't leave an orphan file behind if the database part failed.
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        if ($newPath && $oldPhoto) {
            $this->deletePhotoFile($oldPhoto);
        }

        return $service;
    }

    public function delete(Service $service): void
    {
        $photo = $service->photo;

        DB::transaction(fn() => $service->delete());

        $this->deletePhotoFile($photo);
    }

    /**
     * Update rows that still exist, create new ones, and handle removed ones:
     * delete if never booked, archive if past appointments still use them.
     */
    private function syncAddons(Service $service, array $rows): void
    {
        $existing = $service->addons()->get()->keyBy('id');
        $keptIds = [];

        foreach ($rows as $row) {
            $values = [
                'name' => $row['name'],
                'extra_price' => $row['extra_price'],
                'extra_duration_minutes' => $row['extra_duration_minutes'],
                'is_active' => true,
            ];

            $id = isset($row['id']) ? (int) $row['id'] : null;

            if ($id && $existing->has($id)) {
                $existing[$id]->update($values);
                $keptIds[] = $id;
            } else {
                $keptIds[] = $service->addons()->create($values)->id;
            }
        }

        $service->addons()->whereNotIn('id', $keptIds)->get()->each(function (ServiceAddon $addon) {
            $used = DB::table('appointment_addon')->where('service_addon_id', $addon->id)->exists();

            $used ? $addon->update(['is_active' => false]) : $addon->delete();
        });
    }

    /** Seeded placeholders are external URLs, so only local files are removed. */
    private function deletePhotoFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
