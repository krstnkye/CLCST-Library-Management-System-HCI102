# CLCST Library Management System

Laravel-based HCI102 project for the Central Luzon College of Science and Technology library. It provides separate student, faculty, librarian, administrator, and super administrator portals backed by a relational database, with role-based access control (RBAC) enforced on the server.

## What each portal does

| Portal | Main tasks |
| --- | --- |
| Student | Search the catalog, reserve books, renew loans online, see due dates, fines, borrowing history (CSV download), notifications, and send concerns to the library |
| Faculty | Everything a student can do with faculty loan limits, plus course reserves, reading lists, library class sessions, inter-library loans, academic requests, and book recommendations |
| Librarian | Check books out and in at the desk, serve reservations, track overdue loans, record fine payments or waivers, add and edit books and copies, manage inventory, answer requests, publish announcements, review accounts, view reports and the activity log |
| Administrator | Account approvals (including librarians), book records, transaction history, reports, user and book CSV import/export, activity log, plus the librarian workspace |
| Super administrator | Everything above, plus roles and permissions overview, editable borrowing rules and fine settings, system policies, security monitoring, and system status |

## Scanner, pop-ups and notifications

- **Barcode and QR scanner.** The scan buttons (catalog search, sidebar "Find by barcode or ISBN", librarian desk fields, inventory and book search) open the camera and read ISBN/EAN barcodes, copy barcodes, and QR codes. Without a camera you can scan a photo or type the code. Browsers only allow the camera on `localhost`, `127.0.0.1`, or HTTPS. The scanner library is bundled in `public/js/vendor`, so it works offline.
- **Library e-Pass.** Students and faculty can open a QR code of their school ID from the sidebar or the dashboard. Librarians scan it in the patron field when checking out a book.
- **Confirmations and results.** Important actions (reserve, renew, cancel, check in/out, record or waive a fine, approve or suspend an account) ask for confirmation in a pop-up, and every action shows a success or failure pop-up afterwards.
- **Notifications.** Patrons can open any notification to read it in full, mark it read or unread, and mark everything read or unread at once. Read marks are stored in the `notification_reads` table.

## Circulation rules

All borrowing rules live in `app/Services/CirculationService.php`, so every screen enforces the same policy:

- Loan limits and loan periods differ for students and faculty.
- A patron with an overdue book cannot borrow more until it is returned.
- Loans can be renewed online a limited number of times, but not when overdue or when other patrons are waiting.
- Shelf copies are held for patrons on the waiting list.
- Reference books cannot leave the library.
- Late returns create a fine automatically (per-day rate with a per-book cap). Only staff can record payments or waivers, and each one gets a receipt number.
- Uncollected pickup holds expire through the scheduled `library:expire-holds` command.

Default values are in `config/library.php`. Super administrators can change them from **Borrowing Rules** and **Fine Settings**; changes are stored in the `library_settings` table and logged.

## Database

The schema is defined in `database/migrations` and works on SQLite (local) and MySQL/MariaDB (deployment).

| Table | Purpose |
| --- | --- |
| `users` | Accounts with role, status (pending, active, suspended), school ID, and approval details |
| `library_books` | Catalog records (title, author, ISBN, call number, category, loan policy) |
| `book_copies` | Each physical copy with a unique barcode and status (available, on loan, reference, maintenance, lost) |
| `loans` | Who borrowed which copy, due date, return date, renewals, receipt number, issuing and receiving staff |
| `reservations` | Waiting-list entries and pickup holds |
| `fines` | Overdue charges with status (outstanding, paid, waived) and receipt numbers |
| `library_requests` | Faculty and patron requests, feedback, and staff replies |
| `announcements` | Notices shown in patron notifications |
| `library_settings` | Policy values changed by the super administrator |
| `audit_logs` | Sign-ins, account changes, data imports, and circulation actions |
| `notification_reads` | Which patron notifications each user has read |

Foreign keys protect history (a user or copy with loans cannot be deleted), unique constraints prevent duplicate ISBNs, barcodes, school IDs, emails, and receipt numbers, and indexes cover the common lookups (status, due date, patron and title).

## Security

- Only school-registered emails ending in `@clcst.edu.ph` can register, sign in, or be imported.
- Passwords are hashed with bcrypt, must be at least 10 characters with mixed case and a number, and are never exported.
- New accounts stay pending until someone allowed to manage that role reviews the uploaded ID. Administrator roles cannot be requested through public registration.
- Permissions come from the role saved in the database, checked on every request by the `portal` middleware and again in controllers. Suspended accounts are signed out on their next request.
- Sign-in attempts are rate limited, sessions are encrypted and regenerated at sign-in, and changing a password signs out other devices.
- All forms use CSRF tokens, Blade escapes output, queries use parameter binding, CSV exports neutralize spreadsheet formulas, and responses send security headers. Protected pages are not cached by the browser after logout.
- Uploaded ID documents are stored outside the public folder and can only be opened by authorized reviewers.

## Requirements

- PHP 8.3 or newer with the `pdo_sqlite` (or `pdo_mysql`) extension
- Composer
- Git

No Node.js build step is required. The UI uses Tailwind CSS from a CDN and Laravel Blade views.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8001
```

Open http://127.0.0.1:8001. To use MySQL instead, set `DB_CONNECTION=mysql` and the database credentials in `.env` before running the migrations.

To reset the demo data at any time:

```bash
php artisan migrate:fresh --seed
```

## Demo accounts

In a local or testing environment, the seeder creates one account per role, all with the password `LibraryDemo2026!`:

| Role | Email | School ID |
| --- | --- | --- |
| Student | `student@clcst.edu.ph` | `STU-2026-001` |
| Faculty | `faculty@clcst.edu.ph` | `FAC-2026-001` |
| Librarian | `librarian@clcst.edu.ph` | `LIB-2026-001` |
| Administrator | `admin@clcst.edu.ph` | `ADM-2026-001` |
| Super administrator | `superadmin@clcst.edu.ph` | `SYS-2026-001` |

It also adds a pending application (Ana Reyes) for Account Review, 12 catalog titles with individual copies, current and past loans with dates relative to today, fines in each state, reservations, faculty requests, and announcements. Change or remove the demo credentials before any public deployment.

## Bulk data

Authorized users can download CSV templates, import up to 2,000 records per file, and export book records. Book imports create individually tracked copies. Administrators and super administrators can also import and export user accounts within the roles they are allowed to manage. Imports run in a database transaction, so one invalid row rolls back the whole file.

## Testing

```bash
php artisan test
```

The suite covers access control, the circulation rules, security checks (email domain, password hashing, escaping, CSRF tokens, headers), and loads every page of every portal against the demo data.

## Project structure

- `app/Http/Controllers`: `AuthController` (sign-in and registration), `PatronController` (student and faculty), `LibrarianController`, `AdminController` (administrator and super administrator), `AccountManagementController`, `BulkDataController`, `ProfileController`
- `app/Services`: `CirculationService` (borrowing rules) and `LibrarySettings` (policy values)
- `app/Support`: patron notifications and report figures
- `resources/views`: one folder per portal (`student`, `staff` for faculty, `librarian`, `admin`, `superadmin`) plus shared `layouts` and `partials`

## Team workflow

```bash
git status
git add .
git commit -m "Describe the update"
git push
```

The `.env` file, `vendor` folder, cache files, and local build/runtime files are ignored and should not be committed.
