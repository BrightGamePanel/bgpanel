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
		-- Version 0.3.0 to Version 0.3.5
		-- 13/04/2013
		*/

		//---------------------------------------------------------+



		//---------------------------------------------------------+

		//Updating passphrase file if this one is the default one

		$line = file_get_contents("../.ssh/passphrase");

		if (preg_match('#isEmpty = TRUE;#', $line))
		{
			$oldPassphrase = 'isEmpty = TRUE;';
			$newPassphrase = hash('sha512', md5(str_shuffle(time())));

			if (is_writable("../.ssh/passphrase"))
			{
				$handle = fopen('../.ssh/passphrase', 'w');
				fwrite($handle, $newPassphrase);
				fclose($handle);
			}

			//---------------------------------------------------------+

			require_once("../libs/phpseclib/Crypt/AES.php");

			$aes = new Crypt_AES();
			$aes->setKeyLength(256);

			//---------------------------------------------------------+

			$boxes = mysql_query( "SELECT `boxid`, `password` FROM `".DBPREFIX."box`" );

			while ($rowsBoxes = mysql_fetch_assoc($boxes))
			{
				$aes->setKey($oldPassphrase);
				$password = $aes->decrypt($rowsBoxes['password']);

				$aes->setKey($newPassphrase);
				$password = $aes->encrypt($password);

				query_basic( "UPDATE `".DBPREFIX."box` SET `password` = '".mysql_real_escape_string($password)."' WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

				unset($password);
			}

			unset($boxes);
		}

		unset($line);

		//---------------------------------------------------------+

		//Updating structure for table "log"

			query_basic( "ALTER TABLE `".DBPREFIX."log` ADD `scriptid` int(8) UNSIGNED NULL" );

		//---------------------------------------------------------+

		//Updating structure for table "script"

			query_basic( "ALTER TABLE `".DBPREFIX."script` CHANGE `daemon` `type` int(1) NOT NULL " );

		//Updating data for table "config"

			query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '0.3.5' WHERE `setting` = 'panelversion' LIMIT 1" );

			query_basic( "
		INSERT INTO `".DBPREFIX."config` (`setting`, `value`)
		VALUES
		  ('maintenance', '0')  ; " );

		//---------------------------------------------------------+

		//Dumping data for table "game"

			query_basic( "
		INSERT INTO `".DBPREFIX."game` (`game`, `status`, `maxslots`, `defaultport`, `cfg1name`, `cfg1`, `cfg2name`, `cfg2`, `cfg3name`, `cfg3`, `cfg4name`, `cfg4`, `cfg5name`, `cfg5`, `cfg6name`, `cfg6`, `cfg7name`, `cfg7`, `cfg8name`, `cfg8`, `cfg9name`, `cfg9`, `startline`, `querytype`, `queryport`, `cachedir`)
		VALUES
		  ('ArmA: Armed Assault (*)', 'Active', '64', '2302', 'Server CFG File', 'server.cfg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './server -config={cfg1} -netlog -port={port}', 'arma', '2302', ''),
		  ('Battlefield 2 (*)', 'Active', '64', '16567', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './start.sh', 'bf2', '29900', ''),
		  ('Battlefield 1942 (*)', 'Active', '64', '14567', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './start.sh +statusMonitor 1', 'bf1942', '23000', ''),
		  ('Multi Theft Auto (*)', 'Active', '128', '22003', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './mta-server', 'mta', '22126', ''),
		  ('San Andreas: Multiplayer (SA-MP) (*)', 'Active', '128', '7777', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './samp03svr', 'samp', '7777', ''),
		  ('Urban Terror (*)', 'Active', '32', '27960', 'Server CFG File', 'server.cfg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', './ioUrTded.i386 +set fs_game q3ut4 +set net_port {port} +set com_hunkmegs 128 +exec {cfg1} +set dedicated 2', 'urbanterror', '27960', '')  ; " );

		//---------------------------------------------------------+


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


		mysql_close($mysql_link);
	}
}


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>