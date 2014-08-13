@echo off
rem -------------------------------------------
rem ShiftMETHOD IdentityAccess database setup
rem -------------------------------------------

echo Creating IDDD IdentityAccess database...
type ..\sql\iam.sql ..\..\..\Common\Resources\sql\common.sql > create_iam.sql
mysql -u root -p < create_iam.sql
del /Q create_iam.sql
echo Completed
