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



$return = TRUE;


require("configuration.php");
require("include.php");
require_once("./libs/phpseclib/SSH2.php");
require_once("./libs/phpseclib/Crypt/AES.php");


if (isset($_POST['task']))
{
	$task = mysql_real_escape_string($_POST['task']);
}
else if (isset($_GET['task']))
{
	$task = mysql_real_escape_string($_GET['task']);
}


switch (@$task)
{
	case 'getserverlog':
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= 'No ServerID specified !';
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= 'Invalid ServerID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= 'Invalid ServerID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
		if ($status['status'] == 'Inactive')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server has been disabled.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is pending.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		// Rights
		$checkGroup = checkClientGroup($server['groupid'], $_SESSION['clientid']);
		if ($checkGroup == FALSE)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'This is not your server!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: server.php?id=".urlencode($serverid) );
			die();
		}
		###
		$cmd = "cat screenlog.0";
		$output = $ssh->exec('cd '.$server['homedir'].'; '.$cmd."\n");
		###
		//Adding event to the database
		$message = mysql_real_escape_string($server['name']).' : screenlog downloaded';
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".$_SESSION['clientfirstname']." ".$_SESSION['clientlastname']."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="'.$server['screen'].'_'.date('Y-m-d').'.screenlog"');
		###
		echo $output;
		###
		die();
		break;

	case 'serverstart':
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= 'No ServerID specified !';
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= 'Invalid ServerID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= 'Invalid ServerID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
		if ($status['status'] == 'Inactive')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server has been disabled.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is pending.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is already running!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		// Rights
		$checkGroup = checkClientGroup($server['groupid'], $_SESSION['clientid']);
		if ($checkGroup == FALSE)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'This is not your server!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: server.php?id=".urlencode($serverid) );
			die();
		}
		###
		//We prepare the startline
		$startline = $server['startline'];
		###
		if (preg_match("#\{ip\}#", $startline))
		{
			$startline = preg_replace("#\{ip\}#", $box['ip'], $startline); //IP replacement
		}
		if (preg_match("#\{port\}#", $startline))
		{
			$startline = preg_replace("#\{port\}#", $server['port'], $startline); //Port replacement
		}
		if (preg_match("#\{slots\}#", $startline))
		{
			$startline = preg_replace("#\{slots\}#", $server['slots'], $startline); //Slots replacement
		}
		###
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
		$ssh->exec('cd '.$server['homedir'].'; '.$cmd."\n");
		#-----------------+
		if (preg_match("#^xvfb-run#", $server['startline']))
		{
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			/**
			 *
			 *	Xvfb - virtual framebuffer X server for X
			 *	Xvfb pid backup by warhawk3407 and sUpEr g2
			 *
			 */
			sleep(3);
			$ssh->exec('cd '.$server['homedir'].'; pgrep -u '.$box['login'].' Xvfb -n > xvfb.pid.tmp');
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
		}
		//Mark the server as started
		query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Started' WHERE `serverid` = '".$serverid."'" );
		###
		//Adding event to the database
		$message = 'Server Started : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".$_SESSION['clientfirstname']." ".$_SESSION['clientlastname']."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Successfully Started!';
		$_SESSION['msg2'] = 'With command : '.htmlspecialchars($startline, ENT_QUOTES);
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php?id=".urlencode($serverid) );
		die();
		break;

	case 'serverstop':
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= 'No ServerID specified !';
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= 'Invalid ServerID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= 'Invalid ServerID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
		if ($status['status'] == 'Inactive')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server has been disabled.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is pending.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['panelstatus'] == 'Stopped')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is already stopped!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		// Rights
		$checkGroup = checkClientGroup($server['groupid'], $_SESSION['clientid']);
		if ($checkGroup == FALSE)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'This is not your server!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: server.php?id=".urlencode($serverid) );
			die();
		}
		###
		$output = $ssh->exec("screen -ls | grep ".$server['screen']."\n");
		$output = trim($output);
		$session = explode("\t", $output);
		#-----------------+
		$cmd = "screen -S ".$session[0]." -X quit \n";
		$ssh->exec($cmd."\n");
		#-----------------+
		if (preg_match("#^xvfb-run#", $server['startline']))
		{
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			/**
			 *
			 *	Xvfb - virtual framebuffer X server for X
			 *	TASK KILLER by warhawk3407 and sUpEr g2
			 *
			 */
			$ssh->exec('cd '.$server['homedir'].'; kill $(cat xvfb.pid.tmp); rm xvfb.pid.tmp');
			sleep(3);
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
		}
		//Mark the server as stopped
		query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Stopped' WHERE `serverid` = '".$serverid."'" );
		###
		//Adding event to the database
		$message = 'Server Stopped : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".$_SESSION['clientfirstname']." ".$_SESSION['clientlastname']."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Successfully Stopped!';
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php?id=".urlencode($serverid) );
		die();
		break;

	case 'serverreboot':
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= 'No ServerID specified !';
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= 'Invalid ServerID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= 'Invalid ServerID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" );
		if ($status['status'] == 'Inactive')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server has been disabled.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is pending.';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['panelstatus'] == 'Stopped')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server is already stopped!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		// Rights
		$checkGroup = checkClientGroup($server['groupid'], $_SESSION['clientid']);
		if ($checkGroup == FALSE)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'This is not your server!';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: server.php?id=".urlencode($serverid) );
			die();
		}
		###
		$output = $ssh->exec("screen -ls | grep ".$server['screen']."\n");
		$output = trim($output);
		$session = explode("\t", $output);
		#-----------------+
		$cmd = "screen -S ".$session[0]." -X quit \n";
		$ssh->exec($cmd."\n");
		#-----------------+
		if (preg_match("#^xvfb-run#", $server['startline']))
		{
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			/**
			 *
			 *	Xvfb - virtual framebuffer X server for X
			 *	TASK KILLER by warhawk3407 and sUpEr g2
			 *
			 */
			$ssh->exec('cd '.$server['homedir'].'; kill $(cat xvfb.pid.tmp); rm xvfb.pid.tmp');
			sleep(3);
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
		}
		###
		query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Stopped' WHERE `serverid` = '".$serverid."'" );
		###
		usleep(2000);
		###
		//We prepare the startline
		$startline = $server['startline'];
		###
		if (preg_match("#\{ip\}#", $startline))
		{
			$startline = preg_replace("#\{ip\}#", $box['ip'], $startline); //IP replacement
		}
		if (preg_match("#\{port\}#", $startline))
		{
			$startline = preg_replace("#\{port\}#", $server['port'], $startline); //Port replacement
		}
		if (preg_match("#\{slots\}#", $startline))
		{
			$startline = preg_replace("#\{slots\}#", $server['slots'], $startline); //Slots replacement
		}
		###
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
		$ssh->exec('cd '.$server['homedir'].'; '.$cmd."\n");
		#-----------------+
		if (preg_match("#^xvfb-run#", $server['startline']))
		{
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
			/**
			 *
			 *	Xvfb - virtual framebuffer X server for X
			 *	Xvfb pid backup by warhawk3407 and sUpEr g2
			 *
			 */
			sleep(3);
			$ssh->exec('cd '.$server['homedir'].'; pgrep -u '.$box['login'].' Xvfb -n > xvfb.pid.tmp');
			//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
		}
		###
		query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Started' WHERE `serverid` = '".$serverid."'" );
		###
		//Adding event to the database
		$message = 'Server Rebooted : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".$_SESSION['clientfirstname']." ".$_SESSION['clientlastname']."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Successfully Rebooted!';
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'info';
		header( "Location: server.php?id=".urlencode($serverid) );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>