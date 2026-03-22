@echo off
TITLE Project Debugger
COLOR 0E

echo Checking XAMPP...
if exist "C:\xampp\xampp_control.exe" (
    echo [OK] XAMPP found in C:\xampp
) else (
    echo [FAIL] XAMPP NOT found in C:\xampp!
)

echo Checking Project Folder...
if exist "C:\xampp\htdocs\GREEN_CAMPUS MANAGEMENT SYSTEM\login.php" (
    echo [OK] Project found in htdocs
) else (
    echo [FAIL] Project NOT found in C:\xampp\htdocs\GREEN_CAMPUS MANAGEMENT SYSTEM\
)

echo.
echo Attempting to start Apache and MySQL...
start "" "C:\xampp\apache_start.bat"
start "" "C:\xampp\mysql_start.bat"

echo.
echo Launching project URL (with cache-busting)...
set "RAND=%RANDOM%"
set URL="http://127.0.0.1/GREEN_CAMPUS%%20MANAGEMENT%%20SYSTEM/login.php?v=%RAND%"
echo URL: %URL%
start "" %URL%

echo.
echo If the project didn't open, please check the following:
echo 1. Is another web server (like IIS or Skype) using Port 80?
echo 2. Are there any errors in the Apache/MySQL windows that just opened?
echo.
pause
