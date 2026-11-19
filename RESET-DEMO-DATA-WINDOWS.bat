@echo off
cd /d "%~dp0"
echo This erases everything you added and loads fresh demo data.
pause
php artisan migrate:fresh --seed --force
echo Done.
pause
