# Developer's Documentation for Installing 1TeamWeb

1. Install PostgreSQL (8.3 or later) on port 5234
	a. Create postgres database with postgres user owner.
	b. Import the sql in 1twbootstrap via psql -f
	c. You now have an application admin user with the login "admin@1teamweb.com" and password "password"

2. Install PHP 5.3.1 or later. Use php.ini file to ensure you have configured the correct extensions.
	Only the extensions in this file are relevant for customizing your config. Other settings are optional.
	You must set an environment variable called PHPRC to the install dir of PHP for the ini file to be found
	When you change PHP.ini, don't forget to either restart your web server or in IIS, just recycle your default application pool

3. Configure a new web server instance
	a. Require bindings for http and https (self-signed cert is fine, but https is required).
	b. If CakePHP is in use (not complete) URL re-writing is required to be configured.
