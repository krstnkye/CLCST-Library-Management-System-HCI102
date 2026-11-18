#!/usr/bin/env bash
# Starts the CLCST Library Management System on macOS or Linux.
cd "$(dirname "$0")" || exit 1

if ! command -v php >/dev/null 2>&1; then
  echo "PHP was not found. Install PHP 8.3 or newer first. See HOW-TO-RUN.txt."
  exit 1
fi

if [ ! -f .env ]; then
  echo "First run: preparing the project..."
  cp .env.example .env
  php artisan key:generate --force
fi

if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
  php artisan migrate --seed --force
fi

# Existing copies may have new tables after an update; apply them before serving.
php artisan migrate --force || exit 1

echo
echo "The library system is starting."
echo "Open http://127.0.0.1:8001 in your browser."
echo "Press Ctrl+C in this window to stop it."
echo
php artisan serve --host=127.0.0.1 --port=8001
