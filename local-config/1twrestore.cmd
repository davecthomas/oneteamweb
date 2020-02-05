@REM restore a dump to the DB. Syntax: 1twrestore <backup file name>
@REM adjust path to PostgreSQL's pg_restore command
"C:\Program Files (x86)\PostgreSQL\8.4\bin\pg_restore" -U postgres -h localhost -p 5432 -i -d postgres %1
