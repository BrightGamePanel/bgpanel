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
 * @version		(Release 0) DEVELOPER BETA 6
 * @link		http://www.bgpanel.net/
 */



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//MySQL


$mysql_link = mysql_connect(DBHOST,DBUSER,DBPASSWORD);
if (!$mysql_link)
{
	die('Could not connect to MySQL: '.mysql_error());
}
else
{
	$mysql_database_link = mysql_select_db(DBNAME);
	if ($mysql_database_link == FALSE)
	{
		echo "Could not connect to MySQL database";
	}
	else
	{


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		//---------------------------------------------------------+

		/*
		-- BrightGamePanel Database Update
		-- Version 0.3.9 to Version 0.4.0
		-- 05/04/2013
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+

		//Updating structure for table "admin"
			query_basic( "ALTER TABLE `".DBPREFIX."admin` ADD `lang` text NOT NULL" );

		//---------------------------------------------------------+

		//Updating data for table "admin"

		$admins = mysql_query( "SELECT `adminid` FROM `".DBPREFIX."admin`" );

		while ($rowsAdmins = mysql_fetch_assoc($admins))
		{
			query_basic( "UPDATE `".DBPREFIX."admin` SET `lang` = '".DEFAULT_LOCALE."' WHERE `adminid` = '".$rowsAdmins['adminid']."'" );
		}

		unset($admins);

		//---------------------------------------------------------+

		//Updating structure for table "box"
			query_basic( "ALTER TABLE `".DBPREFIX."box` DROP `cpu`,
			  DROP `ram`,
			  DROP `loadavg`,
			  DROP `hostname`,
			  DROP `os`,
			  DROP `date`,
			  DROP `kernel`,
			  DROP `arch`,
			  DROP `uptime`,
			  DROP `swap`,
			  DROP `hdd`,
			  DROP `bw_rx`,
			  DROP `bw_tx`;" );
			query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `cache` text NULL" );

		//---------------------------------------------------------+

		//Updating data for table "boxData"
			query_basic( "TRUNCATE `".DBPREFIX."boxData`" );

		//Updating structure for table "boxData"
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` DROP `boxids`,
			  DROP `boxnetstat`,
			  DROP `players`,
			  DROP `cpu`,
			  DROP `ram`,
			  DROP `loadavg`,
			  DROP `hdd`,
			  DROP `bw_rx`,
			  DROP `bw_tx`;" );
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` ADD `cache` text NOT NULL" );

		//---------------------------------------------------------+

		//Create table "boxIp"
			query_basic( "
			  CREATE TABLE IF NOT EXISTS `".DBPREFIX."boxIp` (
			    `ipid` int(8) unsigned NOT NULL AUTO_INCREMENT,
			    `boxid` int(8) unsigned NOT NULL,
			    `ip` text NOT NULL,
			    PRIMARY KEY (`ipid`)
			  )
			  ENGINE=MyISAM  ; " );

		//Updating data for table "boxIp"
		$boxes = mysql_query( "SELECT `boxid`, `ip` FROM `".DBPREFIX."box`" );

		while ($rowsBoxes = mysql_fetch_assoc($boxes))
		{
			query_basic( "
			INSERT INTO `".DBPREFIX."boxIp` (`boxid`, `ip`)
			VALUES
			  ('".$rowsBoxes['boxid']."', '".$rowsBoxes['ip']."')  ; " );
		}

		unset($boxes);

		//---------------------------------------------------------+

		//Updating structure for table "client"
			query_basic( "ALTER TABLE `".DBPREFIX."client` ADD `lang` text NOT NULL" );

		//---------------------------------------------------------+

		//Updating data for table "client"

		$clients = mysql_query( "SELECT `clientid` FROM `".DBPREFIX."client`" );

		while ($rowsClients = mysql_fetch_assoc($clients))
		{
			query_basic( "UPDATE `".DBPREFIX."client` SET `lang` = '".DEFAULT_LOCALE."' WHERE `clientid` = '".$rowsClients['clientid']."'" );
		}

		unset($clients);

		//---------------------------------------------------------+

		//Updating structure for table "server"
			query_basic( "ALTER TABLE `".DBPREFIX."server` ADD `ipid` int(8) NOT NULL AFTER `boxid`" );

		//Updating data for table "server"
		$servers = mysql_query( "SELECT `serverid`, `boxid` FROM `".DBPREFIX."server`" );

		while ($rowsServers = mysql_fetch_assoc($servers))
		{
			$ipid = query_fetch_assoc( "SELECT `ipid` FROM `".DBPREFIX."boxIp` WHERE `boxid` = '".$rowsServers['boxid']."' LIMIT 1" );

			query_basic( "UPDATE `".DBPREFIX."server` SET `ipid` = '".$ipid['ipid']."' WHERE `serverid` = '".$rowsServers['serverid']."' LIMIT 1" );
		}

		unset($servers);

		//---------------------------------------------------------+

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.4.0' WHERE `setting` = 'panelversion' LIMIT 1" );

		//---------------------------------------------------------+


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>