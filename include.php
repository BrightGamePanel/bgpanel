<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * LICENSE:
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @categories	Games/Entertainment, Systems Administration
 * @package		Bright Game Panel
 * @author		warhawk3407 <warhawk3407@gmail.com> @NOSPAM
 * @copyleft	2013
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 5
 * @link		http://www.bgpanel.net/
 */



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}


if (is_dir("install"))
{
	header( "Location: install" );
	die();
}

$perms = substr(sprintf('%o', fileperms('./.ssh/passphrase')), -4); //Check PASSPHRASE file CHMOD
if ($perms != '0644')
{
	exit('<html><head></head><body><h1><b>Wrong PASSPHRASE file CHMOD Detected</b></h1><br /><h3>Please change the PASSPHRASE file (.ssh/passphrase) CHMOD to 0644.</h3></body></html>');
}
unset($perms);


require("./includes/functions.php");
require("./includes/mysql.php");


/**
 * Authentication
 */
session_start();

if (isClientLoggedIn() == FALSE) //Check if the user have wanted to access to a protected resource without being logged in
{
	if (!empty($return))  //Retrieve the last page where the user wanted to go
	{
		if ($return === TRUE) //Process protection
		{
			header( "Location: login.php" );
			die();
		}
		else
		{
			header( "Location: login.php?return=".urldecode($return) );
			die();
		}
	}
}


/**
 * SESSION check up (Test if the information stored in the globals $_SESSION are valid)
 */
if (isClientLoggedIn() == TRUE)
{
	$clientverify = mysql_query( "SELECT `username`, `firstname`, `lastname`, `token`, `lastip` FROM `".DBPREFIX."client` WHERE `clientid` = '".$_SESSION['clientid']."' && `status` = 'Active'" );
	###
	$clientverify = mysql_fetch_assoc($clientverify);
	if (
			($clientverify['username'] != $_SESSION['clientusername']) ||
			($clientverify['firstname'] != $_SESSION['clientfirstname']) ||
			($clientverify['lastname'] != $_SESSION['clientlastname']) ||
			($clientverify['token'] != session_id()) ||
			($clientverify['lastip'] != $_SERVER['REMOTE_ADDR'])
		)
	{
		session_destroy();
		header( "Location: login.php" );
		die();
	}
	###
	query_basic( "UPDATE `".DBPREFIX."client` SET `lastactivity` = '".$_SERVER['REQUEST_TIME']."' WHERE `clientid` = '".$_SESSION['clientid']."'" );
}


/**
 * GET BrightGamePanel Database INFORMATION
 * Load 'values' from `config` Table
 */
$panelName = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelname' LIMIT 1" );
$panelVersion = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" );
$template = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'clienttemplate' LIMIT 1" );
$maintenance = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'maintenance' LIMIT 1" );


/**
 * GET BGP CORE FILES INFORMATION
 * Load version.xml (ROOT/.version/version.xml)
 */
$bgpCoreInfo = simplexml_load_file('./.version/version.xml');


/**
 * VERSION CONTROL
 * Check that core files are compatible with the current BrightGamePanel Database
 */
if ($panelVersion['value'] != $bgpCoreInfo->{'version'})
{
	die();
}


/*
 * CONSTANTS
 */
define( 'SITENAME', $panelName['value'] );
define( 'DBVERSION', $panelVersion['value'] );
define( 'TEMPLATE', $template['value'] );
define( 'MAINTENANCE', $maintenance['value'] );
unset($panelName, $panelVersion, $template, $maintenance);

define( 'PROJECT', $bgpCoreInfo->{'project'} );
define( 'PACKAGE', $bgpCoreInfo->{'package'} );
define( 'BRANCH', $bgpCoreInfo->{'branch'} );
define( 'COREVERSION', $bgpCoreInfo->{'version'} );
define( 'RELEASEDATE', $bgpCoreInfo->{'date'} );
unset($bgpCoreInfo);


/**
 * CRYPT_KEY is the Passphrase Used to Cipher/Decipher SSH Passwords
 * The key is stored into the file: ".ssh/passphrase"
 */
define('CRYPT_KEY', file_get_contents("./.ssh/passphrase"));


/**
 * MAINTENANCE CHECKER
 * Logout client.
 */
if (MAINTENANCE == 1)
{
	if (isClientLoggedIn() == TRUE)
	{
		logout();
		exit('<h1><b>503 Service Unavailable</b></h1>'); //If the maintenance mode is ON, we drop the user.
	}
}

?>