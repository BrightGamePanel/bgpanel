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
 * Validating a User
 *
 * This function simply sets the session variable "validclient" / "validadmin"
 *
 * http://tinsology.net/2009/06/creating-a-secure-login-system-the-right-way/
 */
function validateAdmin()
{
	//this is a security measure
	session_regenerate_id();
	###
	$token = session_id();
	###
	mysql_query( "UPDATE `".DBPREFIX."admin` SET `token` = '".$token."' WHERE `adminid` = '".$_SESSION['adminid']."'" );
}

function validateClient()
{
	session_regenerate_id();
	###
	$token = session_id();
	###
	mysql_query( "UPDATE `".DBPREFIX."client` SET `token` = '".$token."' WHERE `clientid` = '".$_SESSION['clientid']."'" );
}



/**
 * Checking if a User is Logged In
 *
 * This function checks the session variable "validclient" / "validadmin"
 *
 * http://tinsology.net/2009/06/creating-a-secure-login-system-the-right-way/
 */
function isAdminLoggedIn()
{
	if (!empty($_SESSION['adminid']) && is_numeric($_SESSION['adminid']))
	{
		$adminverify = mysql_query( "SELECT `username` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$_SESSION['adminid']."' && `status` = 'Active'" );
		if (mysql_num_rows($adminverify) == 1)
		{
			return TRUE;
		}
		unset($adminverify);
	}
	return FALSE;
}

function isClientLoggedIn()
{
	if (!empty($_SESSION['clientid']) && is_numeric($_SESSION['clientid']))
	{
		$clientverify = mysql_query( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$_SESSION['clientid']."' && `status` = 'Active'" );
		if (mysql_num_rows($clientverify) == 1)
		{
			return TRUE;
		}
		unset($clientverify);
	}
	return FALSE;
}



/**
 * Logging Out
 *
 * http://tinsology.net/2009/06/creating-a-secure-login-system-the-right-way/
 */
function logout()
{
	$_SESSION = array(); //Destroy session variables
	session_destroy();
}

?>