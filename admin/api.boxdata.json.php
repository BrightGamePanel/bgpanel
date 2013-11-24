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



set_time_limit(60);

chdir(realpath(dirname(__FILE__)));
set_include_path(realpath(dirname(__FILE__)));

require('../configuration.php');

/**
 * CRYPT_KEY is the Passphrase Used to Cipher/Decipher SSH Passwords
 * The key is stored into the file: ".ssh/passphrase"
 */
define('CRYPT_KEY', file_get_contents("../.ssh/passphrase"));

/**
 * API_KEY is used to access / protect contents
 */
define('API_KEY', substr(CRYPT_KEY, (strlen(CRYPT_KEY) / 2)));


require('../includes/functions.php');
require('../includes/mysql.php');
require_once('../libs/lgsl/lgsl_class.php');
require_once('../libs/phpseclib/SSH2.php');
require_once("../libs/phpseclib/Crypt/AES.php");


//Checks if the install directory has been removed
if (is_dir("../install"))
{
	die();
}


/**
 * GET BrightGamePanel Database INFORMATION
 * Load 'values' from `config` Table
 */
$panelVersion = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" );
$maintenance = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'maintenance' LIMIT 1" );


/**
 * GET BGP CORE FILES INFORMATION
 * Load version.xml (ROOT/.version/version.xml)
 */
$bgpCoreInfo = simplexml_load_file('../.version/version.xml');


/**
 * VERSION CONTROL
 * Check that core files are compatible with the current BrightGamePanel Database
 */
if ($panelVersion['value'] != $bgpCoreInfo->{'version'})
{
	die();
}



unset($panelVersion, $maintenance);


//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+


/**
 * SECURITY
 */
if (!isset($_GET['api_key']))
{
	exit('API Key is invalid.');
}
if ($_GET['api_key'] != API_KEY)
{
	exit('API Key is invalid.');
}


/**
 * PROCESS
 */
if (isset($_GET['task']))
{
	$task = $_GET['task'];
}
else
{
	$task = NULL;
}


/**
 * DATA AVAILABILITY
 */
if ( query_numrows( "SELECT `id` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" ) == 0 )
{
	header("content-type: application/json");
	echo json_encode(array());
	die();
}


//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+


/**
 * Convert bytes to human readable format
 */
function bytesToSize2($bytes, $case)
{
	$kilobyte = 1024;
	$megabyte = $kilobyte * 1024;
	$gigabyte = $megabyte * 1024;
	$terabyte = $gigabyte * 1024;

	if ($case == 'megabyte') {
		return round($bytes / $megabyte, 2); // MB

	} else {
		return round($bytes / $gigabyte, 2); // GB
	}
}


//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+


// Set the JSON header
header("content-type: application/json");


// JSON ARRAY
$json = '[';


//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+


switch (@$task)
{
	case 'players':
		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			$timestamp = $rowsSql['timestamp'] * 1000;
			$players = 0;

			foreach($cache as $key => $value){
				$players += $value['players']['players'];
			}

			$json .= '['.$timestamp.','.$players.'],';
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxplayers':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;
				$players = 0;
				$players += $cache[$boxid]['players']['players'];

				$json .= '['.$timestamp.','.$players.'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxcpu':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;

				$json .= '['.$timestamp.','.$cache[$boxid]['cpu']['usage'].'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxram':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;

				$json .= '['.$timestamp.','.$cache[$boxid]['ram']['usage'].'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxloadavg':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;

				$json .= '['.$timestamp.','.$cache[$boxid]['loadavg']['loadavg'].'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxbwrx usage':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;
				$megabytes = bytesToSize2($cache[$boxid]['bandwidth']['rx_usage'], 'megabyte');

				$json .= '['.$timestamp.','.$megabytes.'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxbwrx consumption':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		$old = 0; // Previous rx_total

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;

				$consumption = $cache[$boxid]['bandwidth']['rx_total'] - $old; // Relative consumption
				$old = $cache[$boxid]['bandwidth']['rx_total'];

				if ($consumption < 0) {
					// Box has rebooted
					$consumption = $cache[$boxid]['bandwidth']['rx_total'];
				}

				$json .= '['.$timestamp.','.bytesToSize2($consumption, 'gigabyte').'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxbwtotal rx consumption':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;
				$consumption = $cache[$boxid]['bandwidth']['rx_total']; // Consumption

				$json .= '['.$timestamp.','.bytesToSize2($consumption, 'gigabyte').'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxbwtx usage':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;
				$megabytes = bytesToSize2($cache[$boxid]['bandwidth']['tx_usage'], 'megabyte');

				$json .= '['.$timestamp.','.$megabytes.'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxbwtx consumption':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		$old = 0; // Previous tx_total

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;

				$consumption = $cache[$boxid]['bandwidth']['tx_total'] - $old; // Relative consumption
				$old = $cache[$boxid]['bandwidth']['tx_total'];

				if ($consumption < 0) {
					// Box has rebooted
					$consumption = $cache[$boxid]['bandwidth']['tx_total'];
				}

				$json .= '['.$timestamp.','.bytesToSize2($consumption, 'gigabyte').'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	case 'boxbwtotal tx consumption':
		if (isset($_GET['boxid'])) {
			if (is_numeric($_GET['boxid'])) {
				$boxid = $_GET['boxid'];
			}
			else {
				exit('FAILURE');
			}
		}
		else {
			exit('FAILURE');
		}

		if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			exit('FAILURE');
		}

		$sql = mysql_query( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 * 3 + CRONDELAY))."'" );

		while ($rowsSql = mysql_fetch_assoc($sql))
		{
			$cache = unserialize(gzuncompress($rowsSql['cache']));

			if ( array_key_exists($boxid, $cache) ) {
				$timestamp = $rowsSql['timestamp'] * 1000;
				$consumption = $cache[$boxid]['bandwidth']['tx_total']; // Consumption

				$json .= '['.$timestamp.','.bytesToSize2($consumption, 'gigabyte').'],';
			}
		}
	break;

	//------------------------------------------------------------------------------------------------------------+

	default:
		exit('FAILURE');
}


//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+


// Last Coma Patch
if ( substr($json, -1) == ',') {
	$json = substr($json, 0, -1); // Remove the last coma
}

// Set the JSON footer
$json .= ']';

// Output
echo $json;


//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+


?>