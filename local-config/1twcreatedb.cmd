@REM Create schema for 1TeamWeb
@REM adjust path to PostgreSQL's psql command
@REM This creates an empty table set, which is a good start, but you need to bootstrap with an admin user to do anything in the web app
"C:\Program Files (x86)\PostgreSQL\8.4\bin\psql" -U postgres -h localhost -p 5432 -f 1twschema1.sql
