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
		-- 25/02/2013
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

		//Updating structure for table "box"
			query_basic( "ALTER TABLE `".DBPREFIX."box` DROP (`cpu`, `ram`, `loadavg`, `hostname`, `os`, `date`, `kernel`, `arch`, `uptime`, `swap`, `hdd`, `bw_rx`, `bw_tx`)" );
			query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `cache` text NULL" );

		//---------------------------------------------------------+

		//Updating data for table "boxData"
			query_basic( "TRUNCATE `".DBPREFIX."boxData`" );

		//Updating structure for table "boxData"
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` DROP (`boxids`, `boxnetstat`, `players`, `cpu`, `ram`, `loadavg`, `hdd`, `bw_rx`, `bw_tx`)" );
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` ADD `cache` text NOT NULL" );

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