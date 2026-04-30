@echo off
REM Simentor Backend Services Launcher
REM This script starts both RoadRunner web server and Laravel Queue worker

REM Using values from .env file
REM set APP_ENV=production
REM set APP_DEBUG=true

echo [%DATE% %TIME%] Starting Simentor Backend Services...
echo [%DATE% %TIME%] Working Directory: %CD%

REM Create log directories if they don't exist
if not exist "storage\logs" mkdir "storage\logs"

REM Start RoadRunner in background
echo [%DATE% %TIME%] Starting RoadRunner web server...
start "RoadRunner Server" /MIN cmd /c "rr.exe serve -c .rr.yaml >> storage\logs\roadrunner.log 2>&1"

REM Wait a moment for RoadRunner to initialize
timeout /t 3 /nobreak >nul

REM Start Laravel Queue Worker in background
echo [%DATE% %TIME%] Starting Laravel Queue Worker...
start "Laravel Queue Worker" /MIN cmd /c "php artisan queue:work --timeout=60 --sleep=1 --tries=3 >> storage\logs\queue.log 2>&1"

echo [%DATE% %TIME%] All services started successfully!
echo [%DATE% %TIME%] Check storage\logs\ for service logs.
echo [%DATE% %TIME%] Press Ctrl+C to stop monitoring (services will continue running)

REM Keep the script running to maintain service status
:loop
timeout /t 30 /nobreak >nul
goto loop