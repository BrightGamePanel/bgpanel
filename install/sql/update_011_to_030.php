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
		-- Version 0.1.1 to Version 0.3.0
		-- 19/09/2012
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+

		$q = mysql_query( "SELECT * FROM `".DBPREFIX."group`" );

		while($rowsQ = mysql_fetch_assoc($q))
		{
			$backup[] = $rowsQ;
		}


		query_basic( "TRUNCATE `".DBPREFIX."boxData`" );

		//---------------------------------------------------------+

		//Table structure for table "group"

			query_basic( "DROP TABLE IF EXISTS `".DBPREFIX."group`  ; " );
			query_basic( "
		CREATE TABLE `".DBPREFIX."group` (
		  `groupid` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `name` text NOT NULL,
		  `description` text NULL,
		  PRIMARY KEY  (`groupid`)
		)
		ENGINE=MyISAM  ; " );

		//Dumping data for table "group"

		$i = 0;
		while(@array_key_exists($i, @$backup))
		{
				query_basic( "
			INSERT INTO `".DBPREFIX."group` (`groupid`, `name`)
			VALUES
			  ('".$backup[$i]['groupid']."', '".$backup[$i]['name']."')  ; " );

			$i++;
		}

		//---------------------------------------------------------+

		//Table structure for table "groupMember"

			query_basic( "DROP TABLE IF EXISTS `".DBPREFIX."groupMember`  ; " );
			query_basic( "
		CREATE TABLE `".DBPREFIX."groupMember` (
		  `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `clientid` int(8) NULL,
		  `groupids` text NULL,
		  PRIMARY KEY  (`id`)
		)
		ENGINE=MyISAM  ; " );

		//Dumping data for table "groupMember"

		$i = 0;
		while(@array_key_exists($i, @$backup)) // For each group
		{
			$n = 1;
			while ($n < 10) // For each member
			{
				//$adminid = NULL;
				$clientid = NULL;

				// Retrieve ID

				/*
				if ($backup[$i]['member'.$n.'rank'] == 'admin')
				{
					$adminid = $backup[$i]['member'.$n];
				}
				else if
				*/
				if ($backup[$i]['member'.$n.'rank'] == 'client')
				{
					$clientid = $backup[$i]['member'.$n];
				}

				/*
				if ($adminid != NULL) //The member is an admin
				{
					if (mysql_num_rows(mysql_query( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `adminid` = '".$adminid."'" )) == 0) // Admin doesn't exists
					{
							query_basic( "
						INSERT INTO `".DBPREFIX."groupMember` (`adminid`)
						VALUES
						  ('".$adminid."')  ; " );
					}

					if (mysql_num_rows(mysql_query( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `adminid` = '".$adminid."'" )) == 1) // Admin exists
					{
						// We have to retrieve from the database the groups which belongs to this administrator

						$admin = mysql_query( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `adminid` = '".$adminid."'" );

						while ($rowsAdmin = mysql_fetch_assoc($admin))
						{
							$groupids = $rowsAdmin['groupids']; // Existing groups
							$groupid = $backup[$i]['groupid']; // Current group

							$groupids .= $groupid; // Add the current group to the list
						}

						// Each group ID is seperated by a ";" (Comma-seperated values)
						query_basic( "UPDATE `".DBPREFIX."groupMember` SET `groupids` = '".$groupids.";' WHERE `adminid` = '".$adminid."' LIMIT 1" );

						unset($admin, $groupid, $groupids);
					}
				}
				else if
				*/
				if ($clientid != NULL) // The member is a client
				{
					if (mysql_num_rows(mysql_query( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" )) == 0) // Client doesn't exists
					{
							query_basic( "
						INSERT INTO `".DBPREFIX."groupMember` (`clientid`)
						VALUES
						  ('".$clientid."')  ; " );
					}

					if (mysql_num_rows(mysql_query( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" )) == 1) // Client exists
					{
						// We have to retrieve from the database the groups which belongs to this client

						$client = mysql_query( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" );

						while ($rowsClient = mysql_fetch_assoc($client))
						{
							$groupids = $rowsClient['groupids']; // Existing groups
							$groupid = $backup[$i]['groupid']; // Current group

							$groupids .= $groupid; // Add the current group to the list
						}

						// Each group ID is seperated by a ";" (Comma-seperated values)
						query_basic( "UPDATE `".DBPREFIX."groupMember` SET `groupids` = '".$groupids.";' WHERE `clientid` = '".$clientid."' LIMIT 1" );

						unset($client, $groupid, $groupids);
					}
				}

				//unset($clientid, $adminid);
				unset($clientid);

				++$n;
			}

			$i++;
		}
		unset($i, $n);

		//---------------------------------------------------------+

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.3.0' WHERE `setting` = 'panelversion' LIMIT 1" );

		//---------------------------------------------------------+

		//Dumping data for table "game"

			query_basic( "
		INSERT INTO `".DBPREFIX."game` (`game`, `status`, `maxslots`, `defaultport`, `cfg1name`, `cfg1`, `cfg2name`, `cfg2`, `cfg3name`, `cfg3`, `cfg4name`, `cfg4`, `cfg5name`, `cfg5`, `cfg6name`, `cfg6`, `cfg7name`, `cfg7`, `cfg8name`, `cfg8`, `cfg9name`, `cfg9`, `startline`, `querytype`, `queryport`, `cachedir`)
		VALUES
		  ('Garrysmod (*)', 'Active', '16', '27015', 'Default Map', 'gm_construct', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './srcds_run -game garrysmod -ip {ip} -port {port} -maxplayers {slots} +map {cfg1} -nohltv -autoupdate', 'source', '27015', ''),
		  ('Counter-Strike: Global Offensive (*)', 'Active', '24', '27015', 'Default Map', 'cs_italy', 'Map Group', 'mg_hostage', 'Game Type', '0', 'Game Mode', '0', 'Tickrate', '100', '', '', '', '', '', '', '', '', './srcds_run -game csgo -console -usercon -secure -nohltv -tickrate {cfg5} +net_public_adr {ip} +hostport {port} -maxplayers_override {slots} +map {cfg1} +mapgroup {cfg2} +game_type {cfg3} +game_mode {cfg4}', 'source', '27015', '')  ; " );

		//---------------------------------------------------------+

		//Table structure for table "script"

			query_basic( "DROP TABLE IF EXISTS `".DBPREFIX."script`  ; " );
			query_basic( "
		CREATE TABLE `".DBPREFIX."script` (
		  `scriptid` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `groupid` int(8) UNSIGNED NULL,
		  `boxid` int(8) UNSIGNED NOT NULL,
		  `catid` int(8) UNSIGNED NOT NULL,
		  `name` text NOT NULL,
		  `description` text NULL,
		  `status` text NOT NULL,
		  `panelstatus` text NULL,
		  `startline` text NOT NULL,
		  `filename` text NOT NULL,
		  `homedir` text NOT NULL,
		  `type` int(1) NOT NULL,
		  `screen` text NULL,
		  PRIMARY KEY  (`scriptid`)
		)
		ENGINE=MyISAM  ; " );

		//---------------------------------------------------------+

		//Table structure for table "scriptCat"

			query_basic( "DROP TABLE IF EXISTS `".DBPREFIX."scriptCat`  ; " );
			query_basic( "
		CREATE TABLE `".DBPREFIX."scriptCat` (
		  `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `name` text NOT NULL,
		  `description` text NULL,
		  PRIMARY KEY  (`id`)
		)
		ENGINE=MyISAM  ; " );


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>