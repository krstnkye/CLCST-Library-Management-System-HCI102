@echo off
setlocal
title CLCST Library Management System
cd /d "%~dp0"

rem ---- Refuse to run from inside the zip (Windows opens it in a temporary folder) ----
echo "%~dp0" | findstr /i /l /c:".zip" /c:"Temp1_" >nul
if not errorlevel 1 (
  echo This is running from inside the zip file.
  echo Right-click the zip, choose "Extract All...", then run START-WINDOWS.bat from the extracted folder.
  pause
  exit /b 1
)

rem ---- PHP must be installed, version 8.3 or newer, with the needed extensions ----
where php >nul 2>nul
if errorlevel 1 (
  echo PHP was not found. Install PHP 8.3 or newer first. See HOW-TO-RUN.txt, Step 1.
  pause
  exit /b 1
)
php -r "exit(version_compare(PHP_VERSION, '8.3.0', '<') ? 1 : 0);"
if errorlevel 1 (
  echo Your PHP is too old. This system needs PHP 8.3 or newer. You have:
  php -r "echo PHP_VERSION, PHP_EOL;"
  echo See HOW-TO-RUN.txt, Step 1.
  pause
  exit /b 1
)
php -r "foreach (['pdo_sqlite', 'fileinfo', 'mbstring', 'openssl'] as $e) { if (! extension_loaded($e)) { echo '  extension=', $e, PHP_EOL; $m = 1; } } exit($m ?? 0);"
if errorlevel 1 (
  echo The PHP extensions listed above are turned off. Turn them on in php.ini
  echo by removing the semicolon at the start of each line. See HOW-TO-RUN.txt, Step 1.
  pause
  exit /b 1
)

echo "%~dp0" | findstr /i /c:"OneDrive" /c:"Google Drive" /c:"Dropbox" >nul
if not errorlevel 1 (
  echo Note: this folder is inside a cloud-synced folder. Syncing can lock files and slow
  echo the system down. If pages fail to load, move the folder to C:\CLCST-Library.
  echo.
)

rem ---- Folders the system writes to (zip tools sometimes drop empty folders) ----
for %%D in ("bootstrap\cache" "storage\app\private" "storage\framework\cache\data" "storage\framework\sessions" "storage\framework\views" "storage\logs") do (
  if not exist "%%~D" mkdir "%%~D"
)

rem ---- First run: settings file and encryption key ----
if not exist ".env" (
  echo First run: preparing the project...
  copy ".env.example" ".env" >nul
  php artisan key:generate --force
  if errorlevel 1 (
    del ".env" >nul 2>nul
    echo Could not prepare the settings file. See the message above.
    pause
    exit /b 1
  )
)

rem ---- Database: create it, or finish a setup that failed last time (empty file) ----
set "NEEDS_DB="
if not exist "database\database.sqlite" set "NEEDS_DB=1"
if exist "database\database.sqlite" for %%F in ("database\database.sqlite") do if %%~zF==0 set "NEEDS_DB=1"
if defined NEEDS_DB (
  echo Creating the database and loading demo data...
  type nul > "database\database.sqlite"
  php artisan migrate --seed --force
  if errorlevel 1 (
    del "database\database.sqlite" >nul 2>nul
    echo The database could not be created. See the message above, then run this file again.
    pause
    exit /b 1
  )
)

rem ---- Use port 8001, or 8002 if something else is already using 8001 ----
set "PORT=8001"
netstat -ano | findstr /r /c:":8001 .*LISTENING" >nul
if not errorlevel 1 (
  echo Port 8001 is already in use, probably by another copy of this window. Using port 8002.
  set "PORT=8002"
)

echo.
echo The library system is starting.
echo Open http://127.0.0.1:%PORT% in your browser.
echo Keep this window open while you use it. Close it to stop the system.
echo.
rem Open the browser a few seconds later, once the server is listening.
start "" /b cmd /c "ping -n 4 127.0.0.1 >nul & start http://127.0.0.1:%PORT%"
php artisan serve --host=127.0.0.1 --port=%PORT%
echo.
echo The library system has stopped.
pause
