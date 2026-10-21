# CLCST Library Management System

Laravel-based HCI102 project for the CLCST Library Management System. It provides separate student, faculty, librarian, administrator, and super administrator portals with database-backed authentication and role-based access control (RBAC).

## Security and Access

- Passwords are hashed by Laravel and are never stored or exported as plain text.
- Account permissions come from the database, not from the role selected on the login form.
- New registrations remain pending until an authorized user verifies them.
- Login attempts are rate limited and important account/data actions are recorded.
- Protected responses disable browser caching after logout.
- Librarians may manage student and faculty access, administrators may also manage librarians, and super administrators control all roles.

## Requirements

- PHP 8.3 or newer
- Composer
- MySQL/MariaDB for deployment, or SQLite for local development
- Git

No Node.js build step is required. The UI uses CDN assets and Laravel Blade views.

## Fresh Setup

Clone the repository, then run:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Update `.env` with your local database credentials:

```env
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Start the local Laravel server:

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

Open:

```text
http://127.0.0.1:8001
```

## Useful Pages

- Login: `/`
- Registration: `/register`
- Student dashboard: `/student/dashboard`
- Book catalog: `/books/catalog`
- Borrow/Return: `/student/borrow-return`
- Reservations: `/student/reservations`
- Borrowing history: `/student/history`
- Fines and penalties: `/student/fines`
- Notifications: `/student/notifications`
- My profile: `/student/account`
- Help and support: `/student/support`
- Faculty dashboard: `/faculty/dashboard`
- Librarian dashboard: `/librarian/dashboard`
- Administrator dashboard: `/admin/dashboard`
- Super administrator dashboard: `/super-admin/dashboard`
- Account review: `/management/accounts`
- Bulk import and export: `/management/data`

## Local Demo Accounts

Running `php artisan migrate --seed` in a local or testing environment creates one account for each role. All demo accounts use the password `LibraryDemo2026!`:

- `student@clcst.edu.ph`
- `faculty@clcst.edu.ph`
- `librarian@clcst.edu.ph`
- `admin@clcst.edu.ph`
- `superadmin@clcst.edu.ph`

Change or remove demo credentials before any public deployment.

## Bulk Data

Authorized users can download CSV templates, import up to 2,000 records per file, and export book records. Administrators and super administrators can also import/export user accounts. Imports are validated and use a database transaction so an invalid row rolls back the whole file.

## Testing

Run the test suite before committing changes:

```bash
php artisan test
```

## Team Workflow

Use this pattern when updating the system:

```bash
git status
git add .
git commit -m "Describe the update"
git push
```

The `.env` file, `vendor` folder, cache files, and local build/runtime files are ignored and should not be committed.
