# CLCST Library Management System

Laravel-based student library portal for the CLCST Library Management System. The project includes login, registration, student dashboard, book catalog, borrow/return, reservations, borrowing history, fines and penalties, profile, notifications, and help/support pages.

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB
- Git

No Node.js build step is required. The UI uses CDN assets and Laravel Blade views.

## Fresh Setup

Clone the repository, then run:

```bash
composer install
cp .env.example .env
php artisan key:generate
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
