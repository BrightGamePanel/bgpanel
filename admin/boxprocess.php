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


require("../configuration.php");
require("./include.php");
require_once("../libs/phpseclib/SSH2.php");
require_once("../libs/phpseclib/Crypt/AES.php");


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
	case 'boxadd':
		$name = mysql_real_escape_string($_POST['name']);
		$ip = mysql_real_escape_string($_POST['ip']);
		$login = mysql_real_escape_string($_POST['login']);
		$password = mysql_real_escape_string($_POST['password']);
		$password2 = mysql_real_escape_string($_POST['password2']);
		$sshport = mysql_real_escape_string($_POST['sshport']);
		$notes = mysql_real_escape_string($_POST['notes']);
		if (isset($_POST['verify'])) {
			$verify = 'on';
		} else {
			$verify = '';
		}
		###
		//Used to fill in the blanks of the form
		$_SESSION['name'] = $name;
		$_SESSION['ip'] = $ip;
		$_SESSION['login'] = $login;
		$_SESSION['sshport'] = $sshport;
		$_SESSION['notes'] = $notes;
		###
		//Check the inputs. Output an error if the validation failed
		$nameLength = strlen($name);
		###
		$error = '';
		###
		if ($nameLength < 2)
		{
			$error .= 'Box Name is too short (2 Chars min.). ';
		}
		if (!validateIP($ip))
		{
			$error .= 'Invalid IP. ';
		}
		else if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box` WHERE `ip` = '".$ip."' && `login` = '".$login."'" ) != 0)
		{
			$error .= 'This IP is already in use with the same login ! ';
		}
		if (empty($login))
		{
			$error .= 'No SSH login ! ';
		}
		if (empty($password))
		{
			$error .= 'SSH Password is not set. ';
		}
		else if ($password != $password2)
		{
			$error .= "SSH Passwords don't match. ";
		}
		if (empty($sshport))
		{
			$sshport = 22;
		}
		else if (!is_numeric($sshport))
		{
			$error .= 'SSH Port is not a numeric value ! ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: boxadd.php" );
			die();
		}
		###
		//Check SSH2 connection if specified
		if ($verify == 'on')
		{
			$ssh = new Net_SSH2($ip.':'.$sshport);
			if (!$ssh->login($login, $password))
			{
				$_SESSION['msg1'] = 'Connection Error!';
				$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: boxadd.php" );
				die();
			}
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['name']);
		unset($_SESSION['ip']);
		unset($_SESSION['login']);
		unset($_SESSION['sshport']);
		unset($_SESSION['notes']);
		###
		//Security
		$sshport = abs($sshport);
		###
		//Adding the box to the database
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		query_basic( "INSERT INTO `".DBPREFIX."box` SET
			`name` = '".$name."',
			`ip` = '".$ip."',
			`login` = '".$login."',
			`password` = \"".$aes->encrypt($password)."\",
			`sshport` = '".$sshport."',
			`notes` = '".$notes."',
		    `bw_rx` = '0',
		    `bw_tx` = '0',
			`cpu` = 'Unknown;0;0',
			`ram` = '0;0;0;0',
			`loadavg` = '~',
			`hostname` = '~',
			`os` = '~',
			`date` = '~',
			`kernel` = '~',
			`arch` = '~',
			`uptime` = '~',
			`swap` = '0;0;0;0',
			`hdd` = '0;0;0;0'" );
		###
		//Adding event to the database
		$boxid = mysql_insert_id();
		$message = "Box Added: ".$name;
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `boxid` = '".$boxid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Box Added Successfully!';
		$_SESSION['msg2'] = 'The box has been added and is ready for use.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: boxsummary.php?id=".urlencode($boxid) );
		die();
		break;

	case 'boxprofile':
		$boxid = mysql_real_escape_string($_POST['boxid']);
		$name = mysql_real_escape_string($_POST['name']);
		$ip = mysql_real_escape_string($_POST['ip']);
		$login = mysql_real_escape_string($_POST['login']);
		$password = mysql_real_escape_string($_POST['password']);
		$sshport = mysql_real_escape_string($_POST['sshport']);
		$notes = mysql_real_escape_string($_POST['notes']);
		if (isset($_POST['verify'])) {
			$verify = 'on';
		} else {
			$verify = '';
		}
		###
		//Check the inputs. Output an error if the validation failed
		$nameLength = strlen($name);
		###
		$error = '';
		###
		if (!is_numeric($boxid))
		{
			$error .= 'Invalid BoxID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		###
		if ($nameLength < 2)
		{
			$error .= 'Box Name is too short (2 Chars min.). ';
		}
		if (!validateIP($ip))
		{
			$error .= 'Invalid IP. ';
		}
        else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `ip` = '".$ip."' && `login` = '".$login."' && `boxid` != '".$boxid."'" ) != 0)
        {
			$error .= 'This IP is already in use with the same login ! ';
		}
		if (empty($login))
		{
			$error .= 'No SSH login ! ';
		}
		if (empty($sshport))
		{
			$sshport = 22;
		}
		else if (!is_numeric($sshport))
		{
			$error .= 'SSH Port is not a numeric value ! ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: boxprofile.php?id=".urlencode($boxid) );
			die();
		}
		###
		//Security
		$sshport = abs($sshport);
		###
		//Check SSH2 connection if specified
		if ($verify == 'on')
		{
			if (empty($password))
			{
				$passwd = query_fetch_assoc( "SELECT `password` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
				$aes = new Crypt_AES();
				$aes->setKeyLength(256);
				$aes->setKey(CRYPT_KEY);
				$password = $aes->decrypt($passwd['password']);
				unset($passwd);
			}
			$ssh = new Net_SSH2($ip.':'.$sshport);
			if (!$ssh->login($login, $password))
			{
				$_SESSION['msg1'] = 'Connection Error!';
				$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: boxprofile.php?id=".urlencode($boxid) );
				die();
			}
		}
		###
		//Processing password
		if (empty($password)) //No password provided, we keep the encrypted one that is stored into database
		{
			$passwd = query_fetch_assoc( "SELECT `password` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
			$password = $passwd['password'];
			unset($passwd);
		}
		else
		{
			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);
			$password = $aes->encrypt($password);
		}
		###
		//Updating
		query_basic( "UPDATE `".DBPREFIX."box` SET `name` = '".$name."', `ip` = '".$ip."', `login` = '".$login."', `password` = \"".$password."\", `sshport` = '".$sshport."', `notes` = '".$notes."' WHERE `boxid` = '".$boxid."'" );
		###
		//Adding event to the database
		$message = "Box Edited: ".$name;
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `boxid` = '".$boxid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Box Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the box have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: boxsummary.php?id=".urlencode($boxid) );
		die();
		break;

	case 'boxnotes':
		$boxid = mysql_real_escape_string($_POST['boxid']);
		$notes = mysql_real_escape_string($_POST['notes']);
		###
		$error = '';
		###
		if (!is_numeric($boxid))
		{
			$error .= 'Invalid BoxID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: index.php" );
			die();
		}
		###
		query_basic( "UPDATE `".DBPREFIX."box` SET `notes` = '".$notes."' WHERE `boxid` = '".$boxid."'" );
		###
		$_SESSION['msg1'] = 'Admin Notes Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the admin notes have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: boxsummary.php?id=".urlencode($boxid) );
		die();
		break;

	case 'boxdelete':
		$boxid = $_GET['id'];
		###
		$error = '';
		###
		if (!is_numeric($boxid))
		{
			$error .= 'Invalid BoxID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: index.php" );
			die();
		}
		###
		if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `boxid` = '".$boxid."'" ) != 0)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'Assigned servers must be deleted first.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: boxsummary.php?id=".urlencode($boxid) );
			die();
		}
		$rows = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
		###
		query_basic( "DELETE FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
		###
		//Adding event to the database
		$message = 'Box Deleted: '.mysql_real_escape_string($rows['name']);
		###
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `boxid` = '".$boxid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Box Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected box has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: box.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>