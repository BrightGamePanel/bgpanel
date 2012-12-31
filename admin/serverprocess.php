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
	case 'serveradd':
		$groupid = mysql_real_escape_string($_POST['groupID']);
		$boxid = mysql_real_escape_string($_POST['boxID']);
		$gameid = mysql_real_escape_string($_POST['gameID']);
		$name = mysql_real_escape_string($_POST['name']);
		$priority = mysql_real_escape_string($_POST['priority']);
		$slots = mysql_real_escape_string($_POST['slots']);
		$port = mysql_real_escape_string($_POST['port']);
		$queryport = mysql_real_escape_string($_POST['queryPort']);
		$cfg1Name = mysql_real_escape_string($_POST['cfg1Name']);
		$cfg1 = mysql_real_escape_string($_POST['cfg1']);
		$cfg2Name = mysql_real_escape_string($_POST['cfg2Name']);
		$cfg2 = mysql_real_escape_string($_POST['cfg2']);
		$cfg3Name = mysql_real_escape_string($_POST['cfg3Name']);
		$cfg3 = mysql_real_escape_string($_POST['cfg3']);
		$cfg4Name = mysql_real_escape_string($_POST['cfg4Name']);
		$cfg4 = mysql_real_escape_string($_POST['cfg4']);
		$cfg5Name = mysql_real_escape_string($_POST['cfg5Name']);
		$cfg5 = mysql_real_escape_string($_POST['cfg5']);
		$cfg6Name = mysql_real_escape_string($_POST['cfg6Name']);
		$cfg6 = mysql_real_escape_string($_POST['cfg6']);
		$cfg7Name = mysql_real_escape_string($_POST['cfg7Name']);
		$cfg7 = mysql_real_escape_string($_POST['cfg7']);
		$cfg8Name = mysql_real_escape_string($_POST['cfg8Name']);
		$cfg8 = mysql_real_escape_string($_POST['cfg8']);
		$cfg9Name = mysql_real_escape_string($_POST['cfg9Name']);
		$cfg9 = mysql_real_escape_string($_POST['cfg9']);
		$startline = mysql_real_escape_string($_POST['startLine']);
		$homedir = mysql_real_escape_string($_POST['homeDir']);
		###
		//Used to fill in the blanks of the form
		$_SESSION['groupid'] = $groupid;
		$_SESSION['boxid'] = $boxid;
		$_SESSION['name'] = $name;
		$_SESSION['priority'] = $priority;
		$_SESSION['slots'] = $slots;
		$_SESSION['port'] = $port;
		$_SESSION['queryport'] = $queryport;
		$_SESSION['cfg1Name'] = $cfg1Name;
		$_SESSION['cfg1'] = $cfg1;
		$_SESSION['cfg2Name'] = $cfg2Name;
		$_SESSION['cfg2'] = $cfg2;
		$_SESSION['cfg3Name'] = $cfg3Name;
		$_SESSION['cfg3'] = $cfg3;
		$_SESSION['cfg4Name'] = $cfg4Name;
		$_SESSION['cfg4'] = $cfg4;
		$_SESSION['cfg5Name'] = $cfg5Name;
		$_SESSION['cfg5'] = $cfg5;
		$_SESSION['cfg6Name'] = $cfg6Name;
		$_SESSION['cfg6'] = $cfg6;
		$_SESSION['cfg7Name'] = $cfg7Name;
		$_SESSION['cfg7'] = $cfg7;
		$_SESSION['cfg8Name'] = $cfg8Name;
		$_SESSION['cfg8'] = $cfg8;
		$_SESSION['cfg9Name'] = $cfg9Name;
		$_SESSION['cfg9'] = $cfg9;
		$_SESSION['startline'] = $startline;
		$_SESSION['homedir'] = $homedir;
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (!is_numeric($gameid))
		{
			$error .= 'GameID is not valid. ';
		}
		else if (query_numrows( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."'" ) == 0)
		{
			$error .= 'Invalid GameID. ';
		}
		if (empty($name))
		{
			$error .= 'No server name specified. ';
		}
		else if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `name` = '".$name."' && `boxid` = '".$boxid."'" ) != 0)
		{
			$error .= 'This name is already in use ! ';
		}
		if (!is_numeric($groupid) && $groupid != 'none')
		{
			$error .= 'GroupID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
		{
			$error .= 'Invalid GroupID. ';
		}
		if ($groupid == 'none')
		{
			$error .= 'Please select an owner group. ';
		}
		if (!is_numeric($boxid))
		{
			$error .= 'BoxID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		if (!is_numeric($priority))
		{
			$error .= 'Priority must be a numeric value ! ';
		}
		if (!is_numeric($slots))
		{
			$error .= 'The slots must be a numeric value ! ';
		}
		if (!is_numeric($port))
		{
			$error .= 'Port must be a numeric value ! ';
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `port` = '".$port."' && `boxid` = '".$boxid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= 'Port is already in use ! ';
		}
		if (empty($startline))
		{
			$error .= 'Start command is not specified. ';
		}
		if (!is_numeric($queryport))
		{
			$error .= 'Queryport must be a numeric value ! ';
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `queryport` = '".$queryport."' && `boxid` = '".$boxid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= 'Queryport is already in use ! ';
		}
		if (empty($homedir))
		{
			$error .= 'Home Directory is not specified. ';
		}
		else if(!validateDirPath($homedir))
		{
			$error .= 'Invalid Home Directory. ';
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."'" );
		if ($status['status'] == 'Inactive')
		{
			$error .= 'The game is unavailable.';
		}
		unset($status);
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['groupid']);
		unset($_SESSION['boxid']);
		unset($_SESSION['name']);
		unset($_SESSION['priority']);
		unset($_SESSION['slots']);
		unset($_SESSION['port']);
		unset($_SESSION['queryport']);
		unset($_SESSION['cfg1Name']);
		unset($_SESSION['cfg1']);
		unset($_SESSION['cfg2Name']);
		unset($_SESSION['cfg2']);
		unset($_SESSION['cfg3Name']);
		unset($_SESSION['cfg3']);
		unset($_SESSION['cfg4Name']);
		unset($_SESSION['cfg4']);
		unset($_SESSION['cfg5Name']);
		unset($_SESSION['cfg5']);
		unset($_SESSION['cfg6Name']);
		unset($_SESSION['cfg6']);
		unset($_SESSION['cfg7Name']);
		unset($_SESSION['cfg7']);
		unset($_SESSION['cfg8Name']);
		unset($_SESSION['cfg8']);
		unset($_SESSION['cfg9Name']);
		unset($_SESSION['cfg9']);
		unset($_SESSION['startline']);
		unset($_SESSION['homedir']);
		###
		//Security
		$slots = abs($slots);
		$port = abs($port);
		$queryport = abs($queryport);
		###
		$rows = query_fetch_assoc( "SELECT `game`, `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."' LIMIT 1" );
		$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
		###
		//Adding the server to the database
		query_basic( "INSERT INTO `".DBPREFIX."server` SET
			`groupid` = '".$groupid."',
			`boxid` = '".$boxid."',
			`gameid` = '".$gameid."',
			`name` = '".$name."',
			`game` = '".mysql_real_escape_string($rows['game'])."',
			`status` = 'Pending',
			`panelstatus` = 'Stopped',
			`slots` = '".$slots."',
			`port` = '".$port."',
			`queryport` = '".$queryport."',
			`priority` = '".$priority."',
			`cfg1name` = '".$cfg1Name."',
			`cfg1` = '".$cfg1."',
			`cfg2name` = '".$cfg2Name."',
			`cfg2` = '".$cfg2."',
			`cfg3name` = '".$cfg3Name."',
			`cfg3` = '".$cfg3."',
			`cfg4name` = '".$cfg4Name."',
			`cfg4` = '".$cfg4."',
			`cfg5name` = '".$cfg5Name."',
			`cfg5` = '".$cfg5."',
			`cfg6name` = '".$cfg6Name."',
			`cfg6` = '".$cfg6."',
			`cfg7name` = '".$cfg7Name."',
			`cfg7` = '".$cfg7."',
			`cfg8name` = '".$cfg8Name."',
			`cfg8` = '".$cfg8."',
			`cfg9name` = '".$cfg9Name."',
			`cfg9` = '".$cfg9."',
			`startline` = '".$startline."',
			`homedir` = '".$homedir."',
			`screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $name)."'" );
		###
		$serverid = mysql_insert_id();
		###
		//LGSL
		query_basic( "INSERT INTO `".DBPREFIX."lgsl` SET
			`id` = '".$serverid."',
			`type` = '".mysql_real_escape_string($rows['querytype'])."',
			`ip` = '".$serverIp['ip']."',
			`c_port` = '".$port."',
			`q_port` = '".$queryport."',
			`s_port` = '0',
			`zone` = '0',
			`disabled` = '0',
			`comment` = '".$name."',
			`status` = '0',
			`cache` = '',
			`cache_time` = ''" );
		###
		//Adding event to the database
		$message = "Server Added: ".$name;
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Added Successfully!';
		$_SESSION['msg2'] = 'The new server has been added but must be validated.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	case 'serverprofile':
		$serverid = mysql_real_escape_string($_POST['serverid']);
		$status = mysql_real_escape_string($_POST['status']);
		$name = mysql_real_escape_string($_POST['name']);
		$groupid = mysql_real_escape_string($_POST['groupid']);
		$boxid = mysql_real_escape_string($_POST['boxid']);
		$priority = mysql_real_escape_string($_POST['priority']);
		$slots = mysql_real_escape_string($_POST['slots']);
		$port = mysql_real_escape_string($_POST['port']);
		$queryport = mysql_real_escape_string($_POST['queryPort']);
		$cfg1Name = mysql_real_escape_string($_POST['cfg1Name']);
		$cfg1 = mysql_real_escape_string($_POST['cfg1']);
		$cfg2Name = mysql_real_escape_string($_POST['cfg2Name']);
		$cfg2 = mysql_real_escape_string($_POST['cfg2']);
		$cfg3Name = mysql_real_escape_string($_POST['cfg3Name']);
		$cfg3 = mysql_real_escape_string($_POST['cfg3']);
		$cfg4Name = mysql_real_escape_string($_POST['cfg4Name']);
		$cfg4 = mysql_real_escape_string($_POST['cfg4']);
		$cfg5Name = mysql_real_escape_string($_POST['cfg5Name']);
		$cfg5 = mysql_real_escape_string($_POST['cfg5']);
		$cfg6Name = mysql_real_escape_string($_POST['cfg6Name']);
		$cfg6 = mysql_real_escape_string($_POST['cfg6']);
		$cfg7Name = mysql_real_escape_string($_POST['cfg7Name']);
		$cfg7 = mysql_real_escape_string($_POST['cfg7']);
		$cfg8Name = mysql_real_escape_string($_POST['cfg8Name']);
		$cfg8 = mysql_real_escape_string($_POST['cfg8']);
		$cfg9Name = mysql_real_escape_string($_POST['cfg9Name']);
		$cfg9 = mysql_real_escape_string($_POST['cfg9']);
		$startline = mysql_real_escape_string($_POST['startLine']);
		$homedir = mysql_real_escape_string($_POST['homeDir']);
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (!is_numeric($serverid))
		{
			$error .= 'Invalid ServerID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
		{
			$error .= 'Invalid ServerID. ';
		}
		###
		if ($status != 'Active')
		{
			if ($status != 'Inactive')
			{
				if ($status != 'Pending')
				{
					$error .= 'Invalid status. ';
				}
			}
		}
		###
		$pstatus = query_fetch_assoc( "SELECT `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'");
		if ($pstatus['panelstatus'] == 'Started')
		{
			$error .= 'Cannot edit the server while this one is running. ';
		}
		unset($pstatus);
		###
		if (empty($name))
		{
			$error .= 'No server name specified. ';
		}
		else if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `name` = '".$name."' && `boxid` = '".$boxid."' && `serverid` != '".$serverid."'" ) != 0)
		{
			$error .= 'This name is already in use ! ';
		}
		if (!is_numeric($groupid) && $groupid != 'none')
		{
			$error .= 'GroupID is not valid. ';
		}
		if ($groupid == 'none')
		{
			$error .= 'Please select an owner group. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
		{
			$error .= 'Invalid GroupID. ';
		}
		if (!is_numeric($boxid))
		{
			$error .= 'BoxID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		if (!is_numeric($priority))
		{
			$error .= 'Priority must be a numeric value ! ';
		}
		if (!is_numeric($slots))
		{
			$error .= 'The slots must be a numeric value ! ';
		}
		if (!is_numeric($port))
		{
			$error .= 'Port must be a numeric value ! ';
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `port` = '".$port."' && `serverid` != '".$serverid."' && `boxid` = '".$boxid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= 'Port is already in use ! ';
		}
		if (empty($startline))
		{
			$error .= 'Start command is not specified. ';
		}
		if (!is_numeric($queryport))
		{
			$error .= 'Queryport must be a numeric value ! ';
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `queryport` = '".$queryport."' && `serverid` != '".$serverid."' && `boxid` = '".$boxid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= 'Queryport is already in use ! ';
		}
		if (empty($homedir))
		{
			$error .= 'Home Directory is not specified. ';
		}
		else if(!validateDirPath($homedir))
		{
			$error .= 'Invalid Home Directory. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: serverprofile.php?id='.urlencode($serverid) );
			die();
		}
		###
		//Security
		$slots = abs($slots);
		$port = abs($port);
		$queryport = abs($queryport);
		###
		$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
		###
		//We update the database
		query_basic( "UPDATE `".DBPREFIX."server` SET
			`groupid` = '".$groupid."',
			`boxid` = '".$boxid."',
			`name` = '".$name."',
			`status` = '".$status."',
			`slots` = '".$slots."',
			`port` = '".$port."',
			`queryport` = '".$queryport."',
			`priority` = '".$priority."',
			`cfg1name` = '".$cfg1Name."',
			`cfg1` = '".$cfg1."',
			`cfg2name` = '".$cfg2Name."',
			`cfg2` = '".$cfg2."',
			`cfg3name` = '".$cfg3Name."',
			`cfg3` = '".$cfg3."',
			`cfg4name` = '".$cfg4Name."',
			`cfg4` = '".$cfg4."',
			`cfg5name` = '".$cfg5Name."',
			`cfg5` = '".$cfg5."',
			`cfg6name` = '".$cfg6Name."',
			`cfg6` = '".$cfg6."',
			`cfg7name` = '".$cfg7Name."',
			`cfg7` = '".$cfg7."',
			`cfg8name` = '".$cfg8Name."',
			`cfg8` = '".$cfg8."',
			`cfg9name` = '".$cfg9Name."',
			`cfg9` = '".$cfg9."',
			`startline` = '".$startline."',
			`homedir` = '".$homedir."',
			`screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $name)."' WHERE `serverid` = '".$serverid."'" );
		###
		//LGSL
		query_basic( "UPDATE `".DBPREFIX."lgsl` SET
			`ip` = '".$serverIp['ip']."',
			`c_port` = '".$port."',
			`q_port` = '".$queryport."',
			`s_port` = '0',
			`zone` = '0',
			`disabled` = '0',
			`comment` = '".$name."',
			`status` = '1' WHERE `id` = '".$serverid."'" );
		###
		//Update LGSL cache
		###
		include_once("../libs/lgsl/lgsl_class.php");
		lgsl_query_cached("", "", "", "", "", "sep", $serverid);
		###
		//Adding event to the database
		$message = "Server Edited: ".$name;
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the server have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	case 'servervalidation':
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= 'No ServerID specified for server validation !';
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
		else
		{
			$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
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
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
			###
			//We check for "screen" requirement
			$output = $ssh->exec('screen -v'."\n");
			if (!preg_match("#^Screen version#", $output))
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'Screen is not installed on the server\'s box.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
			###
			//If the server is GUI based, we have to check more stuff...
			if (preg_match("#^xvfb-run#", $server['startline']))
			{
				//We check for "Xorg" requirement
				$output = $ssh->exec('Xorg -version'."\n");
				if (!preg_match("#X.Org X Server#", $output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'Xorg is not installed on the server\'s box.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
				}
				//We check for "hal" requirement
				$output = $ssh->exec('dpkg --status hal'."\n");
				if (!preg_match("#Status: install ok installed#", $output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'hal is not installed on the server\'s box.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
				}
				//We check for "xvfb" requirement
				$output = $ssh->exec('dpkg --status xvfb'."\n");
				if (!preg_match("#Status: install ok installed#", $output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'Xvfb is not installed on the server\'s box.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
				}
			}
			###
			//We check for "wine" requirement if it is necessary
			if (preg_match("#wine#", $server['startline']))
			{
				$output = $ssh->exec('wine --version'."\n");
				if (!preg_match("#^wine#", $output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'Wine is not installed on the server\'s box.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
				}
			}
			###
			//We check server dir
			$output = $ssh->exec('cd '.$server['homedir']."\n"); //We retrieve the output of the 'cd' command
			if (!empty($output)) //If the output is empty, we consider that there is no errors
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'Unable to find HOMEDIR path.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
			else
			{
				//We need the binary name, in order to check if this one is located in the home directory
				###
				//Binary Exceptions
				$exceptions = array( 'wine', 'java', 'python', 'xvfb-run' );
				###
				$words = explode(' ', $server['startline']);
				###
				foreach($words as $value)
				{
					$value = trim($value);
					###
					if (preg_match("#^./#", $value))
					{
						$value = substr($value, 2); //Removing ./ if the word begin with it
					}
					###
					if(preg_match("#[a-zA-Z0-9_\.-]#", $value)) //alphanumeric + " - _ . "
					{
						if(!in_array($value, $exceptions)) //Wine, java and so on are skipped (exceptions)
						{
							$binary = $value;
							break;
						}
					}
				}
				###
				unset($exceptions, $words);
				###
				if (!isset($binary))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'No server executable was found in the start command.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
				}
				###
				$ssh->exec('cd '.$server['homedir'].'; ls > temp.txt'."\n"); //We list all files of the home directory into 'temp.txt'
				$output = $ssh->exec('cd '.$server['homedir'].'; grep \''.$binary.'\' temp.txt'."\n"); //We check for the bin
				$ssh->exec('cd '.$server['homedir'].'; rm temp.txt'."\n"); //temp.txt is now useless
				if (empty($output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'Unable to find '.htmlspecialchars($binary, ENT_QUOTES).' located in '.htmlspecialchars($server['homedir'], ENT_QUOTES);
					$_SESSION['msg-type'] = 'error';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
				}
				else
				{
					//Everything is OKAY, Mark the server as validated
					###
					query_basic( "UPDATE `".DBPREFIX."server` SET `status` = 'Active' WHERE `serverid` = '".$serverid."'" );
					###
					//Adding event to the database
					$message = 'Server Validated : '.mysql_real_escape_string($server['name']);
					query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
					###
					$_SESSION['msg1'] = 'Server Successfully Validated!';
					$_SESSION['msg2'] = 'The server is now ready for use.';
					$_SESSION['msg-type'] = 'success';
					header( "Location: serversummary.php?id=".urlencode($serverid) );
					die();
					break;
				}
			}
		}

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
		$server = query_fetch_assoc( "SELECT `boxid`, `name`, `homedir`, `screen` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
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
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$cmd = "cat screenlog.0";
		$output = $ssh->exec('cd '.$server['homedir'].'; '.$cmd."\n");
		###
		//Adding event to the database
		$message = mysql_real_escape_string($server['name']).' : screenlog downloaded';
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="'.$server['screen'].'_'.date('Y-m-d').'.screenlog"');
		###
		echo $output;
		###
		die();
		break;

	case 'serverdelete':
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= 'No ServerID specified for server validation !';
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
		$rows = query_fetch_assoc( "SELECT `boxid`, `name`, `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		###
		if ($rows['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The server must be stopped first!';
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		query_basic( "DELETE FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		query_basic( "DELETE FROM `".DBPREFIX."lgsl` WHERE `id` = '".$serverid."' LIMIT 1" ); //LGSL
		###
		$message = 'Server Deleted: '.mysql_real_escape_string($rows['name']);
		###
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `boxid` = '".$rows['boxid']."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected server has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: server.php" );
		die();
		break;

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

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
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: servermanage.php?id=".urlencode($serverid) );
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
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Successfully Started!';
		$_SESSION['msg2'] = 'With command : '.htmlspecialchars($startline, ENT_QUOTES);
		$_SESSION['msg-type'] = 'info';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
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
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: servermanage.php?id=".urlencode($serverid) );
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
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Successfully Stopped!';
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'info';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
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
		$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = 'Connection Error!';
			$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: servermanage.php?id=".urlencode($serverid) );
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
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Server Successfully Rebooted!';
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'info';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>