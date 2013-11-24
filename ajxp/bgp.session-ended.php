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
 * @version		(Release 0) DEVELOPER BETA 9
 * @link		http://www.bgpanel.net/
 */



if (is_dir("../install"))
{
	die();
}


require("../configuration.php");
require("../includes/functions.php");
require("../includes/mysql.php");


/**
 * GET BGP CORE FILES INFORMATION
 * Load version.xml (ROOT/.version/version.xml)
 */
$bgpCoreInfo = simplexml_load_file('../.version/version.xml');


$panelVersion = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" );


/**
 * VERSION CONTROL
 * Check that core files are compatible with the current BrightGamePanel Database
 */
if ($panelVersion['value'] != $bgpCoreInfo->{'version'})
{
	exit('<html><head></head><body><h1><b>Wrong Database Version Detected</b></h1><br /><h3>Make sure you have followed the instructions to install/update the database.</h3></body></html>');
}
unset($bgpCoreInfo);


/**
 * GET BrightGamePanel Database INFORMATION
 * Load 'values' from `config` Table
 */
$maintenance = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'maintenance' LIMIT 1" );
define( 'MAINTENANCE', $maintenance['value'] );
unset($maintenance);


/**
 * CRYPT_KEY is the Passphrase Used to Cipher/Decipher SSH Passwords
 * The key is stored into the file: ".ssh/passphrase"
 */
define('CRYPT_KEY', file_get_contents("../.ssh/passphrase"));


/**
 * API_KEY is used to access / protect contents
 */
define('API_KEY', substr(CRYPT_KEY, (strlen(CRYPT_KEY) / 2)));


/**
 * MAINTENANCE CHECKER
 * Logout user if this one is not a Super Administrator.
 */
if (MAINTENANCE == 1)
{
	exit('<h1><b>503 Service Unavailable</b></h1>');
}


/**
 * Authentication
 */
session_start();

if ( (isAdminLoggedIn() == FALSE) && (isClientLoggedIn() == FALSE) )
{
?>
<html>
	<head>
	</head>
	<body>
		<h1><b>403 Forbidden</b></h1>
	</body>
</html>
<?php
	die();
}


/**
 * SESSION check up (Test if the information stored in the globals $_SESSION are valid)
 */
if (isAdminLoggedIn() == TRUE)
{
	$adminverify = mysql_query( "SELECT `username`, `firstname`, `lastname`, `token`, `lastip` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$_SESSION['adminid']."' && `status` = 'Active'" );
	###
	$adminverify = mysql_fetch_assoc($adminverify);
	if (
			($adminverify['username'] != $_SESSION['adminusername']) ||
			($adminverify['firstname'] != $_SESSION['adminfirstname']) ||
			($adminverify['lastname'] != $_SESSION['adminlastname']) ||
			($adminverify['token'] != session_id()) ||
			($adminverify['lastip'] != $_SERVER['REMOTE_ADDR'])
		)
	{
		session_destroy();
		die();
	}

	/**
	 * AJXP Bridge
	 */
	define('AJXP_EXEC', true);

	$glueCode = realpath(dirname(__FILE__))."/plugins/auth.remote/glueCode.php";
	$secret = API_KEY;

	global $AJXP_GLUE_GLOBALS;
	$AJXP_GLUE_GLOBALS = array();
	$AJXP_GLUE_GLOBALS["secret"] = $secret;
	$AJXP_GLUE_GLOBALS["plugInAction"] = "logout";

	require_once($glueCode);

}
else if (isClientLoggedIn() == TRUE)
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
		die();
	}

	/**
	 * AJXP Bridge
	 */
	define('AJXP_EXEC', true);

	$glueCode = realpath(dirname(__FILE__))."/plugins/auth.remote/glueCode.php";
	$secret = API_KEY;

	global $AJXP_GLUE_GLOBALS;
	$AJXP_GLUE_GLOBALS = array();
	$AJXP_GLUE_GLOBALS["secret"] = $secret;
	$AJXP_GLUE_GLOBALS["plugInAction"] = "logout";

	require_once($glueCode);

}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>BrightGamePanel</title>
		<!--Powered By Bright Game Panel-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Javascript -->
			<script src="../bootstrap/js/jquery.js"></script>
			<script src="../bootstrap/js/bootstrap.js"></script>
		<!-- Style -->
			<!-- Boostrap -->
			<link href="../bootstrap/css/bootstrap.css" rel="stylesheet">
			<style type="text/css">
			body {
				padding-top: 60px;
				padding-bottom: 40px;
			}
			</style>
			<link href="../bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
			<!--[if lt IE 9]>
			  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
			<![endif]-->
		<!-- Favicon -->
			<link rel="shortcut icon" href="../bootstrap/img/favicon.ico">
	</head>


	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="brand" href="#">Bright Game Panel</a>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="page-header">
				<h1>AjaXplorer WebFTP Session Ended&nbsp;<small></small></h1>
			</div>
			<div class="alert alert-block">
				<h4 class="alert-heading">AjaXplorer WebFTP Session Ended</h4>
				You can now safety close this window.
			</div>
			<hr>
			<footer>
				<div class="pull-left">
					Copyleft - 2013. Released Under <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPLv3</a>.<br />
					All Images Are Copyrighted By Their Respective Owners.
				</div>
				<div class="pull-right" style="text-align: right;">
					<a href="http://www.bgpanel.net/" target="_blank">Bright Game Panel</a><br />
					Built with <a href="http://getbootstrap.com/" target="_blank">Bootstrap</a>.
				</div>
			</footer>
		</div><!--/container-->

		<!--Powered By Bright Game Panel-->

	</body>
</html>
