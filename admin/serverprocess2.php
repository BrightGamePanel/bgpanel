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



$return = TRUE;


require("../configuration.php");
require("./include.php");
require("../includes/func.ssh2.inc.php");
require_once("../libs/phpseclib/Crypt/AES.php");


if (isset($_POST['actionOnMultipleServers']))
{
	$task = $_POST['actionOnMultipleServers'];
}


switch (@$task)
{
	case 'multipleStart':
		require_once("../libs/gameinstaller/gameinstaller.php");

		$servers = @$_POST['serverCheckedBoxes'];
		$startedServers = '<ul>';

		if (!isset($servers))
		{
			header( 'Location: server.php' );
			die();
		}

		foreach ($servers as $serverid)
		{
			// Security
			if (!is_numeric($serverid))
			{
				continue;
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				continue;
			}

			// Status Check
			$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
			if ($status['status'] == 'Inactive')
			{
				continue;
			}
			else if ($status['status'] == 'Pending')
			{
				continue;
			}
			else if ($status['panelstatus'] == 'Started')
			{
				continue;
			}

			$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
			$serverIP = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$server['ipid']."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );

			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);

			// Get SSH2 Object OR ERROR String
			$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
			if (!is_object($ssh))
			{
				continue;
			}

			$gameInstaller = new GameInstaller( $ssh );
			###
			$setGameServerPath = $gameInstaller->setGameServerPath( dirname($server['path']) );
			if ($setGameServerPath == FALSE) {
				continue;
			}
			###
			$opStatus = $gameInstaller->checkOperation( 'installGame' );
			if ($opStatus == TRUE) {
				continue;
			}

			//We prepare the startline
			$startline = $server['startline'];

			if (preg_match("#\{ip\}#", $startline))
			{
				$startline = preg_replace("#\{ip\}#", $serverIP['ip'], $startline); //IP replacement
			}
			if (preg_match("#\{port\}#", $startline))
			{
				$startline = preg_replace("#\{port\}#", $server['port'], $startline); //Port replacement
			}
			if (preg_match("#\{slots\}#", $startline))
			{
				$startline = preg_replace("#\{slots\}#", $server['slots'], $startline); //Slots replacement
			}

			$n = 1;
			while ($n < 10)
			{
				if (preg_match("#\{cfg".$n."\}#", $startline))
				{
					$startline = preg_replace("#\{cfg".$n."\}#", $server['cfg'.$n], $startline); //CFG replacement
				}
				++$n;
			}

			#-----------------+
			$cmd = "screen -AdmSL ".$server['screen']." nice -n ".$server['priority']." ".$startline;
			$ssh->exec('cd '.dirname($server['path']).'; '.$cmd."\n");
			#-----------------+

			if (preg_match("#^xvfb-run#", $server['startline']))
			{
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
				// Xvfb - virtual framebuffer X server for X - Xvfb pid backup
				sleep(3);
				$ssh->exec('cd '.dirname($server['path']).'; pgrep -u '.$box['login'].' Xvfb -n > xvfb.pid.tmp');
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			}

			$ssh->disconnect();

			//Mark the server as started
			query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Started' WHERE `serverid` = '".$serverid."'" );

			//Adding event to the database
			$message = 'Server Started : '.mysql_real_escape_string($server['name']);
			$startedServers .= "<li>{$server['name']}</li>";
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		}

		$startedServers .= '</ul>';

		$_SESSION['msg1'] = T_('The Following Servers Were Started:');
		$_SESSION['msg2'] = $startedServers;
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php" );
		die();
		break;

	case 'multipleStop':
		$servers = @$_POST['serverCheckedBoxes'];
		$stoppedServers = '<ul>';

		if (!isset($servers))
		{
			header( 'Location: server.php' );
			die();
		}

		foreach ($servers as $serverid)
		{
			// Security
			if (!is_numeric($serverid))
			{
				continue;
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				continue;
			}

			// Status Check
			$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
			if ($status['status'] == 'Inactive')
			{
				continue;
			}
			else if ($status['status'] == 'Pending')
			{
				continue;
			}
			else if ($status['panelstatus'] == 'Stopped')
			{
				continue;
			}

			$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
			$serverIP = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$server['ipid']."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );

			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);

			// Get SSH2 Object OR ERROR String
			$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
			if (!is_object($ssh))
			{
				continue;
			}

			$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$server['screen']."$'"."\n" );
			$session = trim($session);

			#-----------------+
			$cmd = "screen -S ".$session." -X quit"."\n";
			$ssh->exec($cmd."\n");
			#-----------------+

			if (preg_match("#^xvfb-run#", $server['startline']))
			{
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
				// Xvfb - virtual framebuffer X server for X - TASK KILLER
				$ssh->exec('cd '.dirname($server['path']).'; kill $(cat xvfb.pid.tmp); rm xvfb.pid.tmp');
				sleep(3);
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			}

			$ssh->disconnect();

			//Mark the server as stopped
			query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Stopped' WHERE `serverid` = '".$serverid."'" );
			###
			//Adding event to the database
			$message = 'Server Stopped : '.mysql_real_escape_string($server['name']);
			$stoppedServers .= "<li>{$server['name']}</li>";
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		}

		$stoppedServers .= '</ul>';

		$_SESSION['msg1'] = T_('The Following Servers Were Stopped:');
		$_SESSION['msg2'] = $stoppedServers;
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php" );
		die();
		break;

	case 'multipleReboot':
		$servers = @$_POST['serverCheckedBoxes'];
		$rebootedServers = '<ul>';

		if (!isset($servers))
		{
			header( 'Location: server.php' );
			die();
		}

		foreach ($servers as $serverid)
		{
			// Security
			if (!is_numeric($serverid))
			{
				continue;
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				continue;
			}

			// Status Check
			$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
			if ($status['status'] == 'Inactive')
			{
				continue;
			}
			else if ($status['status'] == 'Pending')
			{
				continue;
			}
			else if ($status['panelstatus'] == 'Stopped')
			{
				continue;
			}

			$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
			$serverIP = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$server['ipid']."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );

			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);

			// Get SSH2 Object OR ERROR String
			$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
			if (!is_object($ssh))
			{
				continue;
			}

			$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$server['screen']."$'"."\n" );
			$session = trim($session);

			#-----------------+
			$cmd = "screen -S ".$session." -X quit"."\n";
			$ssh->exec($cmd."\n");
			#-----------------+

			if (preg_match("#^xvfb-run#", $server['startline']))
			{
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
				// Xvfb - virtual framebuffer X server for X - TASK KILLER
				$ssh->exec('cd '.dirname($server['path']).'; kill $(cat xvfb.pid.tmp); rm xvfb.pid.tmp');
				sleep(3);
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			}

			query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Stopped' WHERE `serverid` = '".$serverid."'" );

			usleep(2000);

			//We prepare the startline
			$startline = $server['startline'];

			if (preg_match("#\{ip\}#", $startline))
			{
				$startline = preg_replace("#\{ip\}#", $serverIP['ip'], $startline); //IP replacement
			}
			if (preg_match("#\{port\}#", $startline))
			{
				$startline = preg_replace("#\{port\}#", $server['port'], $startline); //Port replacement
			}
			if (preg_match("#\{slots\}#", $startline))
			{
				$startline = preg_replace("#\{slots\}#", $server['slots'], $startline); //Slots replacement
			}

			$n = 1;
			while ($n < 10)
			{
				if (preg_match("#\{cfg".$n."\}#", $startline))
				{
					$startline = preg_replace("#\{cfg".$n."\}#", $server['cfg'.$n], $startline); //CFG replacement
				}
				++$n;
			}

			#-----------------+
			$cmd = "screen -AdmSL ".$server['screen']." nice -n ".$server['priority']." ".$startline;
			$ssh->exec('cd '.dirname($server['path']).'; '.$cmd."\n");
			#-----------------+

			if (preg_match("#^xvfb-run#", $server['startline']))
			{
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
				// Xvfb - virtual framebuffer X server for X - Xvfb pid backup
				sleep(3);
				$ssh->exec('cd '.dirname($server['path']).'; pgrep -u '.$box['login'].' Xvfb -n > xvfb.pid.tmp');
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			}

			$ssh->disconnect();

			//Mark the server as started
			query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Started' WHERE `serverid` = '".$serverid."'" );

			//Adding event to the database
			$message = 'Server Rebooted : '.mysql_real_escape_string($server['name']);
			$rebootedServers .= "<li>{$server['name']}</li>";
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		}

		$rebootedServers .= '</ul>';

		$_SESSION['msg1'] = T_('The Following Servers Were Rebooted:');
		$_SESSION['msg2'] = $rebootedServers;
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php" );
		die();
		break;

	case 'multipleUpdate':
		require_once("../libs/gameinstaller/gameinstaller.php");

		$servers = @$_POST['serverCheckedBoxes'];
		$updatedServers = '<ul>';

		if (!isset($servers))
		{
			header( 'Location: server.php' );
			die();
		}

		foreach ($servers as $serverid)
		{
			// Security
			if (!is_numeric($serverid))
			{
				continue;
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				continue;
			}

			// Status Check
			$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
			if ($status['status'] == 'Inactive')
			{
				continue;
			}
			else if ($status['status'] == 'Pending')
			{
				continue;
			}
			else if ($status['panelstatus'] == 'Started')
			{
				continue;
			}

			$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
			$game = query_fetch_assoc( "SELECT `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `gameid` = '".$server['gameid']."' LIMIT 1" );

			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);

			// Get SSH2 Object OR ERROR String
			$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
			if (!is_object($ssh))
			{
				continue;
			}

			$gameInstaller = new GameInstaller( $ssh );
			###
			$setGame = $gameInstaller->setGame( $game['game'] );
			if ($setGame == FALSE) {
				continue;
			}
			$setRepoPath = $gameInstaller->setRepoPath( $game['cachedir'] );
			if ($setRepoPath == FALSE) {
				continue;
			}
			$repoCacheInfo = $gameInstaller->getCacheInfo( $game['cachedir'] );
			if ($repoCacheInfo['status'] != 'Ready') {
				continue;
			}
			$setGameServerPath = $gameInstaller->setGameServerPath( dirname($server['path']) );
			if ($setGameServerPath == FALSE) {
				continue;
			}
			$opStatus = $gameInstaller->checkOperation( 'updateGame' );
			if ($opStatus == TRUE) {
				continue;
			}
			$updateGameServer = $gameInstaller->updateGameServer( );
			if ($updateGameServer == FALSE) {
				continue;
			}

			$ssh->disconnect();

			//Adding event to the database
			$message = 'Server Update : '.mysql_real_escape_string($server['name']);
			$updatedServers .= "<li>{$server['name']}</li>";
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		}

		$updatedServers .= '</ul>';

		$_SESSION['msg1'] = T_('The Following Servers Are Being Updated:');
		$_SESSION['msg2'] = $updatedServers;
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>