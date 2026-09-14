@echo off
setlocal enabledelayedexpansion
title PembdaHUB - Sinkronisasi Storage (File Upload)

:: Cari PHP di folder Laragon
set "PHP_BIN=d:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

if not exist "!PHP_BIN!" (
    for /d %%D in (d:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
    )
)

if not exist "!PHP_BIN!" (
    where php >nul 2>nul
    if %errorlevel% equ 0 (
        set "PHP_BIN=php"
    ) else (
        echo [ERROR] PHP tidak ditemukan di Laragon atau PATH sistem.
        pause
        exit /b 1
    )
)

"!PHP_BIN!" "%~dp0sync-storage.php" %*

if %errorlevel% neq 0 (
    echo.
    echo [SELESAI] Tekan sembarang tombol untuk menutup jendela ini...
    pause >nul
)
