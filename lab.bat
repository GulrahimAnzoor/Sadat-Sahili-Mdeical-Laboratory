@echo off

echo Starting Apache...
net start Apache2.4

echo Starting MySQL...
net start MySQL80

echo Starting Laravel...
cd /d "D:\Sadat-Sahili-Mdeical-Laboratory"

start "" cmd /k "php artisan serve"

timeout /t 3 /nobreak >nul
start "" http://127.0.0.1:8000
