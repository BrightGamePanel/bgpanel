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
		-- Version 0.4.1 to Version 0.4.5
		-- 21/08/2013
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+

		//Updating structure for table "box"
			query_basic( "ALTER TABLE `".DBPREFIX."box` CHANGE `cache` `cache` BLOB NULL DEFAULT NULL" );
			query_basic( "ALTER TABLE `".DBPREFIX."box` CHANGE `password` `password` BLOB NOT NULL" );

		//---------------------------------------------------------+

		//Updating structure for table "boxData"
			query_basic( "ALTER TABLE `".DBPREFIX."boxData` CHANGE `cache` `cache` BLOB NOT NULL" );

		//---------------------------------------------------------+

		//Updating data for table "game"

			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/minecraft/' WHERE `game` = 'Minecraft' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/mta/' WHERE `game` = 'Multi Theft Auto' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/samp/' WHERE `game` = 'San Andreas: Multiplayer (SA-MP)' LIMIT 1" );
			
			query_basic( "UPDATE `".DBPREFIX."game` SET `queryport` = '27016' WHERE `game` = 'Call of Duty: Modern Warfare 3' LIMIT 1" );

		//---------------------------------------------------------+

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.4.5' WHERE `setting` = 'panelversion' LIMIT 1" );

		//---------------------------------------------------------+


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>