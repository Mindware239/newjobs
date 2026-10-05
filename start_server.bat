@echo off
cd /d "%~dp0"
echo Starting PHP Development Server...
echo.
echo Server will run on: http://localhost:8000
echo (The site must run at a root URL - http://localhost/jobsencecomplete/ breaks CSS and links.)
echo Press Ctrl+C to stop
echo.
php -S localhost:8000 index.php
