<?php

namespace App\Http\Controllers\Booking;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(): View
    {
        return view('booking.start');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => preg_replace('/\s+/', '', (string) $request->input('phone')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^0[567][0-9]{8}$/'],
        ]);

        $client = User::firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => $validated['name']],
        );

        session(['booking.client_id' => $client->id]);

        return redirect()->route('booking.service');
    }

    public function selectService(): View|RedirectResponse
    {
        if (! session()->has('booking.client_id')) {
            return redirect()->route('booking.start');
        }

        $services = Service::with('addons')->get();

        return view('booking.service', ['services' => $services]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        if (! session()->has('booking.client_id')) {
            return redirect()->route('booking.start');
        }

        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'addon_ids' => ['array'],
            'addon_ids.*' => [
                Rule::exists('service_addons', 'id')
                    ->where('service_id', $request->input('service_id')),
            ],
        ]);

        session([
            'booking.service_id' => $validated['service_id'],
            'booking.addon_ids' => $validated['addon_ids'] ?? [],
        ]);

        return redirect()->route('booking.staff');
    }

    public function selectStaff(): View|RedirectResponse
    {
        if (! session()->has('booking.service_id')) {
            return redirect()->route('booking.service');
        }

        $staff = User::where('role', 'staff')->get();

        return view('booking.staff', ['staff' => $staff]);
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        if (! session()->has('booking.service_id')) {
            return redirect()->route('booking.service');
        }

        $validated = $request->validate([
            'staff_id' => ['required', Rule::exists('users', 'id')->where('role', 'staff')],
        ]);

        session(['booking.staff_id' => $validated['staff_id']]);

        return redirect()->route('booking.slots');
    }

    public function selectSlot(Request $request): View|RedirectResponse
    {
        if (! session()->has('booking.staff_id')) {
            return redirect()->route('booking.staff');
        }

        $staff = User::findOrFail(session('booking.staff_id'));
        $service = Service::findOrFail(session('booking.service_id'));
        $addonIds = session('booking.addon_ids', []);
        $addons = ServiceAddon::whereIn('id', $addonIds)->get();

        $totalDuration = $service->duration_minutes + $addons->sum('extra_duration_minutes');
        $totalPrice = $service->base_price + $addons->sum('extra_price');

        $selectedDate = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $days = collect(range(0, 13))->map(
            fn($i) => Carbon::today()->addDays($i)
        );

        $slots = app(AvailabilityService::class)->slotsFor($staff, $selectedDate, $totalDuration);

        return view('booking.slots', [
            'staff' => $staff,
            'service' => $service,
            'addons' => $addons,
            'totalDuration' => $totalDuration,
            'totalPrice' => $totalPrice,
            'days' => $days,
            'selectedDate' => $selectedDate,
            'slots' => $slots,
        ]);
    }

    public function storeAppointment(Request $request): RedirectResponse
    {
        if (! session()->has('booking.staff_id')) {
            return redirect()->route('booking.staff');
        }

        $validated = $request->validate([
            'start_time' => ['required', 'date'],
        ]);

        $clientId = session('booking.client_id');
        $staffId = session('booking.staff_id');
        $serviceId = session('booking.service_id');
        $addonIds = session('booking.addon_ids', []);

        $service = Service::findOrFail($serviceId);
        $addons = ServiceAddon::whereIn('id', $addonIds)->get();

        $totalDuration = $service->duration_minutes + $addons->sum('extra_duration_minutes');
        $totalPrice = $service->base_price + $addons->sum('extra_price');

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($totalDuration);

        $appointment = DB::transaction(function () use (
            $clientId,
            $staffId,
            $serviceId,
            $startTime,
            $endTime,
            $totalPrice,
            $totalDuration
        ) {
            $staff = User::where('id', $staffId)->where('role', 'staff')->firstOrFail();

            // Re-run the availability engine, using the same rules the
            // client saw when browsing — never trust the submitted
            // start_time just because it matches the expected format.
            $validSlots = app(AvailabilityService::class)
                ->slotsFor($staff, $startTime->copy()->startOfDay(), $totalDuration);

            $isLegitimateSlot = collect($validSlots)->contains(
                fn($slot) => $slot->equalTo($startTime)
            );

            if (! $isLegitimateSlot) {
                return null;
            }

            // Lock any of this staff member's appointments that day, so no
            // other request can insert a conflicting one while we check.
            $conflictExists = Appointment::where('staff_id', $staffId)
                ->where('status', '!=', AppointmentStatus::Cancelled->value)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->lockForUpdate()
                ->exists();

            if ($conflictExists) {
                return null;
            }

            return Appointment::create([
                'client_id' => $clientId,
                'staff_id' => $staffId,
                'service_id' => $serviceId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_price' => $totalPrice,
                'status' => AppointmentStatus::Confirmed->value,
            ]);
        });

        if (! $appointment) {
            return redirect()->route('booking.slots')
                ->with('error', 'That slot is no longer available — please pick another.');
        }

        $appointment->addons()->attach($addonIds);

        // Client identity persists across bookings — only the
        // choices specific to this one booking get cleared.
        session()->forget([
            'booking.service_id',
            'booking.addon_ids',
            'booking.staff_id',
        ]);

        session(['booking.confirmed_id' => $appointment->id]);

        return redirect()->route('booking.confirmation');
    }

    public function confirmation(): View|RedirectResponse
    {
        if (! session()->has('booking.confirmed_id')) {
            return redirect()->route('booking.start');
        }

        $appointment = Appointment::with(['service', 'staff'])
            ->findOrFail(session()->pull('booking.confirmed_id'));

        return view('booking.confirmation', ['appointment' => $appointment]);
    }

    public function myAppointments(): View|RedirectResponse
    {
        if (! session()->has('booking.client_id')) {
            return redirect()->route('booking.start');
        }

        $appointments = Appointment::with(['service', 'staff'])
            ->where('client_id', session('booking.client_id'))
            ->where('status', AppointmentStatus::Confirmed->value)
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get();

        return view('booking.my-appointments', ['appointments' => $appointments]);
    }

    public function cancelAppointment(Appointment $appointment): RedirectResponse
    {
        if (! session()->has('booking.client_id')) {
            return redirect()->route('booking.start');
        }

        // Ownership check — a client can only cancel their own
        // appointment, never one referenced by guessing an ID.
        if ($appointment->client_id !== session('booking.client_id')) {
            abort(403);
        }

        $appointment->update(['status' => AppointmentStatus::Cancelled->value]);

        return redirect()->route('booking.my-appointments')
            ->with('status', 'Appointment cancelled.');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('booking.client_id');

        return redirect()->route('booking.start');
    }
}
