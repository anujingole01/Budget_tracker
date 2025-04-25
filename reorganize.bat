@echo off
REM Run this from your budget-tracker root folder

cd p

REM Create new directory structure
mkdir private
mkdir api
mkdir api\transactions
mkdir public
mkdir public\assets

REM Move files to secure locations
move config.php private\
move header.php private\
move footer.php private\
move assets\* public\assets\

REM Create API endpoints (copies existing logic)
copy transactions.php api\transactions\get.php
echo <?php require_once __DIR__ . '/../../private/config.php'; > api\transactions\export.php
echo header('Content-Type: text/csv'); >> api\transactions\export.php
echo header('Content-Disposition: attachment; filename="transactions.csv"'); >> api\transactions\export.php
echo // Your export logic here >> api\transactions\export.php

REM Create vercel.json
echo { > ..\vercel.json
echo   "version": 2, >> ..\vercel.json
echo   "builds": [ >> ..\vercel.json
echo     { "src": "p/public/*.php", "use": "@vercel/php" }, >> ..\vercel.json
echo     { "src": "p/api/**/*.php", "use": "@vercel/php" } >> ..\vercel.json
echo   ], >> ..\vercel.json
echo   "routes": [ >> ..\vercel.json
echo     { "src": "/api/(.*)", "dest": "/p/api/$1" }, >> ..\vercel.json
echo     { "src": "/(.*)", "dest": "/p/public/$1" } >> ..\vercel.json
echo   ] >> ..\vercel.json
echo } >> ..\vercel.json

echo Reorganization complete! Check the new structure.
pause