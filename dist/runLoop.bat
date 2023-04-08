@echo off
setlocal

:loop
start "RawBT Print Server" /MIN php "%~dp0/rawbt.phar"
echo RawBT server starting.

:wait
timeout /t 5 /nobreak >nul
tasklist /fi "imagename eq php.exe" | find /i "php.exe" >nul
if errorlevel 1 (
    echo RawBT server stopping. Restarting...
    goto loop
) else (
    echo RawBT server currently running.
    goto wait
)

endlocal