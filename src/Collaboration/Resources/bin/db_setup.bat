@echo off
rem -------------------------------------------
rem IDDD collaboration views database setup
rem -------------------------------------------

SET "FILE=%~dp0"
SET "FILE=%FILE%/../sql/collaboration.sql"
echo Creating IDDD Collaboration Event Store and Views database...
mysql -u root -p < %FILE%

echo Completed
