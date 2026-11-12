# CLCST Library Management System

Laravel-based HCI102 project for the Central Luzon College of Science and Technology library. It provides separate student, faculty, librarian, administrator, and super administrator portals backed by a relational database, with role-based access control (RBAC) enforced on the server.

## What each portal does

| Portal | Main tasks |
| --- | --- |
| Student | Search the catalog, reserve books, renew loans online, see due dates, fines, borrowing history (CSV download), the borrowing policy, notifications, and send concerns to the library |
| Faculty | Everything a student can do (including Fines & Penalties and the Borrowing Policy) with faculty loan limits, plus course reserves, reading lists, library class sessions, inter-library loans, academic requests, and book recommendations |
| Librarian | Check books out and in at the desk, serve reservations, track overdue loans, record fine payments or waivers, add and edit books and copies, manage inventory, answer requests, publish announcements, review accounts, view reports and the activity log |
| Administrator | Account approvals (including librarians), book records, transaction history, reports, user and book CSV import/export, activity log, plus the librarian workspace |
| Super administrator | Everything above, plus roles and permissions overview, editable borrowing rules and fine settings, system policies, security monitoring, and system status |

## Scanner, pop-ups and notifications

- **Barcode and QR scanner.** The scan buttons (catalog search, sidebar "Find by barcode or ISBN", librarian desk fields, inventory and book search) open the camera and read ISBN/EAN barcodes, copy barcodes, and QR codes. Without a camera you can scan a photo or type the code. Browsers only allow the camera on `localhost`, `127.0.0.1`, or HTTPS. The scanner library is bundled in `public/js/vendor`, so it works offline.
- **Library e-Pass.** Students and faculty can open a QR code of their school ID from the sidebar or the dashboard. Librarians scan it in the patron field when checking out a book.
- **Confirmations and results.** Important actions (reserve, renew, cancel, check in/out, record or waive a fine, approve, suspend or reactivate an account, reset a password) ask for confirmation in a pop-up, and every action shows a success or failure pop-up afterwards.
- **Notifications.** Patrons can open any notification to read it in full, mark it read or unread, and mark everything read or unread at once. Read marks are stored in the `notification_reads` table.

## Sidebar and profile photos

- Every portal keeps one sidebar on all of its pages, including shared library pages opened by an administrator or the super administrator.
- On desktop, the menu button beside the logo collapses the sidebar to icons (remembered per browser). On phones the sidebar is hidden and the same button opens it as a slide-in menu. The sidebar keeps its scroll position between pages.
- Every user can upload a profile photo (JPG, PNG or WebP, up to 2 MB) from their profile page. It replaces their initials in the top bar. Photos are stored outside the public folder and only shown to signed-in users.

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

The full HCI 102 write-up, with screenshots of every portal, is in `documentation/CLCST-Library-Management-System-HCI-102-Documentation.docx`.

Foreign keys protect history (a user or copy with loans cannot be deleted), unique constraints prevent duplicate ISBNs, barcodes, school IDs, emails, and receipt numbers, and indexes cover the common lookups (status, due date, patron and title).

## Security

- Only school-registered emails ending in `@clcst.edu.ph` can register, sign in, or be imported.
- Passwords are hashed with bcrypt, must be at least 10 characters with mixed case and a number, and are never exported.
- New accounts stay pending until someone allowed to manage that role reviews the uploaded ID. Administrator roles cannot be requested through public registration.
- Forgotten passwords: there is no email reset. A librarian (students and faculty), administrator (also librarians) or super administrator (everyone) presses **Reset password** in Account Review. A temporary password is shown once, the old password stops working, and the account is signed out everywhere. The owner changes it from their profile after signing in.
- Suspended accounts (and rejected applications) can be turned back on with **Reactivate** by anyone allowed to manage that role.
- Permissions come from the role saved in the database, checked on every request by the `portal` middleware and again in controllers. Suspended accounts are signed out on their next request.
- Sessions end after 15 minutes without activity or when the browser closes (`SESSION_LIFETIME`, `SESSION_EXPIRE_ON_CLOSE`), unless the user ticks "Keep me signed in" on a private device.
- Sign-in attempts are rate limited, sessions are encrypted and regenerated at sign-in, and changing a password signs out other devices.
- All forms use CSRF tokens, Blade escapes output, queries use parameter binding, CSV exports neutralize spreadsheet formulas, and responses send security headers. Protected pages are not cached by the browser after logout.
- Uploaded ID documents are stored outside the public folder and can only be opened by authorized reviewers.

## JSON API with JWT

The website signs in with encrypted session cookies. For other clients (a mobile app, Postman, another system) there is a small JSON API under `/api/v1` that uses JSON Web Tokens (HS256, signed with a key derived from `APP_KEY`).

| Method | Endpoint | Who |
| --- | --- | --- |
| POST | `/api/v1/auth/login` with `role`, `identifier`, `password` | Anyone (same strict role check as the website) |
| GET | `/api/v1/auth/me` | Any signed-in role |
| POST | `/api/v1/auth/logout` | Any signed-in role (revokes the token) |
| GET | `/api/v1/books?q=` | Any signed-in role (students and faculty see active titles only) |
| GET | `/api/v1/my/loans` | Students and faculty |
| GET | `/api/v1/audit-logs` | Administrators and the super administrator |

Send the token as `Authorization: Bearer <access_token>`. Tokens expire after 60 minutes (`JWT_TTL_MINUTES`), are rejected if tampered with or signed with another algorithm, and stop working at once if the account is suspended or the token is revoked.

## Requirements

- PHP 8.3 or newer with the `pdo_sqlite` (or `pdo_mysql`) extension
- Composer
- Git

No Node.js build step is required to run the system. The pages use Laravel Blade views with a ready-built Tailwind stylesheet (`public/css/tailwind.css`) and fonts stored in `public/fonts`, so the system works with no internet connection.

### Working offline

Everything the pages need is inside the project: the styling, the fonts and icons, and the barcode scanner. On the computer running the system, and on any phone or laptop connected to the same Wi-Fi or router (through the computer's local address, for example `http://192.168.1.10:8021`), the system works without internet. Two things still need internet:

- The public Dev Tunnel link for people on other networks.
- Book cover pictures, which come from Open Library. Without internet the cover space shows the plain book card instead.

### Changing the styling

If you add new Tailwind classes to a view or script, rebuild the stylesheet once (needs Node.js and internet the first time):

```bash
npm install --no-save tailwindcss@3.4.17 @tailwindcss/forms@0.5 @tailwindcss/container-queries@0.1
npx tailwindcss -c tailwind.config.js -i resources/css/tailwind.css -o public/css/tailwind.css --minify
```

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
