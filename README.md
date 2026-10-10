# ✂️ Salon Booking & Management System

### A complete salon operations platform — from online booking to the owner's dashboard.

A Laravel-powered portfolio project for managing services, add-ons, staff, weekly schedules, appointments, and salon performance.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-Templates-F05340?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-UI-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-Interactions-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=black)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)

**Portfolio project · Responsive web application · Role-based workflows**

[![View source on GitHub](https://img.shields.io/badge/GitHub-View%20source-181717?style=for-the-badge&logo=github)](https://github.com/tahavvd/appointment-booking-system)

**Live demo:** Not deployed yet — follow the setup guide below to run the project locally.

## 🔑 Demo logins

| Role | Email | Password |
|---|---|---|
| Admin | `admin@test.com` | `testing-password` |
| Staff | `aymen@gmail.com` | `password` |
| Staff | `mohammed@gmail.com` | `password` |

These are public development credentials created by the database seeder. Do not use them in production.

---

## 📚 Table of contents

- [Overview](#-overview)
- [Explore the application](#-explore-the-application)
- [Engineering decisions](#-important-engineering-decisions)
- [Validation and access control](#️-validation-and-access-control)
- [Technology stack](#-technology-stack)
- [Project structure](#️-project-structure)
- [Requirements](#️-requirements)
- [Installation and setup](#-installation-and-setup)
- [Demo data and test accounts](#-demo-data-and-test-accounts)
- [Service photo handling](#️-service-photo-handling)
- [Main routes](#️-main-routes)
- [Data model](#-data-model)
- [Tests and useful commands](#-tests-and-useful-commands)
- [Troubleshooting](#-troubleshooting)
- [Production and security notes](#-production-and-security-notes)
- [Possible future improvements](#-possible-future-improvements)
- [License](#-license)

## ✨ Overview

This project is a web-based booking and management system designed around the day-to-day needs of a small barbershop or hair salon. Clients can book appointments through a browser, while the owner and staff use dedicated workspaces to manage salon operations.

The project demonstrates practical full-stack development with Laravel, including relational data modelling, server-side business rules, appointment availability, transactional writes, role-based access, reusable Blade components, and responsive interfaces.

The interface uses a dark teal/cyan visual identity for the client booking experience and a lighter, information-focused workspace for salon administration.

### 🖼️ Screenshots

Recommended screenshots to add once captured from the running application:

1. Client booking flow and service selection.
2. Admin dashboard with appointments and revenue.
3. Staff dashboard and appointment history.

Save the screenshots in a folder such as `docs/screenshots/`, then embed them here using relative Markdown paths. Real application screenshots are not included because no screenshot files were supplied for this update.

## 🎯 The problem it solves

Managing appointments through a notebook or messages can lead to overlapping bookings, unused time, inconsistent service prices, and confusion about staff availability.

The application brings those workflows together:

- 📱 **Browser-based booking** without requiring a dedicated mobile app.
- 🗓️ **Staff-specific weekly schedules** instead of one shared working calendar.
- ⏱️ **Duration-aware availability** that accounts for the selected service and add-ons.
- 💈 **Configurable services** with photos, descriptions, prices, durations, and optional extras.
- 👥 **Separate admin and staff permissions** with distinct interfaces.
- 📋 **Appointment lifecycle tracking** for confirmed, completed, cancelled, and no-show appointments.
- 📊 **Operational overview** including daily appointments, active staff, active services, and weekly revenue figures.

## 🧭 Explore the application

### 💈 Client booking experience

The public booking flow starts at `/book`. The booking pages guide clients through the choices needed to make an appointment.

1. Identify the client using the details requested by the booking form.
2. Choose a service and review its description, price, duration, and available add-ons.
3. Choose a staff member.
4. Select an available time based on the staff member's schedule, existing appointments, and total service duration.
5. Review and confirm the appointment.
6. Review or cancel appointments from the client appointment area, subject to the application's rules.

The wizard uses session state between steps. If a visitor opens a later step without the required earlier selections, the controller can redirect them to the appropriate step rather than trusting incomplete browser-submitted data.

### 🧑‍💼 Admin / owner workspace

The admin area is protected by authentication and the `admin` role.

| Area | What it supports |
|---|---|
| **Dashboard** | Today's appointment overview, active staff and service counts, and a weekly revenue summary. |
| **Appointments** | Review appointments by date and update their status. |
| **Services** | Create and edit services, upload service photos, configure prices and durations, manage add-ons, and activate or deactivate services. |
| **Staff** | Create and update staff accounts and activate or deactivate staff members. |
| **Schedules** | Configure each staff member's recurring weekly working hours. Multiple schedule windows can represent a break during the workday. |

Deactivating a service or staff member is different from deleting historical appointment records. Existing bookings need to remain understandable even when an option is no longer offered for new bookings.

### 💇 Staff workspace

The staff area is protected by authentication and the `staff` role.

Staff members can:

- Open a dashboard showing their appointments for a selected day.
- Review appointment details, including the client, service, and selected add-ons.
- Mark eligible appointments as **completed**, **no-show**, or **cancelled**.
- Browse appointment history and filter it by outcome.
- Review monthly completion, no-show, and completed-service value summaries.
- Update their account password.
- View their own working schedule.

A staff member can only update appointments assigned to their own account. The application also prevents an appointment that has already left the confirmed state from being marked again through the same staff action.

## 🧠 Important engineering decisions

### 1. Server-side availability calculation

`app/Services/AvailabilityService.php` calculates possible appointment start times.

At a high level, it:

1. Loads the staff member's schedule windows for the requested day.
2. Loads that staff member's non-cancelled appointments for the day.
3. Subtracts busy appointment ranges from schedule windows to identify free gaps.
4. Generates start times at **30-minute intervals** only when the full requested duration fits inside a free gap.
5. Excludes start times that are already in the past.

A day can contain multiple working windows, such as a morning shift and an afternoon shift separated by lunch.

The duration used for booking includes selected add-ons. A slot is not valid merely because its start time is free: the complete service must fit before the working window ends and before another appointment begins.

### 2. Availability is checked again when a booking is submitted

A slot displayed in the browser can become unavailable before the client confirms it. The final booking action therefore must not trust the previously displayed slot as proof that it is still available.

The booking controller revalidates the submitted selection against the availability rules and performs the conflict check and appointment creation inside a database transaction with row locking. This is intended to reduce the risk of overlapping bookings for the same staff member.

**Database note:** transaction and row-lock behaviour depends on the database engine. Use a transactional database such as MySQL/InnoDB for realistic concurrency testing. SQLite is convenient for lightweight local development but does not reproduce every production locking behaviour.

### 3. Prices and durations come from database records

The browser submits selections, not authoritative prices. The application derives the base price, add-on prices, and total duration from the selected service and its associated add-ons on the server.

It also validates that selected add-ons belong to the service being booked. This avoids relying on client-side totals or accepting an unrelated add-on ID simply because that ID exists.

### 4. Role-based access is enforced on routes

The custom middleware in `app/Http/Middleware/EnsureUserHasRole.php` is registered as the `role` middleware alias in `bootstrap/app.php`.

- Admin routes use authentication plus `role:admin`.
- Staff routes use authentication plus `role:staff`.
- Controllers apply additional ownership checks where a staff member is changing an individual appointment.

The interface is not the security boundary: the server checks permissions when protected routes are requested.

### 5. Service updates keep related data in sync

`app/Services/ServiceManager.php` centralizes saving and deleting services, handling uploaded photos, and synchronizing add-ons.

The service and its add-on changes are wrapped in a database transaction. When a new photo is uploaded, the old local photo is removed after the database update succeeds; if the database operation fails, the newly uploaded file is cleaned up. Removed add-ons already referenced by appointments are deactivated rather than physically deleted, preserving historical context.

### 6. Forms use server-side validation

The admin service form uses `app/Http/Requests/Admin/ServiceRequest.php`. Among other rules, it validates service names, prices, durations, image type/size/dimensions, and the structure of add-on rows.

The interface can improve the editing experience, but the request rules remain authoritative.

## 🛡️ Validation and access control

The codebase includes several layers of protection for normal application workflows:

- Authenticated, role-restricted admin and staff route groups.
- Server-side validation of incoming form data.
- Staff ownership checks before staff members can update appointments.
- Status-transition checks for staff appointment actions.
- Context-aware validation of service, staff, and add-on selections.
- Server-side recalculation of booking totals and duration.
- Availability revalidation at the final booking step.
- Database transactions around important multi-record updates.
- File validation for uploaded service photos.
- Laravel's CSRF protection for state-changing web forms.

These measures are useful foundations, not a claim that the application has undergone an independent penetration test. Review environment configuration, database behaviour, deployment permissions, and integrations before using the application with real customers.

## 🧰 Technology stack

| Technology | Role in the project |
|---|---|
| **PHP 8.3+** | Application language. |
| **Laravel 13** | Routing, controllers, middleware, validation, Eloquent ORM, migrations, sessions, and service container. |
| **Blade** | Server-rendered pages and reusable view components. |
| **Tailwind CSS** | Responsive styling and interface layout. |
| **Alpine.js** | Lightweight browser interactions, including dynamic form rows and UI state. |
| **Vite** | Frontend asset development and production builds. |
| **MySQL / SQLite** | Relational database options supported by the Laravel configuration. MySQL is recommended for concurrency testing. |
| **Laravel Sail / Docker Compose** | Container-based local development. |
| **PHPUnit** | Test runner configured for the project. |

No separate SPA framework is required: application pages are rendered with Blade, while Alpine.js provides small interactive behaviours.

## 🏗️ Project structure

The following are the main places to look when exploring or extending the codebase:

```text
app/
├── Enums/
│   └── AppointmentStatus.php       # Appointment lifecycle states
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                  # Owner/admin workflows
│   │   ├── Auth/                   # Authentication
│   │   ├── Booking/                # Client booking wizard
│   │   └── Staff/                  # Staff dashboard and history
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php   # Role-based route access
│   └── Requests/
│       └── Admin/                  # Validated admin requests
├── Models/                         # Eloquent models and relationships
├── Services/
│   ├── AvailabilityService.php     # Bookable time calculation
│   └── ServiceManager.php          # Service/photo/add-on persistence
└── View/Components/                # Layout components

database/
├── factories/
├── migrations/                     # Database schema
└── seeders/
    ├── assets/services/             # Source photos for demo services
    ├── DatabaseSeeder.php
    └── DemoAppointmentSeeder.php

resources/
├── css/
├── js/
└── views/
    ├── admin/                       # Owner workspace
    ├── auth/                        # Login
    ├── booking/                     # Client booking pages
    ├── components/                  # Shared UI components
    ├── layouts/                     # Client, admin, staff, guest layouts
    └── staff/                       # Staff workspace

routes/
├── web.php                          # Public booking flow
├── auth.php                         # Authentication routes
├── admin.php                        # Admin route group
└── staff.php                        # Staff route group

tests/
├── Feature/
└── Unit/
```

## ⚙️ Requirements

### Docker-based setup

- Docker Desktop or Docker Engine with Docker Compose.
- Git.
- The repository's Laravel Sail configuration.

### Local PHP setup

- PHP **8.3 or newer**.
- Composer.
- Node.js and npm.
- A database supported by the project's Laravel configuration.
- PHP extensions required by Laravel and the installed Composer dependencies.

---

## 🚀 Installation and setup

The commands below assume you are starting from a fresh clone.

### Option A — Docker / Laravel Sail

This is the recommended route if you want a consistent environment without installing the project's PHP dependencies directly on your host.

#### 1. Clone the repository

```bash
git clone https://github.com/tahavvd/appointment-booking-system.git
cd appointment-booking-system
```

#### 2. Create the environment file

**Linux / macOS / WSL / Git Bash:**

```bash
cp .env.example .env
```

**PowerShell:**

```powershell
Copy-Item .env.example .env
```

#### 3. Install Composer dependencies

If Composer and the required PHP version are available locally:

```bash
composer install
```

If you prefer to install dependencies through the Sail Composer image, use the project's documented Sail/Composer workflow and ensure the generated `vendor/` directory belongs to your working tree.

#### 4. Start the containers

```bash
./vendor/bin/sail up -d
```

On Windows PowerShell, invoke the Sail script through the appropriate shell or use WSL. Once Sail is available, the shorter `sail` command can be used if you have configured its shell alias.

#### 5. Generate the application key

```bash
./vendor/bin/sail artisan key:generate
```

#### 6. Configure the database

Open `.env` and check the database values against the services in `compose.yaml`. For the default Sail MySQL service, use the database host/service name and credentials defined by that Compose configuration. Do not assume `127.0.0.1` is the correct database host from inside a container.

#### 7. Run migrations and seed demo data

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

> **Warning:** `migrate:fresh` drops all existing tables before rebuilding them. Use it only for a disposable development database, never for a database containing data you need to keep.

The seeder expects the service photos to exist under `database/seeders/assets/services/`. Keep those source assets in place.

#### 8. Create the public storage link

Run this through Sail so the symlink is created in the application container:

```bash
./vendor/bin/sail artisan storage:link
```

#### 9. Install frontend dependencies and run Vite

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Keep the Vite process running while developing. For a compiled frontend build instead, run:

```bash
./vendor/bin/sail npm run build
```

#### 10. Open the application

- Client booking: `http://localhost/book`
- Admin login: `http://localhost/login`
- Admin workspace after login: `http://localhost/admin`
- Staff workspace after login: `http://localhost/staff`

If your local Compose configuration maps the web service to a different port, use the port shown by Docker Compose.

### Option B — Local PHP environment

Use this option if PHP, Composer, Node.js, and a database are already installed on your machine.

#### 1. Clone and install dependencies

```bash
git clone https://github.com/tahavvd/appointment-booking-system.git
cd appointment-booking-system
composer install
npm install
```

Create `.env` from `.env.example` (use `cp` on Linux/macOS/WSL or `Copy-Item` in PowerShell).

#### 2. Configure `.env`

Set `APP_NAME`, `APP_URL`, the database connection, and other environment-specific settings. For SQLite, create the database file if it does not exist and configure `DB_CONNECTION=sqlite` and the correct `DB_DATABASE` path. For MySQL, configure the host, port, database name, username, and password.

Generate the application key:

```bash
php artisan key:generate
```

#### 3. Build the database and demo data

```bash
php artisan migrate:fresh --seed
```

Again, this resets the database. Use it only in development.

#### 4. Link public storage

Service photos are written to Laravel's public storage disk. Create the public storage symlink:

```bash
php artisan storage:link
```

#### 5. Start the application and asset server

Open two terminals in the project directory.

**Terminal 1 — Laravel:**

```bash
php artisan serve
```

**Terminal 2 — Vite:**

```bash
npm run dev
```

Visit `http://127.0.0.1:8000/book`.

For a production-style frontend build, run `npm run build` and configure your web server to serve the Laravel `public/` directory.

---

## 🧪 Demo data and test accounts

`database/seeders/DatabaseSeeder.php` creates the demo admin and staff users, schedules, services, and service add-ons. It also calls `DemoAppointmentSeeder.php` to add appointment examples.

The demo login credentials are listed at the top of this README for quick access. They are public development credentials and must not be reused in a real deployment.

### Seeded services

| Service | Base price | Base duration | Add-ons |
|---|---:|---:|---|
| **Buzz Cut** | 600 DA | 20 min | None |
| **Classic Taper Cut** | 1,000 DA | 30 min | Beard Trim (+300 DA, +10 min); Hot Towel Finish (+200 DA, +5 min) |
| **Skin Fade** | 1,400 DA | 45 min | Beard Shape-up (+200 DA, +10 min); Eyebrow Clean-up (+100 DA, +5 min) |

Prices and durations are sample data defined by the seeder, not fixed application-wide values. The admin can manage service details through the application.

### Seeded schedules

The seeder creates weekly schedule rows for the two staff members. Each working day is split into **09:00–12:00** and **13:00–17:00**, representing a lunch break from noon to 13:00.

The schedule days differ by staff member, so each person has their own weekly availability. Check `DatabaseSeeder.php` if you want to change the demo schedule.

### Demo clients and appointments

`DemoAppointmentSeeder.php` creates **20 demo client accounts** with phone numbers from `0711111101` through `0711111120`. It generates approximately **1–5 appointments per day** across the next **100 days**, plus **14 days of past appointments** so the dashboard can display historical activity and revenue.

The exact number of successfully created appointments can vary depending on the seeded staff schedules, services, and available slots.

To explore the client appointment area, enter one of the seeded phone numbers in the booking flow at `/book`.

> **Important privacy limitation:** Clients are identified by phone number only, without phone ownership verification. Anyone who knows or guesses a seeded phone number may be able to view or cancel that client's bookings. This is a documented limitation for the portfolio demo, not a suitable access-control design for real customer data.

---

## 🖼️ Service photo handling

There are two separate locations with different purposes:

| Location | Purpose |
|---|---|
| `database/seeders/assets/services/` | **Source images committed with the project.** The seeder reads the files from here. |
| `storage/app/public/services/` | **Runtime copies** used by the application's public storage disk. |
| `public/storage/` | The public symlink created by `php artisan storage:link`, which exposes files stored on the public disk. |

The seed images expected by the current seeder are:

```text
database/seeders/assets/services/
├── buzz-cut.jpg
├── classic-taper.jpg
└── skin-fade.jpg
```

When seeding, the application copies these source images into `storage/app/public/services/`. If a required source image is missing, the seeder throws a `RuntimeException` rather than silently creating a service with a missing photo.

For a missing-photo error:

1. Confirm that the filename matches exactly, including the `.jpg` extension.
2. Confirm the file is inside `database/seeders/assets/services/` (not only in `storage/`).
3. Run `php artisan storage:link` if the files exist but are not publicly reachable.
4. Rerun the seeder after restoring the missing source image.

When an admin uploads a new service photo, the application stores it on the public disk under the `services` directory. The old local image is cleaned up after a successful update.

---

## 🗺️ Main routes

The route files are separated by responsibility.

| URL / route area | Purpose | Access |
|---|---|---|
| `/` | Redirects to the booking start page | Public |
| `/book` | Start the booking flow | Public entry point |
| `/book/service` | Select a service and add-ons | Booking flow |
| `/book/staff` | Select a staff member | Booking flow |
| `/book/slots` | Choose an available slot | Booking flow |
| `/book/confirm` | Submit the final booking | Booking flow |
| `/book/confirmation` | View booking confirmation | Booking flow |
| `/my-appointments` | View the client's appointment area | Booking/client session rules |
| `/login` | Login page | Guest / authenticated redirect behaviour |
| `/admin` | Admin dashboard | Authenticated admin |
| `/admin/appointments` | Manage appointments | Authenticated admin |
| `/admin/services` | Manage services and add-ons | Authenticated admin |
| `/admin/staff` | Manage staff accounts | Authenticated admin |
| `/admin/schedules` | Manage weekly staff schedules | Authenticated admin |
| `/staff` | Staff dashboard | Authenticated staff |
| `/staff/history` | Staff appointment history | Authenticated staff |
| `/staff/account` | Staff account settings | Authenticated staff |

Route definitions are the source of truth. To inspect exact route names, HTTP methods, and middleware in your local environment, run:

```bash
php artisan route:list
```

When running through Sail, prefix the command with `./vendor/bin/sail`.

---

## 🧱 Data model

The application's central records are connected through Eloquent relationships.

- **`User`** — stores client, staff, and admin accounts, with a `role` and activation state.
- **`Service`** — a bookable service with its description, photo path, base price, duration, and activation state.
- **`ServiceAddon`** — an optional extra belonging to a service, with an additional price and duration.
- **`StaffSchedule`** — a recurring weekly working window for a staff member.
- **`Appointment`** — a booking linking a client, staff member, and service, with start/end times, total price, and status.
- **`appointment_addon`** — pivot records connecting appointments to selected add-ons.

### Appointment statuses

The enum `App\Enums\AppointmentStatus` defines the following values:

- `confirmed` — the booking is confirmed.
- `completed` — the appointment was completed.
- `cancelled` — the appointment was cancelled.
- `no_show` — the client did not attend.

The schema also includes a unique staff/start-time constraint migration. Application-level availability validation and database constraints serve different purposes: the former provides business-rule checks and helpful feedback; the latter adds a final integrity guard for the specific uniqueness rule it represents.

---

## 🧪 Tests and useful commands

Run the automated test suite:

```bash
php artisan test
```

Or with Sail:

```bash
./vendor/bin/sail artisan test
```

The repository includes Laravel example tests and authentication feature tests. Run the suite in your environment before relying on any behaviour described in this README.

### Useful development commands

| Task | Command |
|---|---|
| List routes | `php artisan route:list` |
| Run pending migrations | `php artisan migrate` |
| Rebuild local DB and seed demo data | `php artisan migrate:fresh --seed` |
| Rerun seeders without dropping tables | `php artisan db:seed` |
| Create public storage symlink | `php artisan storage:link` |
| Clear cached configuration | `php artisan config:clear` |
| Build frontend assets | `npm run build` |
| Run frontend dev server | `npm run dev` |
| Format PHP with Laravel Pint | `vendor/bin/pint` |

Use the Sail prefix for Artisan, npm, and other commands that need to run inside the configured containers.

---

## 🔧 Troubleshooting

### Seeder fails with “Missing service photo”

The seeder reads its source images from `database/seeders/assets/services/`. Restore the required image there. A copy under `storage/app/public` does not replace the seeder source asset.

### Service photos do not display in the browser

Run `php artisan storage:link`, check that the image exists under `storage/app/public/services/`, and confirm that `APP_URL` matches the address used to open the application.

### Database connection fails in Docker

Inside Docker, the database host is usually the Compose service name, not `127.0.0.1`. Compare your `.env` database settings with `compose.yaml`, then restart the containers if necessary.

### Frontend changes are not appearing

Ensure `npm run dev` is still running, or run `npm run build` and refresh the page. If the Vite server is running in a container, make sure its port is exposed as expected.

### Login does not work with a demo account

Rerun `php artisan db:seed` in a disposable development database and verify the current credentials in `database/seeders/DatabaseSeeder.php`. If you changed the seeder, the credentials in this README may need updating too.

### There are no available time slots

Check that the chosen staff member has schedule rows for the selected weekday, that the service plus add-ons fits within a working window, that the selected date/time is in the future, and that existing non-cancelled appointments do not occupy the relevant interval.

---

## 🔐 Production and security notes

This repository is intended to be run and explored as a portfolio project. One important limitation is that client access is based on a phone number without ownership verification. A person who knows or guesses a client's phone number may be able to view or cancel that client's bookings.

Do not expose this demo to real customer data until client identity and cancellation authorization are strengthened.

Before deploying it for real customers:

- Add a secure client verification flow, such as a verified one-time code, and enforce authorization before showing or cancelling appointments.
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Generate and protect a unique `APP_KEY`; never commit `.env`.
- Replace demo passwords and remove or disable demo accounts.
- Configure production database credentials and verify transaction/locking behaviour on the actual database engine.
- Serve the application over HTTPS.
- Ensure `storage/` and `bootstrap/cache/` have appropriate permissions.
- Review authentication, session, rate-limiting, logging, backup, and recovery settings.
- Configure a real mail/SMS provider only if those features are implemented and credentials are available.
- Run automated tests and conduct a separate security review before handling real customer data.

> **Caution:** Never use the seeded demo accounts or development environment settings on a publicly accessible production deployment.

## 💡 Possible future improvements

Potential extensions for a later iteration include:

- Automated SMS or email appointment reminders through a configured provider.
- One-off schedule exceptions for holidays, leave, and temporary closures.
- More extensive automated tests for booking conflicts, schedule edge cases, role access, and service/add-on updates.
- Audit logs for administrative changes and appointment status transitions.
- Deployment automation and production monitoring.

These are ideas for future work, not claims that the integrations are already present.

## 📄 License

This project is released under the MIT License. See the [`LICENSE`](LICENSE) file. Confirm that you have the rights to redistribute any third-party assets included in the repository.

---

**Built with Laravel · Designed around real salon workflows · Documented for developers**
