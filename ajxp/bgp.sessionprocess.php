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
require("../libs/ajxp/bridge.php");
require("../libs/phpseclib/Crypt/AES.php");


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
 * CRYPT_KEY is the Passphrase Used to Cipher/Decipher SSH Passwords
 * The key is stored into the file: ".ssh/passphrase"
 */
define('CRYPT_KEY', file_get_contents("../.ssh/passphrase"));
$aes = new Crypt_AES();
$aes->setKeyLength(256);
$aes->setKey(CRYPT_KEY);


/**
 * API_KEY is used to access / protect contents
 */
define('API_KEY', substr(CRYPT_KEY, (strlen(CRYPT_KEY) / 2)));


/**
 * GET BrightGamePanel Database INFORMATION
 * Load 'values' from `config` Table
 */
$maintenance = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'maintenance' LIMIT 1" );
define( 'MAINTENANCE', $maintenance['value'] );
unset($maintenance);


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
	 * Get BGP Workspaces
	 */
	$bgpBoxes = array();
	if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box` ORDER BY `boxid`" ) != 0)
	{
		$boxes = mysql_query( "SELECT `boxid`, `name`, `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box`" );

		while ($rowsBoxes = mysql_fetch_assoc($boxes))
		{
			$rowsBoxes['password'] = $aes->decrypt($rowsBoxes['password']);
			$rowsBoxes['path'] = '/home/'.$rowsBoxes['login'].'/';

			$bgpBoxes[] = $rowsBoxes;
		}
		unset($boxes);
	}

	$bgpServers = array();
	if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `status` = 'Active' ORDER BY `serverid`" ) != 0)
	{
		$servers = mysql_query( "SELECT `serverid`, `boxid`, `ipid`, `name`, `path` FROM `".DBPREFIX."server` WHERE `status` = 'Active'" );

		while ($rowsServers = mysql_fetch_assoc($servers))
		{
			$box = query_fetch_assoc( "SELECT `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsServers['boxid']."'" );
			$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rowsServers['ipid']."'" );

			$box['password'] = $aes->decrypt($box['password']);
			$rowsServers['path'] = dirname($rowsServers['path']).'/';

			unset($rowsServers['boxid'], $rowsServers['ipid']);

			$bgpServers[] = $rowsServers+$box+$ip;
		}
		unset($servers);
	}

	/**
	 * AJXP Hook
	 */
	define('AJXP_EXEC', true);

	$glueCode = realpath(dirname(__FILE__))."/plugins/auth.remote/glueCode.php";

	if ( isset($_GET["api_key"]) && isset($_GET["login"]) && isset($_GET["password"]) ) {

		$secret	= $_GET["api_key"];
		$user	= $_SESSION['adminusername'];

		if ($secret == API_KEY) {

			/**
			 * AJXP Bridge
			 */
			$AJXP_Bridge = new AJXP_Bridge( $bgpBoxes, $bgpServers, $user );

			// Update Workspaces
			$AJXP_Bridge->updateAJXPWorspaces();

			// Update Current User Workspaces
			$AJXP_Bridge->updateAJXPUser();

		}

		// Initialize the "parameters holder"
		global $AJXP_GLUE_GLOBALS;
		$AJXP_GLUE_GLOBALS = array();
		$AJXP_GLUE_GLOBALS["secret"] = $secret;
		$AJXP_GLUE_GLOBALS["plugInAction"] = "login";
		$AJXP_GLUE_GLOBALS["login"] = array(
										"name" => $user,
										"password" => md5($_GET["password"])
									);
		$AJXP_GLUE_GLOBALS["autoCreate"] = true;

		// NOW call glueCode!
		require_once($glueCode);
		header( "Location: index.php" );
		die();
	}

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
	 * Get BGP Workspaces
	 */
	$bgpServers = array();

	$bgpServers = getClientServers( $_SESSION['clientid'] );

	foreach($bgpServers as $key => $item)
	{
		$box = query_fetch_assoc( "SELECT `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$item['boxid']."'" );
		$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$item['ipid']."'" );

		$box['password'] = $aes->decrypt($box['password']);

		// Real path
		$item['path'] = dirname($item['path']).'/';

		// Clean Arr
		unset($item['groupid'], $item['boxid'], $item['ipid'], $item['gameid'], $item['game'], $item['status'], $item['panelstatus'], $item['slots'],
		$item['port'], $item['queryport'], $item['priority'], $item['startline'], $item['screen'], $item['boxid']
		);
		unset(
			$item['cfg1name'], $item['cfg1'],
			$item['cfg2name'], $item['cfg2'],
			$item['cfg3name'], $item['cfg3'],
			$item['cfg4name'], $item['cfg4'],
			$item['cfg5name'], $item['cfg5'],
			$item['cfg6name'], $item['cfg6'],
			$item['cfg7name'], $item['cfg7'],
			$item['cfg8name'], $item['cfg8'],
			$item['cfg9name'], $item['cfg9']
		);

		$bgpServers[$key] = $item+$box+$ip;
	}

	/**
	 * AJXP Hook
	 */
	define('AJXP_EXEC', true);

	$glueCode = realpath(dirname(__FILE__))."/plugins/auth.remote/glueCode.php";

	if ( isset($_GET["api_key"]) && isset($_GET["login"]) && isset($_GET["password"]) ) {

		$secret = $_GET["api_key"];
		$user	= $_SESSION['clientusername'];

		if ($secret == API_KEY) {

			/**
			 * AJXP Bridge
			 */
			$AJXP_Bridge = new AJXP_Bridge( array(), $bgpServers, $user );

			// Update Workspaces
			$AJXP_Bridge->updateAJXPWorspaces();

			// Update Current User Workspaces
			$AJXP_Bridge->updateAJXPUser();

		}

		// Initialize the "parameters holder"
		global $AJXP_GLUE_GLOBALS;
		$AJXP_GLUE_GLOBALS = array();
		$AJXP_GLUE_GLOBALS["secret"] = $secret;
		$AJXP_GLUE_GLOBALS["plugInAction"] = "login";
		$AJXP_GLUE_GLOBALS["login"] = array(
										"name" => $user,
										"password" => md5($_GET["password"])
									);
		$AJXP_GLUE_GLOBALS["autoCreate"] = true;

		// NOW call glueCode!
		require_once($glueCode);
		header( "Location: index.php" );
		die();
	}

}

?>
<html>
	<head>
	</head>
	<body>
		<h1><b>403 Forbidden</b></h1>
	</body>
</html>