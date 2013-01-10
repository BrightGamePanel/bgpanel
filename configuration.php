<?php
//*************************************************************************************************

	### REQUIRED SETTINGS ###

	// DO NOT CHANGE
	define('LICENSE', 'GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007');
	// DO NOT CHANGE

	// DBHOST is the MySQL Database Hostname
	// Default: "localhost"
	define('DBHOST', 'localhost');

	// DBNAME is the MySQL Database Name
	define('DBNAME', 'brightgamepanel');

	// DBUSER is the MySQL Database Username
	define('DBUSER', 'root');

	// DBPASSWORD is the MySQL Database Password
	define('DBPASSWORD', '');

	// DBPREFIX is the MySQL Table Prefix
	define('DBPREFIX', 'bgp_');


	/**
	 * CRON Configuration
	 * Sets the period (in seconds) between two crons
	 * Required for pChart
	 */
	define('CRONDELAY', 600); // Default: "600"

	/**
	 * DATE Configuration
	 * Sets the default timezone used by all date/time functions
	 * @link: http://php.net/manual/en/timezones.php
	 */
	date_default_timezone_set('Europe/London'); // Default: "Europe/London"

	/**
	 * Internalization define constants
	 */
	define('PROJECT_DIR', $_SERVER['DOCUMENT_ROOT']);
	define('LOCALE_DIR', PROJECT_DIR .'/locale');
	define('DEFAULT_LOCALE', 'fr_FR');
	/**
	 * ERROR Handling
	 * Sets which PHP errors are reported
	 * @link: http://php.net/manual/en/function.error-reporting.php
	 *
	 * Turn off all error reporting:
	 * error_reporting(0);
	 *
	 * Report all PHP errors:
	 * error_reporting(E_ALL);
	 */
	error_reporting(E_ALL);
//*************************************************************************************************
?>