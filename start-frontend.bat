@echo off
title Smart Guidance - Next.js Frontend
echo ============================================
echo  SMART GUIDANCE - Next.js Frontend Dev Server
echo ============================================
echo.
cd /d "%~dp0frontend"
echo Starting Next.js at http://localhost:3000
echo Press Ctrl+C to stop.
echo.
node node_modules\.bin\next dev
pause
