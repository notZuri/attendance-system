@echo off
echo Starting WebSocket Server for Attendance System...
echo.
echo Make sure you have:
echo 1. XAMPP running (Apache + MySQL)
echo 2. Composer installed with Ratchet library
echo 3. Your computer's IP address configured in ESP32 code
echo.
echo WebSocket server will start on port 8080
echo Press Ctrl+C to stop the server
echo.

cd /d "C:\xampp\htdocs\attendance-system"

REM Check if Composer is installed
if not exist "vendor" (
    echo Installing Composer dependencies...
    composer install
    if errorlevel 1 (
        echo Error: Composer not found or failed to install dependencies
        echo Please install Composer and run: composer require cboden/ratchet
        pause
        exit /b 1
    )
)

REM Start the WebSocket server
echo Starting WebSocket server...
php websocket_server.php

pause 