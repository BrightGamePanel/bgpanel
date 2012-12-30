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
require("../includes/templates.php");


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
	case 'generaledit':
		$panelName = mysql_real_escape_string($_POST['panelName']);
		$systemUrl = mysql_real_escape_string($_POST['systemUrl']);
		$adminTemplate = mysql_real_escape_string($_POST['adminTemplate']);
		$clientTemplate = mysql_real_escape_string($_POST['clientTemplate']);
		$maintenance = mysql_real_escape_string($_POST['status']);
		###
		//Check the inputs. Output an error if the validation failed
		$panelNameLength = strlen($panelName);
		$systemUrlLength = strlen($systemUrl);
		###
		$error = '';
		###
		if ($panelNameLength == 0)
		{
			$error .= 'Panel Name is too short ! ';
		}
		if ($systemUrlLength <= 7)
		{
			$error .= 'System Url is too short ! ';
		}
		if ($maintenance != '0' && $maintenance != '1')
		{
			$error .= 'Invalid maintenance mode. ';
		}
		//---------------------------------------------------------+
		$err = 0;

		foreach ($templates as $key => $value)
		{
			if ($adminTemplate == $value)
			{
				if (is_file('../bootstrap/css/'.$value))
				{
					unset($err);
					break;
				}
			}
			$err++;
		}

		if (isset($err))
		{
			$error .= 'Invalid Admin template !';
		}
		//---------------------------------------------------------+
		$err = 0;

		foreach ($templates as $key => $value)
		{
			if ($clientTemplate == $value)
			{
				if (is_file('../bootstrap/css/'.$value))
				{
					unset($err);
					break;
				}
			}
			$err++;
		}

		if (isset($err))
		{
			$error .= 'Invalid Client template !';
		}
		//---------------------------------------------------------+
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configgeneral.php" );
			die();
		}
		###
		//Update
		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".$panelName."' WHERE `setting` = 'panelname'" );
		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".$systemUrl."' WHERE `setting` = 'systemurl'" );
		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".$adminTemplate."' WHERE `setting` = 'admintemplate'" );
		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".$clientTemplate."' WHERE `setting` = 'clienttemplate'" );
		query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".$maintenance."' WHERE `setting` = 'maintenance'" );
		###
		$_SESSION['msg1'] = 'Settings Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the settings have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: configgeneral.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>