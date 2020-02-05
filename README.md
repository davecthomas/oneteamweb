Developer's Documentation for Installing 1TeamWeb

1. Install PostgreSQL (8.3 or later) on port 5234 
	a. Create postgres database with postgres user owner. 
		Password is whatever you want, but if you set it to something other than "password" you need to update your local copy of the pgd file in 1team.
	b. Import the sql in 1twbootstrap via psql -f
	c. You now have an application admin user with the login "admin@1teamweb.com" and password "password" 
	d. Create an odbc connection called "wwwpg1teamweb" 
	   On Windows: run the ODBC control panel app. Note: on 64 bit windows connecting to 32 bit DB, you must configure this with C:\Windows\SysWOW64\odbcad32.exe, 
	   not the default control panel app in the adminstrative toolset.

2. Install PHP 5.3.1 or later. Use php.ini file to ensure you have configured the correct extensions. 
	Only the extensions in this file are relevant for customizing your config. Other settings are optional.
	You must set an environment variable called PHPRC to the install dir of PHP for the ini file to be found
	When you change PHP.ini, don't forget to either restart your web server or in IIS, just recycle your default application pool

3. Configure a new web server instance 
	a. Require bindings for http and https (self-signed cert is fine, but https is required).
	b. If CakePHP is in use (not complete) URL re-writing is required to be configured. With IIS, you must install http://www.iis.net/download/URLRewrite plug-in 
	
4. Install a Subversion client
	a. Create dir "1team" under your web server's wwwroot and sync head revision. This is the 1teamweb web site root.
	b. Create dir "uploaded" under 1team. This is the uploads directory. This is not checked in to SVN to prevent publishing test data to production site.
	c. if Dave has port forwarding turned on, which he currently does NOT, access SVN on port 8443 at http://1teamweb.com. Otherwise, it's on internal
	   host david.

5. Security recommendation: do not expose your Postgres or web server ports outside your firewall, especially with config dir exposed, and with default password!

6. Development IDE suggestion: Netbeans 6.8 or later with support for PHP, SVN, PostgreSQL, and JIRA built-in! Yes, it does RoR also.

7. Jira is installed on an internal host david at port 8080. If port redirection is on (not now), it's at http://1teamweb.com:8080