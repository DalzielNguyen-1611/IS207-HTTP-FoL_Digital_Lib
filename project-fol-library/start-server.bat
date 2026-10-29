@echo off
chcp 65001 >nul
title FoL Digital Library - Local Server (Port 8000)

echo ========================================================
echo   FoL DIGITAL LIBRARY - KHOA VAN HOC (IS207.R11)
echo   Dang khoi dong may chu PHP Localhost...
echo ========================================================
echo.

cd /d "%~dp0"

set "PHP_CMD=php"

if exist "E:\xampp\php\php.exe" (
    set "PHP_CMD=E:\xampp\php\php.exe"
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_CMD=C:\xampp\php\php.exe"
) else if exist "D:\xampp\php\php.exe" (
    set "PHP_CMD=D:\xampp\php\php.exe"
)

echo [1/2] Dang dung PHP tai: %PHP_CMD%
echo [2/2] Khoi chay may chu: http://localhost:8000
echo.
echo Luu y: Hay dam bao MySQL trong XAMPP Control Panel dang chay (Start).
echo Nhan Ctrl + C de dung may chu khi khong su dung.
echo --------------------------------------------------------
echo.

start "" http://localhost:8000

"%PHP_CMD%" -S localhost:8000 -t public

pause
