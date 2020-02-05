@ECHO OFF
@REM backup schema only to ascii format. Syntax: 1twschema 
@REM adjust path to PostgreSQL's pg_dump command
"C:\Program Files (x86)\PostgreSQL\8.4\bin\pg_dump" -U postgres -h localhost -p 5234 -F p -s -f "1twschema.sql" postgres
