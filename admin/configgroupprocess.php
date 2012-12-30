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
	case 'configgroupadd':
		$name = mysql_real_escape_string($_POST['name']);
		$description = mysql_real_escape_string($_POST['notes']);
		###
		//Used to fill in the blanks of the form
		$_SESSION['name'] = $name;
		$_SESSION['notes'] = $description;
		###
		//Check the inputs. Output an error if the validation failed
		$nameLength = strlen($name);
		###
		$error = '';
		###
		if ($nameLength < 2)
		{
			$error .= 'Group Name is too short (2 Chars min.). ';
		}
		if (query_numrows( "SELECT `groupid` FROM `".DBPREFIX."group` WHERE `name` = '".$name."'" ) != 0)
		{
			$error .= 'This name is already in use !';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configgroupadd.php" );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['name']);
		unset($_SESSION['notes']);
		###
		//Adding group to the database
		query_basic( "INSERT INTO `".DBPREFIX."group` SET `name` = '".$name."', `description` = '".$description."'" );
		###
		$groupid = mysql_insert_id();
		###
		$_SESSION['msg1'] = 'Group Added Successfully!';
		$_SESSION['msg2'] = 'The new group has been added but you have to edit it to add members.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: configgroupedit.php?id=".urlencode($groupid) );
		die();
		break;

	case 'configgroupedit':
		$groupid = mysql_real_escape_string($_POST['groupid']);
		$name = mysql_real_escape_string($_POST['name']);
		$description = mysql_real_escape_string($_POST['notes']);
		if (is_numeric($groupid))
		{
			if (getGroupClients($groupid) != FALSE)
			{
				$clients = getGroupClients($groupid);
				foreach($clients as $key => $value)
				{
					if (isset($_POST['removeid'.$key]))
					{
						$removeids[] = $value;
					}
				}
				unset($clients);
			}
		}
		$newClient = mysql_real_escape_string($_POST['newClient']);
		###
		//Check the inputs. Output an error if the validation failed
		$nameLength = strlen($name);
		###
		$error = '';
		###
		if (!is_numeric($groupid))
		{
			$error .= 'Invalid GroupID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
		{
			$error .= 'Invalid GroupID. ';
		}
		###
		if ($nameLength < 2)
		{
			$error .= 'Group Name is too short (2 Chars min.). ';
		}
		if ($newClient != '-Select-')
		{
			if (query_numrows( "SELECT `clientid` FROM `".DBPREFIX."client` WHERE `username` = '".$newClient."'" ) == 0)
			{
				$error .= 'Invalid Client Username '.$newClient.'. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configgroupedit.php?id=".urlencode($groupid) );
			die();
		}
		###
		if ($newClient == '-Select-')
		{
			// Update group
			query_basic( "UPDATE `".DBPREFIX."group` SET `name` = '".$name."', `description` = '".$description."' WHERE `groupid` = '".$groupid."'" );
			###
			if (isset($removeids))
			{
				// Remove clients
				foreach($removeids as $key => $value)
				{
					$groupids = query_fetch_assoc( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$value."'" );
					###
					$groupids['groupids'] = str_replace( $groupid.';', '', $groupids['groupids'] );
					###
					if (empty($groupids['groupids']))
					{
						query_basic( "DELETE FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$value."' LIMIT 1" );
					}
					else
					{
						query_basic( "UPDATE `".DBPREFIX."groupMember` SET `groupids` = '".$groupids['groupids']."' WHERE `clientid` = '".$value."'" );
					}
					unset($groupids);
				}
			}
			###
			$_SESSION['msg1'] = 'Group Updated Successfully!';
			$_SESSION['msg2'] = 'Your changes to the group have been saved.';
			$_SESSION['msg-type'] = 'success';
		}
		else
		{
			// Adding a new client
			$clientid = query_fetch_assoc( "SELECT `clientid` FROM `".DBPREFIX."client` WHERE `username` = '".$newClient."'" );
			###
			if (!checkClientGroup($groupid, $clientid['clientid']))
			{
				if (query_numrows( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid['clientid']."'" ) == 0)
				{
					query_basic( "INSERT INTO `".DBPREFIX."groupMember` SET `clientid` = '".$clientid['clientid']."', `groupids` = '".$groupid.";'" );
				}
				else
				{
					$groupids = query_fetch_assoc( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid['clientid']."'" );
					###
					query_basic( "UPDATE `".DBPREFIX."groupMember` SET `groupids` = '".$groupids['groupids'].$groupid.";' WHERE `clientid` = '".$clientid['clientid']."'" );
					###
					unset($groupids);
				}
			}
			unset($clientid);
			###
			$_SESSION['msg1'] = 'New Client Successfully Added!';
			$_SESSION['msg2'] = $newClient.' has been added to the group.';
			$_SESSION['msg-type'] = 'success';
		}
		header( "Location: configgroupedit.php?id=".urlencode($groupid) );
		die();
		break;

	case 'configgroupdelete':
		$groupid = $_GET['id'];
		###
		$error = '';
		###
		if (!is_numeric($groupid))
		{
			$error .= 'Invalid GroupID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
		{
			$error .= 'Invalid GroupID. ';
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
		if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `groupid` = '".$groupid."'" ) != 0)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'The selected group cannot be deleted as it is currently linked with a game server. The server must be deleted first.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: configgroup.php" );
			die();
		}
		###
		if (getGroupClients($groupid) != FALSE)
		{
			$clients = getGroupClients($groupid);
			foreach($clients as $key => $value)
			{
				$removeids[] = $value;
			}
			unset($clients);
		}
		###
		if (isset($removeids))
		{
			// Remove groupID from groupMember table
			foreach($removeids as $key => $value)
			{
				$groupids = query_fetch_assoc( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$value."'" );
				###
				$groupids['groupids'] = str_replace( $groupid.';', '', $groupids['groupids'] );
				###
				if (empty($groupids['groupids']))
				{
					query_basic( "DELETE FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$value."' LIMIT 1" );
				}
				else
				{
					query_basic( "UPDATE `".DBPREFIX."groupMember` SET `groupids` = '".$groupids['groupids']."' WHERE `clientid` = '".$value."'" );
				}
				unset($groupids);
			}
		}
		###
		query_basic( "DELETE FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."' LIMIT 1" );
		###
		$_SESSION['msg1'] = 'Group Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected group has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: configgroup.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>