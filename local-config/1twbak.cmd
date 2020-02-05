@ECHO OFF
for /f "tokens=1,2" %%u in ('date /t') do set d=%%v
for /f "tokens=1" %%u in ('time /t') do set t=%%u
if "%t:~1,1%"==":" set t=0%t%
set timestr=%d:~6,4%-%d:~0,2%-%d:~3,2%
echo Backup for DB tagged with timestamp %timestr%
@REM backup to both custom format (which Postgres requires for restore command) and ascii so it's human readable
"C:\Program Files\PostgreSQL\8.3\bin\pg_dump" -U postgres -h localhost -p 5234 -F c -f "Z:\My Backup\1team\DB-backup\1tw-restore-%timestr%.sql" postgres
"C:\Program Files\PostgreSQL\8.3\bin\pg_dump" -U postgres -h localhost -p 5234 -F p -f "Z:\My Backup\1team\DB-backup\1tw-ascii-%timestr%.sql" postgres
