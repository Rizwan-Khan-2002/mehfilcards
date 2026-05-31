@echo off
title MehfilCards Mobile Server
cd /d "F:\INVITATION CARDS\mehfilcards-laravel"
echo.
echo MehfilCards server starting...
echo.
echo Computer and mobile link:
echo http://192.168.1.7:8002
echo.
echo IMPORTANT: Keep this window open while using QR scan.
echo If Windows Firewall asks, click Allow.
echo.
"C:\xampp\php\php.exe" artisan serve --host=0.0.0.0 --port=8002
echo.
echo Server stopped. Press any key to close.
pause >nul
