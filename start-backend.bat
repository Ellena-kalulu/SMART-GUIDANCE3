@echo off
title Smart Guidance - Laravel Backend
echo ============================================
echo  SMART GUIDANCE - Laravel Backend (API + Blade)
echo ============================================
echo.
cd /d "%~dp0backend"
echo Starting Laravel at http://127.0.0.1:8000
echo Press Ctrl+C to stop.
echo.
php artisan serve
pause
