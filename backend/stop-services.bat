@echo off
REM Simentor Backend Services Stopper
REM This script stops both RoadRunner and Laravel Queue worker

echo [%DATE% %TIME%] Stopping Simentor Backend Services...

REM Stop RoadRunner
echo [%DATE% %TIME%] Stopping RoadRunner...
taskkill /F /IM rr.exe 2>nul
taskkill /F /FI "WINDOWTITLE eq RoadRunner Server*" 2>nul

REM Stop Laravel Queue Worker
echo [%DATE% %TIME%] Stopping Laravel Queue Worker...
taskkill /F /IM php.exe /FI "WINDOWTITLE eq Laravel Queue Worker*" 2>nul

echo [%DATE% %TIME%] All services stopped successfully!