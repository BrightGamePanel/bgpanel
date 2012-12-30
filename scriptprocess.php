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


require("./configuration.php");
require("./include.php");
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
	case 'scriptstart':
		$scriptid = $_GET['scriptid'];
		###
		$error = '';
		###
		if (empty($scriptid))
		{
			$error .= 'No ScriptID specified !';
		}
		else
		{
			if (!is_numeric($scriptid))
			{
				$error .= 'Invalid ScriptID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
			{
				$error .= 'Invalid ScriptID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: index.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" );
		if (($status['status'] == 'Inactive'))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script has been disabled. ';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script is pending. ';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		else
		{
			$script = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$script['boxid']."' LIMIT 1" );
			###
			// Rights
			$checkGroup = checkClientGroup($script['groupid'], $_SESSION['clientid']);
			if ($checkGroup == FALSE)
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'This is not your script!';
				$_SESSION['msg-type'] = 'error';
				header( 'Location: index.php' );
				die();
			}
			###
			if ($script['type'] == '0') // Nohup case
			{
				$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
				$aes = new Crypt_AES();
				$aes->setKeyLength(256);
				$aes->setKey(CRYPT_KEY);
				if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
				{
					$_SESSION['msg1'] = 'Connection Error!';
					$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}

				//We try to retrieve screen name ($session)
				$output = $ssh->exec("screen -ls | grep ".preg_replace('#[^a-zA-Z0-9]#', "_", $script['name'])."\n");
				$output = trim($output);
				$session = explode("\t", $output);
				unset($output);

				//We verify that another instance of this script is not running
				if (!empty($session[0]))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'This script still running: aborting.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}

				//We prepare the startline
				$startline = $script['startline'];
				###
				if (preg_match("#\{script\}#", $startline))
				{
					$startline = preg_replace("#\{script\}#", $script['filename'], $startline); //SCRIPT replacement
				}
				#-----------------+
				$cmd = "screen -AdmSL ".preg_replace('#[^a-zA-Z0-9]#', "_", $script['name'])." ".$startline;
				$ssh->exec('cd '.$script['homedir'].'; rm screenlog.0; '.$cmd."\n");
				#-----------------+
			}
			else // Screen case
			{
				if (($script['panelstatus'] == 'Started'))
				{
					$_SESSION['msg1'] = 'Validation Error!';
					$_SESSION['msg2'] = 'The script has been already started! ';
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
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}
				###
				//We prepare the startline
				$startline = $script['startline'];
				###
				if (preg_match("#\{script\}#", $startline))
				{
					$startline = preg_replace("#\{script\}#", $script['filename'], $startline); //SCRIPT replacement
				}
				#-----------------+
				$cmd = "screen -AdmSL ".$script['screen']." ".$startline;
				$ssh->exec('cd '.$script['homedir'].'; '.$cmd."\n");
				#-----------------+
				//Mark the script as started
				query_basic( "UPDATE `".DBPREFIX."script` SET `panelstatus` = 'Started' WHERE `scriptid` = '".$scriptid."'" );
			}
			###
			//Adding event to the database
			$message = 'Script Launched : '.mysql_real_escape_string($script['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `scriptid` = '".$scriptid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['clientusername'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			###
			$_SESSION['msg1'] = 'Script Successfully Launched!';
			$_SESSION['msg2'] = '';
			$_SESSION['msg-type'] = 'info';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
			break;
		}

	case 'scriptstop':
		$scriptid = $_GET['scriptid'];
		###
		$error = '';
		###
		if (empty($scriptid))
		{
			$error .= 'No ScriptID specified !';
		}
		else
		{
			if (!is_numeric($scriptid))
			{
				$error .= 'Invalid ScriptID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
			{
				$error .= 'Invalid ScriptID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: index.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" );
		if (($status['status'] == 'Inactive'))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script has been disabled. ';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script is pending. ';
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		else
		{
			$script = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$script['boxid']."' LIMIT 1" );
			###
			// Rights
			$checkGroup = checkClientGroup($script['groupid'], $_SESSION['clientid']);
			if ($checkGroup == FALSE)
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'This is not your script!';
				$_SESSION['msg-type'] = 'error';
				header( 'Location: index.php' );
				die();
			}
			###
			if ($script['type'] == '0') // Nohup case
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'Non-interactive scripts are unstoppable!';
				$_SESSION['msg-type'] = 'error';
				header( 'Location: index.php' );
				die();
			}
			// Else : Screen case
			if (($script['panelstatus'] == 'Stopped'))
			{
				$_SESSION['msg1'] = 'Validation Error!';
				$_SESSION['msg2'] = 'The script has been already stopped! ';
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
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			###
			$output = $ssh->exec("screen -ls | grep ".$script['screen']."\n");
			$output = trim($output);
			$session = explode("\t", $output);
			#-----------------+
			$cmd = "screen -S ".$session[0]." -X quit; cd ".$script['homedir']."; rm screenlog.0";
			$ssh->exec($cmd."\n");
			#-----------------+
			//Mark the script as stopped
			query_basic( "UPDATE `".DBPREFIX."script` SET `panelstatus` = 'Stopped' WHERE `scriptid` = '".$scriptid."'" );
			###
			//Adding event to the database
			$message = 'Script Stopped : '.mysql_real_escape_string($script['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `scriptid` = '".$scriptid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['clientusername'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			###
			$_SESSION['msg1'] = 'Script Successfully Stopped!';
			$_SESSION['msg2'] = '';
			$_SESSION['msg-type'] = 'info';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
			break;
		}

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.