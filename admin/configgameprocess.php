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
include("../libs/lgsl/lgsl_protocol.php");


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
	case 'configgameadd':
		$gameName = mysql_real_escape_string($_POST['gameName']);
		$maxSlots = mysql_real_escape_string($_POST['maxSlots']);
		$defaultPort = mysql_real_escape_string($_POST['defaultPort']);
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
		$startLine = mysql_real_escape_string($_POST['startLine']);
		$queryType = mysql_real_escape_string($_POST['queryType']);
		$queryPort = mysql_real_escape_string($_POST['queryPort']);
		$cacheDir = mysql_real_escape_string($_POST['cacheDir']);
		###
		//Used to fill in the blanks of the form
		$_SESSION['gameName'] = $gameName;
		$_SESSION['maxSlots'] = $maxSlots;
		$_SESSION['defaultPort'] = $defaultPort;
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
		$_SESSION['startLine'] = $startLine;
		$_SESSION['queryType'] = $queryType;
		$_SESSION['queryPort'] = $queryPort;
		$_SESSION['cacheDir'] = $cacheDir;
		###
		//Check the inputs. Output an error if the validation failed
		$gameLength = strlen($gameName);
		###
		$error = '';
		###
		if ($gameLength < 2)
		{
			$error .= 'Game Name is too short (2 Chars min.). ';
		}
		if (empty($maxSlots))
		{
			$error .= 'Max Slots is not set ! ';
		}
		else if (!is_numeric($maxSlots))
		{
			$error .= 'Max Slots is not a numeric value ! ';
		}
		if (empty($defaultPort))
		{
			$error .= 'Default Server Port is not set ! ';
		}
		else if (!is_numeric($defaultPort))
		{
			$error .= 'Default Server Port is not a numeric value ! ';
		}
		if (empty($startLine))
		{
			$error .= 'Start Command is not set ! ';
		}
		if (!array_key_exists($queryType, lgsl_type_list()))
		{
			$error .= 'Unknown Query Type ! ';
		}
		if (!empty($queryPort))
		{
			if (!is_numeric($queryPort))
			{
				$error .= 'Query Port is not a numeric value ! ';
			}
		}
		else
		{
			$queryPort = $defaultPort;
		}
		/*
		if(!validateDirPath($cacheDir))
		{
			$error .= 'Invalid Cache Directory. ';
		}
		*/
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configgameadd.php" );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['gameName']);
		unset($_SESSION['maxSlots']);
		unset($_SESSION['defaultPort']);
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
		unset($_SESSION['startLine']);
		unset($_SESSION['queryType']);
		unset($_SESSION['queryPort']);
		unset($_SESSION['cacheDir']);
		###
		//Security
		$maxSlots = abs($maxSlots);
		$defaultPort = abs($defaultPort);
		$queryPort = abs($queryPort);
		###
		//Adding game to the database
		query_basic( "INSERT INTO `".DBPREFIX."game` SET
			`game` = '".$gameName."',
			`status` = 'Active',
			`maxslots` = '".$maxSlots."',
			`defaultport` = '".$defaultPort."',
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
			`startline` = '".$startLine."',
			`querytype` = '".$queryType."',
			`queryport` = '".$queryPort."',
			`cachedir` = '".$cacheDir."'" );
		###
		$_SESSION['msg1'] = 'Game Added Successfully!';
		$_SESSION['msg2'] = 'The new game has been added and is ready for use.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: configgame.php" );
		die();
		break;

	case 'configgameedit':
		$gameid = mysql_real_escape_string($_POST['gameid']);
		$gameName = mysql_real_escape_string($_POST['gameName']);
		$status = mysql_real_escape_string($_POST['status']);
		$maxSlots = mysql_real_escape_string($_POST['maxSlots']);
		$defaultPort = mysql_real_escape_string($_POST['defaultPort']);
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
        $startLine = mysql_real_escape_string( $_POST['startLine'] );
		$queryType = mysql_real_escape_string($_POST['queryType']);
		$queryPort = mysql_real_escape_string($_POST['queryPort']);
		$cacheDir = mysql_real_escape_string($_POST['cacheDir']);
		###
		//Check the inputs. Output an error if the validation failed
		$gameLength = strlen($gameName);
		###
		$error = '';
		###
		if (!is_numeric($gameid))
		{
			$error .= 'Invalid GameID. ';
		}
		else if (query_numrows( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."'" ) == 0)
		{
			$error .= 'Invalid GameID. ';
		}
		###
		if ($gameLength < 2)
		{
			$error .= 'Game Name is too short (2 Chars min.). ';
		}
		if (empty($maxSlots))
		{
			$error .= 'Max Slots is not set ! ';
		}
		else if (!is_numeric($maxSlots))
		{
			$error .= 'Max Slots is not a numeric value ! ';
		}
		if (empty($defaultPort))
		{
			$error .= 'Default Server Port is not set ! ';
		}
		else if (!is_numeric($defaultPort))
		{
			$error .= 'Default Server Port is not a numeric value ! ';
		}
		if (empty($startLine))
		{
			$error .= 'Start Command is not set ! ';
		}
		if (!array_key_exists($queryType, lgsl_type_list()))
		{
			$error .= 'Unknown Query Type ! ';
		}
		if (!empty($queryPort))
		{
			if (!is_numeric($queryPort))
			{
				$error .= 'Query Port is not a numeric value ! ';
			}
		}
		else
		{
			$queryPort = $defaultPort;
		}
		/*
		if(!validateDirPath($cacheDir))
		{
			$error .= 'Invalid Cache Directory. ';
		}
		*/
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configgameedit.php?id=".urlencode($gameid) );
			die();
		}
		###
		//Security
		$maxSlots = abs($maxSlots);
		$defaultPort = abs($defaultPort);
		$queryPort = abs($queryPort);
		###
		//Update
		query_basic( "UPDATE `".DBPREFIX."game` SET
			`game` = '".$gameName."',
			`status` = '".$status."',
			`maxslots` = '".$maxSlots."',
			`defaultport` = '".$defaultPort."',
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
			`startline` = '".$startLine."',
			`querytype` = '".$queryType."',
			`queryport` = '".$queryPort."',
			`cachedir` = '".$cacheDir."' WHERE `gameid` = '".$gameid."'" );
		###
		//Update LGSL and servers
		$servers = query_fetch_assoc( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `gameid` = '".$gameid."'" );
		###
		query_basic( "UPDATE `".DBPREFIX."server` SET
			`game` = '".$gameName."' WHERE `serverid` = '".$servers['serverid']."'" );
		###
		query_basic( "UPDATE `".DBPREFIX."lgsl` SET `type` = '".$queryType."' WHERE `id` = '".$servers['serverid']."'" );
		###
		unset($servers);
		###
		$_SESSION['msg1'] = 'Game Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the game have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: configgame.php" );
		die();
		break;

	case 'configgamedelete':
		$gameid = $_GET['id'];
		###
		$error = '';
		###
		if (!is_numeric($gameid))
		{
			$error .= 'Invalid GameID. ';
		}
		else if (query_numrows( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."'" ) == 0)
		{
			$error .= 'Invalid GameID. ';
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
		if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `gameid` = '".$gameid."'" ) != 0)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'The selected game cannot be deleted as it is currently in use by a game server. The server must be deleted first.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: configgame.php" );
			die();
		}
		###
		query_basic( "DELETE FROM `".DBPREFIX."game` WHERE `gameid` = '".$gameid."' LIMIT 1" );
		$_SESSION['msg1'] = 'Game Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected game has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: configgame.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>