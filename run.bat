@echo off
TITLE Green Campus Management System - Launcher
COLOR 0A

echo =======================================================
echo    GREEN CAMPUS MANAGEMENT SYSTEM - PROJECT LAUNCHER
echo =======================================================
echo.

:: Check if XAMPP exists and start services
if exist "C:\xampp\xampp_start.exe" (
    echo [1/2] Starting XAMPP Services (Apache ^& MySQL)...
    :: Start XAMPP start executable in minimized mode
    start /min "" "C:\xampp\xampp_start.exe"
    :: Give services a moment to start
    timeout /t 3 /nobreak > nul
) else (
    echo [!] WARNING: XAMPP was not found at C:\xampp.
    echo Please make sure XAMPP is installed and Apache/MySQL are running manually.
    echo.
)

:: Open the browser to the project's login page with cache busting
echo [2/2] Opening project in your default browser...
set "RAND=%RANDOM%"
start "" "http://127.0.0.1/GREEN_CAMPUS%%20MANAGEMENT%%20SYSTEM/login.php?v=%RAND%"

echo.
echo =======================================================
echo    Project is now running! You can close this window.
echo =======================================================
echo.
pause
