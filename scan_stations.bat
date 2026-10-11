@echo off
title Scanner Hardware Station PembdaHUB
color 0b
echo ========================================================
echo  PEMBDAHUB - PENCARI IP HARDWARE STATION (ESP8266/ESP32)
echo ========================================================
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scan_stations.ps1"
echo.
pause
