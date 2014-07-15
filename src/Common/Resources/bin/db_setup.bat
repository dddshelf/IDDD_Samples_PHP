@echo off
rem -------------------------------------------
rem IDDD Common database setup
rem -------------------------------------------

SET "DIR=%~dp0"
SET "TESTFILE=%DIR%/../sql/test_common.sql"
SET "COMMONFILE=%DIR%/../sql/common.sql"

echo Creating IDDD Common test database...
type %TESTFILE% %COMMONFILE% > create_test_common.sql
mysql -u root -p < create_test_common.sql
del /Q create_test_common.sql

echo Completed