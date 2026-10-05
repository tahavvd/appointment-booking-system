<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::where('role', 'staff')
            ->with('staffSchedules')
            ->withCount([
                'staffAppointments as today_count' => fn($q) => $q
                    ->whereDate('start_time', today())
                    ->where('status', '!=', AppointmentStatus::Cancelled->value),
                'staffAppointments as upcoming_count' => fn($q) => $q
                    ->where('start_time', '>=', now())
                    ->where('status', AppointmentStatus::Confirmed->value),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', ['staff' => $staff]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::create($data + ['role' => 'staff', 'is_active' => true]);

        return redirect()->route('admin.staff.index')
            ->with('status', "{$data['name']} was added to the team.");
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $data = $this->validated($request, $staff);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $staff->update($data);

        return redirect()->route('admin.staff.index')
            ->with('status', "{$staff->name} was updated.");
    }

    public function toggle(User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $staff->update(['is_active' => ! $staff->is_active]);

        return redirect()->route('admin.staff.index')
            ->with('status', $staff->is_active
                ? "{$staff->name} is active again."
                : "{$staff->name} was deactivated.");
    }

    private function validated(Request $request, ?User $staff = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->id)],
            'phone' => ['nullable', 'regex:/^0[567]\d{8}$/', Rule::unique('users', 'phone')->ignore($staff?->id)],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:8'],
        ], [
            'phone.regex' => 'Use an Algerian mobile number like 0550123456.',
        ]);
    }
}
