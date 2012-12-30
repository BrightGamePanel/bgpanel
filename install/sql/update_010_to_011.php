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
		-- Version 0.1.0 to Version 0.1.1
		-- 18/07/2012
		*/

		//---------------------------------------------------------+



		require_once("../libs/phpseclib/Crypt/AES.php");

		$aes = new Crypt_AES();
		$aes->setKeyLength(256);

		$crypt_key = hash('sha512', md5(str_shuffle(time())));
		$aes->setKey($crypt_key);

		if (is_writable("../.ssh/passphrase"))
		{
			$handle = fopen('../.ssh/passphrase', 'w');
			fwrite($handle, $crypt_key);
			fclose($handle);
		}

		//---------------------------------------------------------+

		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `hostname` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `os` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `date` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `kernel` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `arch` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `uptime` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `swap` text NOT NULL" );
		query_basic( "ALTER TABLE `".DBPREFIX."box` ADD `hdd` text NOT NULL" );

		//---------------------------------------------------------+

		$boxes = mysql_query( "SELECT `boxid`, `password` FROM `".DBPREFIX."box`" );

		while ($rowsBoxes = mysql_fetch_assoc($boxes))
		{
			$password = base64_decode($rowsBoxes['password']);
			$password = $aes->encrypt($password);
			query_basic( "UPDATE `".DBPREFIX."box` SET
				`password` = \"".$password."\",
				`hostname` = '~',
				`os` = '~',
				`date` = '~',
				`kernel` = '~',
				`arch` = '~',
				`uptime` = '~',
				`swap` = '~',
				`hdd` = '~' WHERE `boxid` = '".$rowsBoxes['boxid']."'" );
			unset($password);
		}

		unset($boxes);

		//---------------------------------------------------------+

		//Table structure for table "boxData"

			query_basic( "DROP TABLE IF EXISTS `".DBPREFIX."boxData`  ; " );
			query_basic( "
		CREATE TABLE `".DBPREFIX."boxData` (
		  `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `timestamp` text NOT NULL,
		  `boxids` text NOT NULL,
		  `boxnetstat` text NOT NULL,
		  `players` text NOT NULL,
		  `cpu` text NOT NULL,
		  `ram` text NOT NULL,
		  `loadavg` text NOT NULL,
		  `hdd` text NOT NULL,
		  PRIMARY KEY  (`id`)
		)
		ENGINE=MyISAM  ; " );

		//---------------------------------------------------------+

		//Dumping data for table "config"

		query_basic( "DELETE FROM `".DBPREFIX."config` WHERE `setting` = 'template' AND `value` = 'Bootstrap' LIMIT 1" );

			query_basic( "
		INSERT INTO `".DBPREFIX."config` (`setting`, `value`)
		VALUES
		  ('admintemplate', 'bootstrap.css'),
		  ('clienttemplate', 'bootstrap.css')  ; " );

		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.1.1' WHERE `setting` = 'panelversion' LIMIT 1" );
		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = 'default' WHERE `setting` = 'template' LIMIT 1" );

		//---------------------------------------------------------+

		//Dumping data for table "game"

			query_basic( "
		INSERT INTO `".DBPREFIX."game` (`game`, `status`, `maxslots`, `defaultport`, `cfg1name`, `cfg1`, `cfg2name`, `cfg2`, `cfg3name`, `cfg3`, `cfg4name`, `cfg4`, `cfg5name`, `cfg5`, `cfg6name`, `cfg6`, `cfg7name`, `cfg7`, `cfg8name`, `cfg8`, `cfg9name`, `cfg9`, `startline`, `querytype`, `queryport`, `cachedir`)
		VALUES
		  ('Call of Duty: Modern Warfare 3 (*)', 'Active', '18', '27015', 'net_queryPort', '27014', 'net_authPort', '8766', 'net_masterServerPort', '27016', 'Server CFG File', 'server.cfg', '', '', '', '', '', '', '', '', '', '', 'xvfb-run -a wine iw5mp_server.exe +set sv_config {cfg4} +set sv_maxclients {slots} +start_map_rotate +set net_ip {ip} +set net_port {port} +set net_queryPort {cfg1} +set net_authPort {cfg2} +set net_masterServerPort {cfg3} +set dedicated 2', 'callofdutymw3', '27014', ''),
		  ('Call of Duty 2 (*)', 'Active', '32', '28960', 'Server CFG', 'server.cfg', 'fs_homepath', '/home/user/cod2', 'fs_basepath', '/home/user/cod2', '', '', '', '', '', '', '', '', '', '', '', '', './cod2_lnxded +exec {cfg1} +map_rotate +set net_ip {ip} +set net_port {port} +set fs_homepath {cfg2} +set fs_basepath {cfg3} +set dedicated 2', 'callofduty2', '28960', ''),
		  ('Call of Duty: World at War (*)', 'Active', '32', '28960', 'Server CFG File', 'server.cfg', 'fs_homepath', '/home/user/codwaw', 'fs_basepath', '/home/user/codwaw', '', '', '', '', '', '', '', '', '', '', '', '', './codwaw_lnxded +exec {cfg1} +map_rotate +set net_ip {ip} +set net_port {port} +set fs_homepath {cfg2} +set fs_basepath {cfg3} +set sv_maxclients {slots} +set dedicated 2', 'callofdutywaw', '28960', ''),
		  ('Wolfenstein: Enemy Territory (*)', 'Active', '32', '27960', 'Server CFG File', 'server.cfg', 'fs_homepath', '/home/user/wolfet', 'fs_basepath', '/home/user/wolfet', '', '', '', '', '', '', '', '', '', '', '', '', './etded +exec {cfg1} +sv_maxclients {slots} +set fs_homepath {cfg2} +set fs_basepath {cfg3} +set net_port {port}', 'wolfet', '27960', ''),
		  ('ArmA: 2 (*)', 'Active', '64', '2302', 'Server CFG File', 'server.cfg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './server -config={cfg1} -netlog -port={port}', 'arma2', '2302', '')  ; " );


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>