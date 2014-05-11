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
		-- Version 0.4.5 to Version 0.4.7
		-- 05/05/2014
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+
		// AJXP Update

		if (is_writable( "../ajxp/data/plugins/boot.conf/bootstrap.json" ))
		{
			$crypt_key = file_get_contents("../.ssh/passphrase");
			$api_key = substr($crypt_key, (strlen($crypt_key) / 2));

			$bootstrap = file_get_contents( "../ajxp/data/plugins/boot.conf/bootstrap.json" );
			$bootstrap = str_replace( "\"SECRET\":\"void\"", "\"SECRET\":\"".$api_key."\"", $bootstrap );

			$handle = fopen( "../ajxp/data/plugins/boot.conf/bootstrap.json" , 'w' );
			fwrite($handle, $bootstrap);
			fclose($handle);
			unset($handle);
		}

		//---------------------------------------------------------+

		//Updating structure for table "box"

			query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `path` text NULL" );

			$boxes = mysql_query( "SELECT * FROM `".DBPREFIX."box`" );

			while ($rowsBoxes = mysql_fetch_assoc($boxes))
			{
				//User Path
				if ($rowsBoxes['login'] == 'root') {
					$path = '/root';
				}
				else {
					$path = '/home/'.$rowsBoxes['login'];
				}

				//Update DB
				query_basic( "UPDATE `".DBPREFIX."box` SET `path` = '".$path."' WHERE `boxid` = '".$rowsBoxes['boxid']."' LIMIT 1" );
			}

			unset($boxes);

		//---------------------------------------------------------+

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.4.7' WHERE `setting` = 'panelversion' LIMIT 1" );

		//---------------------------------------------------------+

		//Updating data for table "game"

			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/arma/' WHERE `game` = 'ArmA: Armed Assault' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/arma2/' WHERE `game` = 'ArmA: 2' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/bf2/' WHERE `game` = 'Battlefield 2' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/cod2/' WHERE `game` = 'Call of Duty 2' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/cod4/' WHERE `game` = 'Call of Duty 4: Modern Warfare' LIMIT 1" );
			query_basic( "UPDATE `".DBPREFIX."game` SET `cachedir` = '~/game-repositories/wolfet/' WHERE `game` = 'Wolfenstein: Enemy Territory' LIMIT 1" );

		//---------------------------------------------------------+


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>