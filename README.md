# Salon Booking System

A real-time appointment booking system built for a small hair & beauty salon, replacing a paper notebook that was causing double-bookings and scheduling chaos. Built with **Laravel 13**, **Blade**, **Tailwind CSS**, and **Alpine.js**.

> This is a portfolio project. It simulates a real freelance brief (translated below) and was built end-to-end — schema design, business logic, and UI — as a demonstration of practical backend and frontend engineering, not a tutorial clone.

## The brief

The (fictional) client, a salon owner in Algeria, described this problem:

> Clients call me all day to book appointments, and I manage everything on a paper notebook. As a result, I make mistakes often — sometimes I put two clients at the same time, or I have an empty slot while a stylist is available. It's become unmanageable.

Requirements that came out of that conversation:

- Clients should be able to book themselves from their phone — **no app download**, since older clients may not know how to install one
- Real-time slot availability, so no more double-booked appointments
- Each stylist should have their **own independent schedule**, not one shared calendar
- Services (haircut, coloring, etc.) need configurable price and duration
- Track completed appointments vs. no-shows
- Design should feel upscale, matching a high-end salon — not a generic form

## What's implemented

This repository covers the **complete client-facing booking flow**, end to end:

1. **Client identification** — name + phone, no account or password required. Returning clients are recognized automatically by phone number (`firstOrCreate`).
2. **Service selection** — browsable service cards with photo, description, price, and duration, plus optional add-ons per service.
3. **Stylist selection** — pick a specific staff member (not auto-assigned).
4. **Real-time availability** — browse a 14-day window, see only the time slots that are actually bookable for that stylist and service duration.
5. **Booking confirmation** — a summary (service, add-ons, stylist, time, total price) before committing.
6. **Race-condition-safe booking** — the appointment is only created after re-validating availability inside a locked database transaction (see below).
7. **Manage upcoming appointments** — clients can view and cancel their own booking without creating an account.

**Not yet built** (deliberately out of scope for this phase): the owner/staff admin dashboard, SMS/email reminders, and multi-day recurring schedule exceptions (e.g. a stylist taking a single day off outside their normal pattern).

## Architecture highlights

### The availability engine

The core problem: given a stylist, a date, and a service duration, compute every legitimate time this appointment could start — accounting for working hours _and_ existing bookings.

`app/Services/AvailabilityService.php` solves this in three steps rather than checking every minute of the day one at a time:

1. **Resolve the working window** for that day from the stylist's recurring weekly schedule (`StaffSchedule`).
2. **Invert existing appointments into free gaps** — a single forward sweep through the day's bookings, turning "busy ranges" into "free ranges" between them. Back-to-back appointments correctly produce zero gap between them (a common off-by-one mistake this avoids).
3. **Generate bookable slots within each gap**, at a fixed interval, only where the _entire_ service duration (including add-ons) still fits before the gap closes — a 90-minute coloring service can't start 20 minutes before closing time, even though that moment is technically "free."

### Preventing double-bookings under concurrency

Showing an available slot and actually booking it are two different moments in time — two clients can view the same free slot simultaneously. The final booking step (`BookingController::storeAppointment`) handles this deliberately:

- The chosen time is **re-validated against the availability engine itself** server-side — the client's browser is never trusted to submit a legitimate slot just because it matches the expected format.
- The conflict check and the appointment creation both happen inside a **single database transaction using `lockForUpdate()`**, so a second concurrent request checking the same stylist's schedule is forced to wait until the first request's transaction fully resolves — preventing both from seeing a stale "still free" result and creating overlapping appointments.

### Security: validating relationships, not just existence

A few validation rules go beyond "does this ID exist" to "does this ID make sense _in this context_" — e.g. confirming a submitted `staff_id` actually belongs to a user with the `staff` role, and that a chosen add-on actually belongs to the service being booked, not just any service in the database. IDs referencing _which row_ are trusted from the client; anything affecting price, duration, or business rules is always recomputed server-side.

### Session-based booking wizard

Rather than a single giant form, the flow is a multi-step session-backed wizard — each step validates that the required prior steps were completed (you can't reach the stylist picker without a selected service, etc.) and redirects to the earliest missing step rather than restarting the whole flow.

## Tech stack

- **Laravel 13** (PHP)
- **Blade** templates, no separate frontend framework/build for logic — kept deliberately simple where a full SPA wasn't warranted
- **Tailwind CSS**, mobile-first, with a distinct dark/cyan visual theme for client-facing pages vs. a lighter theme for staff/owner auth
- **Alpine.js** for lightweight interactivity (modals, panels) without a heavier JS framework
- **MySQL**

## Setup

```bash
git clone <this-repo>
cd <project-folder>
cp .env.example .env
composer install
./vendor/bin/sail up -d
sail artisan key:generate
sail artisan migrate:fresh --seed
sail npm install
sail npm run dev
```

Visit `http://localhost/book` to start a booking as a client.

Seeded test accounts (see `database/seeders/DatabaseSeeder.php`):

- Owner: `karim@salonyasmine.test` / `password`
- Staff: `yasmine@salonyasmine.test` / `password`, `sara@salonyasmine.test` / `password`

## Roadmap

- [ ] Owner/staff dashboard (calendar overview, per-stylist schedule view)
- [ ] Service/staff CRUD for the owner
- [ ] No-show tracking and reporting
- [ ] SMS/email appointment reminders
- [ ] Per-date schedule exceptions (holidays, sick days)
