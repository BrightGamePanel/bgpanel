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
		$ipid = mysql_real_escape_string($_POST['ipID']);
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
		$action = mysql_real_escape_string($_POST['radioAction']);;
		$path = mysql_real_escape_string($_POST['path']); // Link
		$path2 = mysql_real_escape_string($_POST['path2']); // Create
		###
		$boxidQuery = query_fetch_assoc( "SELECT `boxid` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$ipid."' LIMIT 1" );
		$boxid = $boxidQuery['boxid'];
		###
		//Used to fill in the blanks of the form
		$_SESSION['groupid'] = $groupid;
		$_SESSION['ipid'] = $ipid;
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
		$_SESSION['path'] = $path;
		$_SESSION['path2'] = $path2;
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (!is_numeric($gameid))
		{
			$error .= T_('GameID is not valid. ');
		}
		else if (query_numrows( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."'" ) == 0)
		{
			$error .= T_('Invalid GameID. ');
		}
		$gameStatus = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."'" );
		if ($gameStatus['status'] == 'Inactive')
		{
			$error .= T_('The game is unavailable.');
		}
		unset($gameStatus);
		if (empty($name))
		{
			$error .= T_('No server name specified. ');
		}
		else if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `name` = '".$name."' && `boxid` = '".$boxid."'" ) != 0)
		{
			$error .= T_('This name is already in use ! ');
		}
		if (!is_numeric($groupid))
		{
			$error .= T_('GroupID is not valid. ');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
		{
			$error .= T_('Invalid GroupID. ');
		}
		if (!is_numeric($boxid))
		{
			$error .= T_('BoxID is not valid. ');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= T_('Invalid BoxID. ');
		}
		if (!is_numeric($ipid))
		{
			$error .= T_('IpID is not valid. ');
		}
		else if (query_numrows( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$ipid."'" ) == 0)
		{
			$error .= T_('Invalid IpID. ');
		}
		if (!is_numeric($priority))
		{
			$error .= T_('Priority must be a numeric value ! ');
		}
		if (!is_numeric($slots))
		{
			$error .= T_('The slots must be a numeric value ! ');
		}
		if (!is_numeric($port))
		{
			$error .= T_('Port must be a numeric value ! ');
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `port` = '".$port."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= T_('Port is already in use ! ');
		}
		if (empty($startline))
		{
			$error .= T_('Start command is not specified. ');
		}
		if (!is_numeric($queryport))
		{
			$error .= T_('Queryport must be a numeric value ! ');
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `queryport` = '".$queryport."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= T_('Queryport is already in use ! ');
		}
		switch (@$action)
		{
			case 'link':
				if (empty($path))
				{
					$error .= T_('Path is not specified. ');
				}
				else if(!validatePath($path))
				{
					$error .= T_('Invalid Path. ');
				}
				else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `path` = '".$path."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
				{
					$error .= T_('Path is already in use ! ');
				}
				break;

			case 'create':
				if (empty($path2))
				{
					$error .= T_('Path is not specified. ');
				}
				else if(!validatePath($path2))
				{
					$error .= T_('Invalid Path. ');
				}
				else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `path` = '".$path2."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
				{
					$error .= T_('Path is already in use ! ');
				}
				break;

			default:
				$error .= T_('Invalid Action ! ');
				break;
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
			die();
		}
		###
		//Security
		$slots = abs($slots);
		$port = abs($port);
		$queryport = abs($queryport);
		###
		$game = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."' LIMIT 1" );
		$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$ipid."' LIMIT 1" );
		###
		// Perform Selected Action
		switch (@$action)
		{
			case 'link':
				//As the form has been validated, vars are useless
				unset($_SESSION['groupid']);
				unset($_SESSION['ipid']);
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
				unset($_SESSION['path']);
				unset($_SESSION['path2']);
				###
				//Adding the server to the database
				query_basic( "INSERT INTO `".DBPREFIX."server` SET
					`groupid` = '".$groupid."',
					`boxid` = '".$boxid."',
					`ipid` = '".$ipid."',
					`gameid` = '".$gameid."',
					`name` = '".$name."',
					`game` = '".mysql_real_escape_string($game['game'])."',
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
					`path` = '".$path."',
					`screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $name)."'" );
				###
				$serverid = mysql_insert_id();
				###
				//LGSL
				query_basic( "INSERT INTO `".DBPREFIX."lgsl` SET
					`id` = '".$serverid."',
					`type` = '".mysql_real_escape_string($game['querytype'])."',
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
				$_SESSION['msg1'] = T_('Server Added Successfully!');
				$_SESSION['msg2'] = T_('The new server has been added but must be validated.');
				$_SESSION['msg-type'] = 'success';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
				break;

			case 'create':
				###
				// Make Game Server
				###
				require_once("../includes/func.gameinstaller.inc.php");
				require_once("../libs/gameinstaller/gameinstaller.php");
				###
				$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
				$realGameServerPath = addBin2GameServerPath( $path2, $game['game'] );
				###
				$aes = new Crypt_AES();
				$aes->setKeyLength(256);
				$aes->setKey(CRYPT_KEY);
				###
				// Get SSH2 Object OR ERROR String
				$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
				if (!is_object($ssh))
				{
					$_SESSION['msg1'] = T_('Connection Error!');
					$_SESSION['msg2'] = $ssh;
					$_SESSION['msg-type'] = 'error';
					header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
					die();
				}
				###
				$gameInstaller = new GameInstaller( $ssh );
				###
				$setGame = $gameInstaller->setGame( $game['game'] );
				if ($setGame == FALSE) {
					$_SESSION['msg1'] = T_('Game Installer Error!');
					$_SESSION['msg2'] = T_('Game Not Supported');
					$_SESSION['msg-type'] = 'error';
					header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
					die();
				}
				$setRepoPath = $gameInstaller->setRepoPath( $game['cachedir'] );
				if ($setRepoPath == FALSE) {
					$_SESSION['msg1'] = T_('Unable To Install Game Server!');
					$_SESSION['msg2'] = T_('Unable To Set Repository Directory');
					$_SESSION['msg-type'] = 'error';
					header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
					die();
				}
				$repoCacheInfo = $gameInstaller->getCacheInfo( $game['cachedir'] );
				if ($repoCacheInfo['status'] != 'Ready') {
					$_SESSION['msg1'] = T_('Unable To Install Game Server!');
					$_SESSION['msg2'] = T_('Game Cache Not Ready!');
					$_SESSION['msg-type'] = 'error';
					header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
					die();
				}
				$setGameServerPath = $gameInstaller->setGameServerPath( dirname($realGameServerPath), TRUE );
				if ($setGameServerPath == FALSE) {
					$_SESSION['msg1'] = T_('Unable To Install Game Server!');
					$_SESSION['msg2'] = T_('Unable To Set Game Server Directory');
					$_SESSION['msg-type'] = 'error';
					header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
					die();
				}
				$makeGameServer = $gameInstaller->makeGameServer( );
				if ($makeGameServer == FALSE) {
					$_SESSION['msg1'] = T_('Unable To Install Game Server!');
					$_SESSION['msg2'] = T_('Internal Error');
					$_SESSION['msg-type'] = 'error';
					header( 'Location: serveradd.php?gameid='.urlencode($gameid) );
					die();
				}
				###
				//As the form has been validated, vars are useless
				unset($_SESSION['groupid']);
				unset($_SESSION['ipid']);
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
				unset($_SESSION['path']);
				unset($_SESSION['path2']);
				###
				//Adding the server to the database
				query_basic( "INSERT INTO `".DBPREFIX."server` SET
					`groupid` = '".$groupid."',
					`boxid` = '".$boxid."',
					`ipid` = '".$ipid."',
					`gameid` = '".$gameid."',
					`name` = '".$name."',
					`game` = '".mysql_real_escape_string($game['game'])."',
					`status` = 'Active',
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
					`path` = '".$realGameServerPath."',
					`screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $name)."'" );
				###
				$serverid = mysql_insert_id();
				###
				//LGSL
				query_basic( "INSERT INTO `".DBPREFIX."lgsl` SET
					`id` = '".$serverid."',
					`type` = '".mysql_real_escape_string($game['querytype'])."',
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
				$_SESSION['msg1'] = T_('Server Added Successfully!');
				$_SESSION['msg2'] = T_('The new server has been added and is currently being created.');
				$_SESSION['msg-type'] = 'success';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
				break;
		}
		break;

	case 'serverprofile':
		$serverid = mysql_real_escape_string($_POST['serverid']);
		$status = mysql_real_escape_string($_POST['status']);
		$name = mysql_real_escape_string($_POST['name']);
		$groupid = mysql_real_escape_string($_POST['groupid']);
		$ipid = mysql_real_escape_string($_POST['ipid']);
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
		$path = mysql_real_escape_string($_POST['path']);
		###
		$boxidQuery = query_fetch_assoc( "SELECT `boxid` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$ipid."' LIMIT 1" );
		$boxid = $boxidQuery['boxid'];
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (!is_numeric($serverid))
		{
			$error .= T_('Invalid ServerID. ');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
		{
			$error .= T_('Invalid ServerID. ');
		}
		###
		if ($status != 'Active')
		{
			if ($status != 'Inactive')
			{
				if ($status != 'Pending')
				{
					$error .= T_('Invalid status. ');
				}
			}
		}
		###
		$pstatus = query_fetch_assoc( "SELECT `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'");
		if ($pstatus['panelstatus'] == 'Started')
		{
			$error .= T_('Cannot edit the server while this one is running. ');
		}
		unset($pstatus);
		###
		if (empty($name))
		{
			$error .= T_('No server name specified. ');
		}
		else if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `name` = '".$name."' && `boxid` = '".$boxid."' && `serverid` != '".$serverid."'" ) != 0)
		{
			$error .= T_('This name is already in use ! ');
		}
		if (!is_numeric($groupid))
		{
			$error .= T_('GroupID is not valid. ');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
		{
			$error .= T_('Invalid GroupID. ');
		}
		if (!is_numeric($boxid))
		{
			$error .= T_('BoxID is not valid. ');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= T_('Invalid BoxID. ');
		}
		if (!is_numeric($ipid))
		{
			$error .= T_('IpID is not valid. ');
		}
		else if (query_numrows( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$ipid."'" ) == 0)
		{
			$error .= T_('Invalid IpID. ');
		}
		if (!is_numeric($priority))
		{
			$error .= T_('Priority must be a numeric value ! ');
		}
		if (!is_numeric($slots))
		{
			$error .= T_('The slots must be a numeric value ! ');
		}
		if (!is_numeric($port))
		{
			$error .= T_('Port must be a numeric value ! ');
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `port` = '".$port."' && `serverid` != '".$serverid."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= T_('Port is already in use ! ');
		}
		if (empty($startline))
		{
			$error .= T_('Start command is not specified. ');
		}
		if (!is_numeric($queryport))
		{
			$error .= T_('Queryport must be a numeric value ! ');
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `queryport` = '".$queryport."' && `serverid` != '".$serverid."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= T_('Queryport is already in use ! ');
		}
		if (empty($path))
		{
			$error .= T_('Path is not specified. ');
		}
		else if(!validatePath($path))
		{
			$error .= T_('Invalid Path. ');
		}
		else if(query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `path` = '".$path."' && `serverid` != '".$serverid."' && `boxid` = '".$boxid."' && `ipid` = '".$ipid."' && `status` != 'Inactive'" ) != 0)
		{
			$error .= T_('Path is already in use ! ');
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error! Form has been reset!');
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
		$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$ipid."' LIMIT 1" );
		###
		//We update the database
		query_basic( "UPDATE `".DBPREFIX."server` SET
			`groupid` = '".$groupid."',
			`boxid` = '".$boxid."',
			`ipid` = '".$ipid."',
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
			`path` = '".$path."',
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

		/**
		 * Update LGSL cache
		 */
		include_once("../libs/lgsl/lgsl_class.php");
		lgsl_query_cached("", "", "", "", "", "sep", $serverid);

		/**
		 * Update AJXP
		 */
		require_once("../libs/ajxp/bridge.php");

		// Crypto
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);

		$bgpBoxes = array();
		if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box` ORDER BY `boxid`" ) != 0)
		{
			$boxes = mysql_query( "SELECT `boxid`, `name`, `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box`" );

			while ($rowsBoxes = mysql_fetch_assoc($boxes))
			{
				$rowsBoxes['password'] = $aes->decrypt($rowsBoxes['password']);
				$rowsBoxes['path'] = '/home/'.$rowsBoxes['login'].'/';

				$bgpBoxes[] = $rowsBoxes;
			}
			unset($boxes);
		}

		$bgpServers = array();
		if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `status` = 'Active' ORDER BY `serverid`" ) != 0)
		{
			$servers = mysql_query( "SELECT `serverid`, `boxid`, `ipid`, `name`, `path` FROM `".DBPREFIX."server` WHERE `status` = 'Active'" );

			while ($rowsServers = mysql_fetch_assoc($servers))
			{
				$box = query_fetch_assoc( "SELECT `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsServers['boxid']."'" );
				$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rowsServers['ipid']."'" );

				$box['password'] = $aes->decrypt($box['password']);
				$rowsServers['path'] = dirname($rowsServers['path']).'/';

				unset($rowsServers['boxid'], $rowsServers['ipid']);

				$bgpServers[] = $rowsServers+$box+$ip;
			}
			unset($servers);
		}

		// AJXP Bridge
		$AJXP_Bridge = new AJXP_Bridge( $bgpBoxes, $bgpServers, $_SESSION['adminusername'] );

		// Update Workspaces
		$AJXP_Bridge->updateAJXPWorspaces();

		//Adding event to the database
		$message = "Server Edited: ".$name;
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Server Updated Successfully!');
		$_SESSION['msg2'] = T_('Your changes to the server have been saved.');
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
			$error .= T_('No ServerID specified for server validation !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}

		//We check for "screen" requirement
		$output = $ssh->exec('screen -v'."\n");
		if (strstr($output, 'Screen version 4.') == FALSE)
		{
			$_SESSION['msg1'] = T_('Error!');
			$_SESSION['msg2'] = T_("Screen is not installed on the server's box.");
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		//If the server is GUI based, we have to check more stuff
		if (strstr($server['startline'], 'xvfb-run') != FALSE)
		{
			//We check for "Xorg" requirement
			$output = $ssh->exec('Xorg -version'."\n");
			if (strstr($output, 'X.Org X Server') == FALSE)
			{
				$_SESSION['msg1'] = T_('Error!');
				$_SESSION['msg2'] = T_("Xorg is not installed on the server's box.");
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
			//We check for "hal" requirement
			$output = $ssh->exec('dpkg --status hal'."\n");
			if (strstr($output, 'Status: install ok installed') == FALSE)
			{
				$_SESSION['msg1'] = T_('Error!');
				$_SESSION['msg2'] = T_("hal is not installed on the server's box.");
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
			//We check for "xvfb" requirement
			$output = $ssh->exec('dpkg --status xvfb'."\n");
			if (strstr($output, 'Status: install ok installed') == FALSE)
			{
				$_SESSION['msg1'] = T_('Error!');
				$_SESSION['msg2'] = T_("Xvfb is not installed on the server's box.");
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
		}
		###
		//We check for "wine" requirement if it is necessary
		if (strstr($server['startline'], 'wine') != FALSE)
		{
			$output = $ssh->exec('wine --version'."\n");
			if (strstr($output, 'wine-') == FALSE)
			{
				$_SESSION['msg1'] = T_('Error!');
				$_SESSION['msg2'] = T_("Wine is not installed on the server's box.");
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
		}
		###
		//We check server dir
		$output = $ssh->exec('cd '.dirname($server['path'])."\n"); // Get the output of the 'cd' command
		if (!empty($output)) // If the output is empty, we consider that there is no errors
		{
			$_SESSION['msg1'] = T_('Error!');
			$_SESSION['msg2'] = T_('Unable to find').' '.htmlspecialchars(dirname($server['path']), ENT_QUOTES);
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		// Check if the server binary is located in the given path
		$serverBinExists = trim($ssh->exec('cd '.dirname($server['path']).'; test -f '.basename($server['path']).' && echo "true" || echo "false";'."\n"));
		if ( $serverBinExists == 'false' )
		{
			$_SESSION['msg1'] = T_('Error!');
			$_SESSION['msg2'] = T_('Unable to find').' '.htmlspecialchars(basename($server['path']), ENT_QUOTES).' '.T_('located in').' '.htmlspecialchars(dirname($server['path']), ENT_QUOTES);
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}

		//Everything is OKAY, Mark the server as validated
		$ssh->exec('cd '.dirname($server['path'])."; echo \"mtime: $(date +%s)\" > .cacheinfo ; "."\n");

		// Finish
		$ssh->disconnect();

		###
		query_basic( "UPDATE `".DBPREFIX."server` SET `status` = 'Active' WHERE `serverid` = '".$serverid."'" );
		###
		//Adding event to the database
		$message = 'Server Validated : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Server Successfully Validated!');
		$_SESSION['msg2'] = T_('The server is now ready for use.');
		$_SESSION['msg-type'] = 'success';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	case 'getserverlog':
		require_once("../libs/phpseclib/SFTP.php");
		###
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= T_('No ServerID specified !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT `boxid`, `name`, `path`, `screen` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);

		// Get SFTP
		$sftp = new Net_SFTP($box['ip'], $box['sshport']);
		if (!$sftp->login($box['login'], $aes->decrypt($box['password'])))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = '';
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}

		$log = $sftp->get( dirname($server['path']).'/screenlog.0' );

		$sftp->disconnect();

		//Adding event to the database
		$message = mysql_real_escape_string($server['name']).' : screenlog downloaded';
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="'.$server['screen'].'_'.date('Y-m-d').'.screenlog"');
		echo $log;
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
			$error .= T_('No ServerID specified for server validation !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: server.php' );
			die();
		}
		###
		$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		###
		if ($rows['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server must be stopped first!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}

		// Crypto
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);

		if ( isset($_GET['serverdeletefiles']) )
		{
			// Purge Files
			###
			require_once("../libs/gameinstaller/gameinstaller.php");
			###
			$error = '';
			###
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
			$game = query_fetch_assoc( "SELECT `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
			###
			// Get SSH2 Object OR ERROR String
			$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
			if (!is_object($ssh))
			{
				$_SESSION['msg1'] = T_('Connection Error!');
				$_SESSION['msg2'] = $ssh;
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
			###
			$gameInstaller = new GameInstaller( $ssh );
			###
			$gameInstaller->setGameServerPath( dirname($rows['path']) );
			###
			$opStatus = $gameInstaller->checkOperation( 'installGame' );
			if ($opStatus == FALSE) {
				$gameInstaller->deleteGameServer( );
			}
			else {
				$_SESSION['msg1'] = T_('Validation Error!');
				$_SESSION['msg2'] = T_('Operation in progress on this game server!');
				$_SESSION['msg-type'] = 'error';
				header( "Location: serversummary.php?id=".urlencode($serverid) );
				die();
			}
		}

		query_basic( "DELETE FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		query_basic( "DELETE FROM `".DBPREFIX."lgsl` WHERE `id` = '".$serverid."' LIMIT 1" ); //LGSL

		/**
		 * Update AJXP
		 */
		require_once("../libs/ajxp/bridge.php");

		$bgpBoxes = array();
		if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box` ORDER BY `boxid`" ) != 0)
		{
			$boxes = mysql_query( "SELECT `boxid`, `name`, `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box`" );

			while ($rowsBoxes = mysql_fetch_assoc($boxes))
			{
				$rowsBoxes['password'] = $aes->decrypt($rowsBoxes['password']);
				$rowsBoxes['path'] = '/home/'.$rowsBoxes['login'].'/';

				$bgpBoxes[] = $rowsBoxes;
			}
			unset($boxes);
		}

		$bgpServers = array();
		if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `status` = 'Active' ORDER BY `serverid`" ) != 0)
		{
			$servers = mysql_query( "SELECT `serverid`, `boxid`, `ipid`, `name`, `path` FROM `".DBPREFIX."server` WHERE `status` = 'Active'" );

			while ($rowsServers = mysql_fetch_assoc($servers))
			{
				$box = query_fetch_assoc( "SELECT `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsServers['boxid']."'" );
				$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rowsServers['ipid']."'" );

				$box['password'] = $aes->decrypt($box['password']);
				$rowsServers['path'] = dirname($rowsServers['path']).'/';

				unset($rowsServers['boxid'], $rowsServers['ipid']);

				$bgpServers[] = $rowsServers+$box+$ip;
			}
			unset($servers);
		}

		// AJXP Bridge
		$AJXP_Bridge = new AJXP_Bridge( $bgpBoxes, $bgpServers, $_SESSION['adminusername'] );

		// Update Workspaces
		$AJXP_Bridge->updateAJXPWorspaces();

		$message = 'Server Deleted: '.mysql_real_escape_string($rows['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `boxid` = '".$rows['boxid']."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Server Deleted Successfully!');
		$_SESSION['msg2'] = T_('The selected server has been removed.');
		$_SESSION['msg-type'] = 'success';
		header( "Location: server.php" );
		die();
		break;

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

	case 'serverstart':
		require_once("../libs/gameinstaller/gameinstaller.php");
		###
		$serverid = $_GET['serverid'];
		###
		$error = '';
		###
		if (empty($serverid))
		{
			$error .= T_('No ServerID specified !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
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
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server has been disabled.');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is pending.');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is already running!');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$serverIP = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$server['ipid']."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$gameInstaller = new GameInstaller( $ssh );
		###
		$opStatus = $gameInstaller->checkOperation( 'installGame' );
		if ($opStatus == TRUE) {
			$_SESSION['msg1'] = T_('Unable To Start The Game Server!');
			$_SESSION['msg2'] = T_('Operation in Progress!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		//We prepare the startline
		$startline = $server['startline'];
		###
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
		###
		//Adding event to the database
		$message = 'Server Started : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Server Successfully Started!');
		$_SESSION['msg2'] = T_('With command').' : '.htmlspecialchars($startline, ENT_QUOTES);
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
			$error .= T_('No ServerID specified !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
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
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server has been disabled.');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is pending.');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['panelstatus'] == 'Stopped')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is already stopped!');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
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
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Server Successfully Stopped!');
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
			$error .= T_('No ServerID specified !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
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
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server has been disabled.');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is pending.');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		else if ($status['panelstatus'] == 'Stopped')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is already stopped!');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: server.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$serverIP = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$server['ipid']."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
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

		query_basic( "UPDATE `".DBPREFIX."server` SET `panelstatus` = 'Started' WHERE `serverid` = '".$serverid."'" );
		###
		//Adding event to the database
		$message = 'Server Rebooted : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Server Successfully Rebooted!');
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'info';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

	case 'makeGameServer':
		require_once("../libs/gameinstaller/gameinstaller.php");
		###
		$serverid = mysql_real_escape_string($_GET['serverid']);
		###
		if (!is_numeric($serverid))
		{
			exit('Invalid ServerID.');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
		{
			exit('Invalid ServerID.');
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		###
		if ($server['status'] == 'Inactive')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server has been disabled.');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		else if ($server['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server must be stopped first!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		$game = query_fetch_assoc( "SELECT `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `gameid` = '".$server['gameid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$gameInstaller = new GameInstaller( $ssh );
		###
		$setGame = $gameInstaller->setGame( $game['game'] );
		if ($setGame == FALSE) {
			$_SESSION['msg1'] = T_('Game Installer Error!');
			$_SESSION['msg2'] = T_('Game Not Supported');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$setRepoPath = $gameInstaller->setRepoPath( $game['cachedir'] );
		if ($setRepoPath == FALSE) {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Unable To Set Repository Directory');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$repoCacheInfo = $gameInstaller->getCacheInfo( $game['cachedir'] );
		if ($repoCacheInfo['status'] != 'Ready') {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Game Cache Not Ready!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$setGameServerPath = $gameInstaller->setGameServerPath( dirname($server['path']), TRUE );
		if ($setGameServerPath == FALSE) {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Unable To Set Game Server Directory');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$opStatus = $gameInstaller->checkOperation( 'installGame' );
		if ($opStatus == TRUE) {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Operation in Progress!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$makeGameServer = $gameInstaller->makeGameServer( );
		if ($makeGameServer == FALSE) {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Internal Error');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		//Adding event to the database
		$message = 'Server Contents Reset : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Installing Game Server!');
		$_SESSION['msg2'] = T_('Your game server is currently being created. Please wait...');
		$_SESSION['msg-type'] = 'success';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	case 'updateGameServer':
		require_once("../libs/gameinstaller/gameinstaller.php");
		###
		$serverid = mysql_real_escape_string($_GET['serverid']);
		###
		if (!is_numeric($serverid))
		{
			exit('Invalid ServerID.');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
		{
			exit('Invalid ServerID.');
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		###
		if ($server['status'] == 'Inactive')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server has been disabled.');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		else if ($server['status'] == 'Pending')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is pending.');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		else if ($server['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server must be stopped first!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		$game = query_fetch_assoc( "SELECT `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `gameid` = '".$server['gameid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$gameInstaller = new GameInstaller( $ssh );
		###
		$setGame = $gameInstaller->setGame( $game['game'] );
		if ($setGame == FALSE) {
			$_SESSION['msg1'] = T_('Game Installer Error!');
			$_SESSION['msg2'] = T_('Game Not Supported');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$setRepoPath = $gameInstaller->setRepoPath( $game['cachedir'] );
		if ($setRepoPath == FALSE) {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Unable To Set Repository Directory');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$repoCacheInfo = $gameInstaller->getCacheInfo( $game['cachedir'] );
		if ($repoCacheInfo['status'] != 'Ready') {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Game Cache Not Ready!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$setGameServerPath = $gameInstaller->setGameServerPath( dirname($server['path']) );
		if ($setGameServerPath == FALSE) {
			$_SESSION['msg1'] = T_('Unable To Install Game Server!');
			$_SESSION['msg2'] = T_('Unable To Set Game Server Directory');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$opStatus = $gameInstaller->checkOperation( 'updateGame' );
		if ($opStatus == TRUE) {
			$_SESSION['msg1'] = T_('Unable To Update Game Server!');
			$_SESSION['msg2'] = T_('Operation in Progress!');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		$updateGameServer = $gameInstaller->updateGameServer( );
		if ($updateGameServer == FALSE) {
			$_SESSION['msg1'] = T_('Unable To Update Game Server!');
			$_SESSION['msg2'] = T_('Internal Error');
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		//Adding event to the database
		$message = 'Server Updated : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Updating Game Server!');
		$_SESSION['msg2'] = T_('Your game server is currently being updated. Please wait...');
		$_SESSION['msg-type'] = 'success';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	case 'abortOperation':
		require_once("../libs/gameinstaller/gameinstaller.php");
		###
		$serverid = mysql_real_escape_string($_GET['serverid']);
		###
		if (!is_numeric($serverid))
		{
			exit('Invalid ServerID.');
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
		{
			exit('Invalid ServerID.');
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		###
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		$game = query_fetch_assoc( "SELECT `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `gameid` = '".$server['gameid']."' LIMIT 1" );
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( "Location: serversummary.php?id=".urlencode($serverid) );
			die();
		}
		###
		$gameInstaller = new GameInstaller( $ssh );
		###
		$gameInstaller->setGameServerPath( dirname($server['path']) );
		###
		$gameInstaller->abortOperation( 'installGame' );
		###
		//Adding event to the database
		$message = 'Server Action Aborted : '.mysql_real_escape_string($server['name']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = T_('Warning: Operation Aborted!');
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'warning';
		header( "Location: serversummary.php?id=".urlencode($serverid) );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>