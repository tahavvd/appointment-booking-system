<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Services\ServiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private ServiceManager $services) {}

    public function index(): View
    {
        $services = Service::query()
            ->with(['addons' => fn($q) => $q->where('is_active', true)->orderBy('name')])
            ->withCount([
                'appointments',
                'appointments as bookings_count' => fn($q) => $q
                    ->where('status', '!=', AppointmentStatus::Cancelled->value),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', ['services' => $services]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'service' => new Service(['is_active' => true]),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = $this->services->save(
            null,
            $this->attributes($request),
            $request->file('photo'),
            $request->input('addons', []),
        );

        return redirect()->route('admin.services.index')
            ->with('status', "{$service->name} was added.");
    }

    public function edit(Service $service): View
    {
        $service->load(['addons' => fn($q) => $q->where('is_active', true)->orderBy('id')]);

        return view('admin.services.edit', ['service' => $service]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->services->save(
            $service,
            $this->attributes($request),
            $request->file('photo'),
            $request->input('addons', []),
        );

        return redirect()->route('admin.services.index')
            ->with('status', "{$service->name} was updated.");
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return redirect()->route('admin.services.index')
            ->with('status', $service->is_active
                ? "{$service->name} is visible to clients again."
                : "{$service->name} is now hidden from booking.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        // Deleting cascades to appointments, so refuse if there is any history.
        if ($service->appointments()->exists()) {
            return redirect()->route('admin.services.index')
                ->with('error', "{$service->name} has appointments, so it can't be deleted. Hide it instead.");
        }

        $name = $service->name;
        $this->services->delete($service);

        return redirect()->route('admin.services.index')
            ->with('status', "$name was deleted.");
    }

    private function attributes(ServiceRequest $request): array
    {
        return $request->safe()->only(['name', 'description', 'base_price', 'duration_minutes'])
            + ['is_active' => $request->boolean('is_active')];
    }
}
