@echo off
title Greenhouse Monitor Server
color 0A

echo ================================================
echo       GREENHOUSE MONITOR - AUTO START
echo ================================================
echo.

:: Jalankan MySQL dan Apache XAMPP
echo [1/3] Menjalankan XAMPP (MySQL + Apache)...
start "" "D:\xampp\xampp-control.exe"
timeout /t 5 /nobreak >nul

:: Jalankan Laravel Server
echo [2/3] Menjalankan Laravel Server...
start cmd /k "cd /d D:\xampp\htdocs\greenhouse && php -S 127.0.0.1:8000 public/index.php"
timeout /t 3 /nobreak >nul

:: Jalankan ngrok
echo [3/3] Menjalankan ngrok...
start cmd /k "C:\Users\Acerr\AppData\Local\Microsoft\WindowsApps\ngrok.exe http 8000"
timeout /t 3 /nobreak >nul

:: Buka dashboard ngrok di browser
echo.
echo ================================================
echo  Server sudah jalan!
echo  Buka http://127.0.0.1:4040 untuk lihat URL ngrok
echo  Kasih URL itu ke Rio dan Dafa
echo ================================================
echo.

start "" "http://127.0.0.1:4040"
start "" "http://127.0.0.1:8000"

pause
