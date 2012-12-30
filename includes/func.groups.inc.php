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


/**
 * GENERAL RULES and KNOWLEDGE:
 *
 * - Groups are reserved to CLIENTS.
 * - A same CLIENT can be linked to multiple groups.
 * - A SERVER MUST be linked to only ONE GROUP.
 * - GroupIDS FORMAT: "ID;"
 */



/**
 * Check if the specified Client is linked to the specified group
 *
 * Return TRUE if yes, FALSE if not
 *
 * Return also FALSE if the GROUPID is invalid OR if the Client doesn't exists
 */
function checkClientGroup($groupid, $clientid)
{
	if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) != 0) // We check if the group exists
	{
		// The specified group exists

		if ( (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) != 0) && (query_numrows( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" ) != 0) ) // We check if our client exists
		{
			// The specified client exists

			$groupids = query_fetch_assoc( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" );
			$groupidsTable = explode(';', $groupids['groupids']); // CSV

			foreach($groupidsTable as $value)
			{
				if ($value == $groupid) // Finally, we check if the client is member of the group
				{
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}



/**
 * Retrieve all Client's groups in an array
 *
 * Return FALSE if the specified Client is NOT linked to a Group OR if the GROUPID is invalid OR if the Client doesn't exists
 */
function getClientGroups($clientid)
{
	if ( (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) != 0) && (query_numrows( "SELECT `id` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" ) != 0) ) // We check if our client exists
	{
		// The specified client exists

		$groupids = query_fetch_assoc( "SELECT `groupids` FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."'" );
		$groupidsTable = explode(';', $groupids['groupids']); // CSV

		$i = 0;
		foreach($groupidsTable as $value)
		{
			if (!empty($value))
			{
				$groups[$i] = $value;
				$i++;
			}
		}

		if (isset($groups))
		{
			return $groups;
		}
	}
	return FALSE;
}



/**
 * Retrieve all Group's servers in a multi- dimensional array
 *
 * Return FALSE if the GROUPID is invalid OR if the Group doesn't have servers
 */
function getGroupServers($groupid)
{
	if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) != 0) // We check if the group exists
	{
		// The specified group exists

		if (query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `groupid` = '".$groupid."'" ) > 0) // We check if the group has servers
		{
			// The specified group has servers

			$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `groupid` = '".$groupid."'" );

			$i = 0;
			while ($rowsServers = mysql_fetch_assoc($servers))
			{
				$groupServers[$i] = $rowsServers;
				$i++;
			}

			return $groupServers;
		}
	}
	return FALSE;
}



/**
 * Retrieve all Group's clients in a multi- dimensional array
 *
 * Return FALSE if the GROUPID is invalid OR if the Group doesn't have clients OR if there is no members in the table
 */
function getGroupClients($groupid)
{
	if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) != 0) // We check if the group exists
	{
		// The specified group exists

		if (query_numrows( "SELECT `id` FROM `".DBPREFIX."groupMember`" ) > 0)
		{
			// There is at least one member

			$members = mysql_query( "SELECT `clientid`, `groupids` FROM `".DBPREFIX."groupMember`" );

			$i = 0;
			while ($rowsMembers = mysql_fetch_assoc($members))
			{
				if (!empty($rowsMembers['clientid']))
				{
					$groupids = explode(';', $rowsMembers['groupids']); // CSV

					foreach($groupids as $value)
					{
						if ($value == $groupid) // MATCH case
						{
							$clients[$i] = $rowsMembers['clientid'];
							$i++;
							break;
						}
					}
				}
			}

			if (isset($clients))
			{
				return $clients;
			}
		}
	}
	return FALSE;
}

?>