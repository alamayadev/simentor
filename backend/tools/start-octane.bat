@echo off
:: start-octane.bat
:: Batch file to start Laravel Octane with RoadRunner and Queue Worker on Windows

cd /d E:\Github\japekv3\backend

echo Starting Laravel Queue Worker in background...
:: Start queue worker in a separate window (minimized)
start "Laravel Queue Worker" /min cmd /c "php artisan queue:work --sleep=3 --tries=3 --max-jobs=1000 --timeout=300"

echo.
echo Starting Laravel Octane with RoadRunner...
echo Access your application at http://0.0.0.0:9000
echo Press CTRL+C to stop the server
echo.

:: Set environment variables
set APP_ENV=production
set APP_DEBUG=false

:: Start Octane with minimal configuration to avoid Windows signal issues
php artisan octane:start --host=127.0.0.1 --port=9000 --workers=1 --max-requests=500 --server=roadrunner

:: When Octane stops, remind user to stop queue worker
echo.
echo Octane stopped. Remember to close the Queue Worker window if still running.
pause
