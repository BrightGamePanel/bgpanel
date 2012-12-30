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
		-- Version 0.3.5 to Version 0.3.9
		-- 30/12/2012
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+

		//Updating data for table "server"

		$servers = mysql_query( "SELECT `serverid`, `screen` FROM `".DBPREFIX."server`" );

		while ($rowsServers = mysql_fetch_assoc($servers))
		{
			query_basic( "UPDATE `".DBPREFIX."server` SET `screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $rowsServers['screen'])."' WHERE `serverid` = '".$rowsServers['serverid']."'" );
		}

		unset($servers);

		//---------------------------------------------------------+

		//Updating data for table "script"

		$scripts = mysql_query( "SELECT `scriptid`, `screen` FROM `".DBPREFIX."script`" );

		while ($rowsScripts = mysql_fetch_assoc($scripts))
		{
			if (!empty($rowsScripts['screen']))
			{
				query_basic( "UPDATE `".DBPREFIX."script` SET `screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $rowsScripts['screen'])."' WHERE `scriptid` = '".$rowsScripts['scriptid']."'" );
			}
		}

		unset($scripts);

		//---------------------------------------------------------+

		//Updating structure for table "box"
			query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `bw_rx` int(15) UNSIGNED NOT NULL" );
			query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `bw_tx` int(15) UNSIGNED NOT NULL" );

		//Updating structure for table "boxData"
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` ADD `bw_rx` text NOT NULL" );
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` ADD `bw_tx` text NOT NULL" );

		//---------------------------------------------------------+

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.3.9' WHERE `setting` = 'panelversion' LIMIT 1" );

		//---------------------------------------------------------+


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>