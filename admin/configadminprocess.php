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
	case 'configadminadd':
		$access = mysql_real_escape_string($_POST['access']);
		$firstname = mysql_real_escape_string($_POST['firstname']);
		$firstname = ucwords(mysql_real_escape_string($firstname)); //Format the first name as a proper noun
		$lastname = mysql_real_escape_string($_POST['lastname']);
		$lastname = ucwords(mysql_real_escape_string($lastname)); //Format the last name as a proper noun
		$email = mysql_real_escape_string($_POST['email']);
		$email = strtolower($email); //Format the email to lower case
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);
		$password2 = mysql_real_escape_string($_POST['password2']);
		###
		//Used to fill in the blanks of the form
		$_SESSION['access'] = $access;
		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;
		$_SESSION['username'] = $username;
		###
		//Check the inputs. Output an error if the validation failed
		$firstnameLength = strlen($firstname);
		$usernameLength = strlen($username);
		$passwordLength = strlen($password);
		###
		$error = '';
		###
		if ($firstnameLength < 2)
		{
			$error .= T_('Firstname is too short (2 Chars min.). ');
		}
		if (checkEmail($email) == FALSE)
		{
			$error .= T_('Invalid Email. ');
		}
		if ($usernameLength < 4)
		{
			$error .= T_('Username is too short (4 Chars min.). ');
		}
		else if (query_numrows( "SELECT `adminid` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."'" ) != 0)
		{
			$error .= T_('Username is already in use. ');
		}
		if ($passwordLength <= 3)
		{
			$error .= T_('Password is unsecure or not set. ');
		}
		else if ($password != $password2)
		{
			$error .= T_("Passwords don't match.");
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configadminadd.php" );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['access']);
		unset($_SESSION['firstname']);
		unset($_SESSION['lastname']);
		unset($_SESSION['email']);
		unset($_SESSION['username']);
		###
		//Adding administrator to the database
		$salt = hash('sha512', $username); //Salt
		$password = hash('sha512', $salt.$password); //Hashed password with salt
		query_basic( "INSERT INTO `".DBPREFIX."admin` SET
			`username` = '".$username."',
			`firstname` = '".$firstname."',
			`lastname` = '".$lastname."',
			`email` = '".$email."',
			`password` = '".$password."',
			`access` = '".$access."',
			`notes` = '',
			`status` = 'Active',
			`lang` = '".DEFAULT_LOCALE."',
			`lastlogin` = '0000-00-00 00:00:00',
			`lastactivity` = '0',
			`lastip` = '~',
			`lasthost` = '~',
			`token` = ''" );
		###
		$_SESSION['msg1'] = T_('Admin Added Successfully!');
		$_SESSION['msg2'] = T_('The new admin account has been added and is ready for use.');
		$_SESSION['msg-type'] = 'success';
		header( "Location: configadmin.php" );
		die();
		break;

	case 'configadminedit':
		$adminid = mysql_real_escape_string($_POST['adminid']);
		$access = mysql_real_escape_string($_POST['access']);
		$firstname = mysql_real_escape_string($_POST['firstname']);
		$firstname = ucwords(mysql_real_escape_string($firstname)); //Format the first name as a proper noun
		$lastname = mysql_real_escape_string($_POST['lastname']);
		$lastname = ucwords(mysql_real_escape_string($lastname)); //Format the last name as a proper noun
		$email = mysql_real_escape_string($_POST['email']);
		$email = strtolower($email); //Format the email to lower case
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);
		$password2 = mysql_real_escape_string($_POST['password2']);
		$status = mysql_real_escape_string($_POST['status']);
		###
		//Check the inputs. Output an error if the validation failed
		$firstnameLength = strlen($firstname);
		$usernameLength = strlen($username);
		$passwordLength = strlen($password);
		###
		$error = '';
		###
		if (!is_numeric($adminid))
		{
			$error .= T_('Invalid AdminID. ');
		}
		else if (query_numrows( "SELECT `username` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."'" ) == 0)
		{
			$error .= T_('Invalid AdminID. ');
		}
		###
		if ($firstnameLength < 2)
		{
			$error .= T_('Firstname is too short (2 Chars min.). ');
		}
		if (checkEmail($email) == FALSE)
		{
			$error .= T_('Invalid Email. ');
		}
		if ($usernameLength < 4)
		{
			$error .= T_('Username is too short (4 Chars min.). ');
		}
		else if (query_numrows( "SELECT `status` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."' && `adminid` != '".$adminid."'" ) != 0)
		{
			$error .= T_('Username is already in use by another administrator. ');
		}
		if (empty($password)) {
			$error .= T_('No password. ');
		}
		else {
			if ($passwordLength <= 3)
			{
				$error .= T_('Password is unsecure. ');
			}
				else if ($password != $password2)
			{
				$error .= T_("Passwords don't match. ");
			}
		}
		if ($adminid == $_SESSION['adminid'])
		{
			$error .= T_("You cannot change your information yourself. You should use")." <a href=\"myaccount.php\">".T_('My Account')."</a> ".T_("instead.");
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error! Form has been reset!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: configadminedit.php?id=".urlencode($adminid));
			die();
		}
		###
		//Processing password
		$salt = hash('sha512', $username); //Salt
		$password = hash('sha512', $salt.$password); //Hashed password with salt
		query_basic( "UPDATE `".DBPREFIX."admin` SET
			`username` = '".$username."',
			`firstname` = '".$firstname."',
			`lastname` = '".$lastname."',
			`email` = '".$email."',
			`password` = '".$password."',
			`access` = '".$access."',
			`status` = '".$status."' WHERE `adminid` = '".$adminid."'" );

		if ($status == "Suspended") {

			/**
			 * Update AJXP
			 */
			require_once("../libs/ajxp/bridge.php");

			// AJXP Bridge
			$AJXP_Bridge = new AJXP_Bridge( array(), array(), $username );

			// Update Workspaces
			$AJXP_Bridge->updateAJXPUser();

			unset($AJXP_Bridge);

		}

		$_SESSION['msg1'] = T_('Admin Updated Successfully!');
		$_SESSION['msg2'] = T_('Your changes to the admin have been saved.');
		$_SESSION['msg-type'] = 'success';
		header( "Location: configadmin.php" );
		die();
		break;

	case 'configadmindelete':
		$adminid = mysql_real_escape_string($_GET['id']);
		###
		$error = '';
		###
		if (!is_numeric($adminid))
		{
			$error .= T_('Invalid AdminID. ');
		}
		else if (query_numrows( "SELECT `adminid` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."'" ) == 0)
		{
			$error .= T_('Invalid AdminID. ');
		}
		if ($adminid == $_SESSION['adminid'])
		{
			$error .= T_('You cannot delete yourself!');
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: index.php" );
			die();
		}

		$username = query_fetch_assoc( "SELECT `username` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."' LIMIT 1" );

		query_basic( "DELETE FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."' LIMIT 1" );

		/**
		 * Update AJXP
		 */
		require_once("../libs/ajxp/bridge.php");

		// AJXP Bridge
		$AJXP_Bridge = new AJXP_Bridge( array(), array(), $username['username'] );

		// Update Workspaces
		$AJXP_Bridge->updateAJXPUser();

		unset($AJXP_Bridge);

		$_SESSION['msg1'] = T_('Admin Deleted Successfully!');
		$_SESSION['msg2'] = T_('The selected admin has been removed.');
		$_SESSION['msg-type'] = 'success';
		header( "Location: configadmin.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>