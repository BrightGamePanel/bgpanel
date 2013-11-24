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
		-- Version 0.4.0 to Version 0.4.1
		-- 22/04/2013
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+

		//Updating structure for table "server"
			query_basic( "ALTER TABLE `".DBPREFIX."server` CHANGE `homedir` `path` TEXT NOT NULL" );

		//Updating data for table "server"

		require_once("./../libs/phpseclib/SSH2.php");
		require_once("./../includes/func.ssh2.inc.php");
		define('CRYPT_KEY', file_get_contents("./../.ssh/passphrase"));

		$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server`" );

		while ($rowsServers = mysql_fetch_assoc($servers))
		{
			if ($rowsServers['status'] != 'Pending') {
				if ($rowsServers['panelstatus'] != 'Stopped') {
					// Send Stop Sequence

					$serverid = $rowsServers['serverid'];
					$server = $rowsServers;
					###
					$box = mysql_fetch_assoc( mysql_query( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" ) );
					###
					$aes = new Crypt_AES();
					$aes->setKeyLength(256);
					$aes->setKey(CRYPT_KEY);
					###
					$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
					if (is_object($ssh))
					{
						$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$server['screen']."$'"."\n" );
						$session = trim($session);
						#-----------------+
						$cmd = "screen -S ".$session." -X quit"."\n";
						$ssh->exec($cmd."\n");
						#-----------------+
						if (preg_match("#^xvfb-run#", $server['startline']))
						{
							$ssh->exec('cd '.dirname($server['path']).'; kill $(cat xvfb.pid.tmp); rm xvfb.pid.tmp');
						}
						#-----------------+
						$ssh->disconnect();
					}

					//Mark the server as stopped
					query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Stopped' WHERE `serverid` = '".$serverid."'" );
					$message = 'Server Stopped : '.mysql_real_escape_string($server['name']);
					query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = 'BGPanel Updater', `ip` = '127.0.0.1'" );

					// End: Stop Sequence
				}

				// Server must be re-validated
				query_basic( "UPDATE `".DBPREFIX."server` SET `status` = 'Pending' WHERE `serverid` = '".$rowsServers['serverid']."' LIMIT 1" );
			}
		}

		unset($servers);

		//---------------------------------------------------------+

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.4.1' WHERE `setting` = 'panelversion' LIMIT 1" );

		//---------------------------------------------------------+


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>