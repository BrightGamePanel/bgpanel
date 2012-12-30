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
	case 'logout':
		if (isAdminLoggedIn() == TRUE)
		{
			logout();
			header( "Location: login.php" );
			die();
		}
		else
		{
			exit('Not logged in');
		}
		break;

	case 'myaccount':
		$adminid = mysql_real_escape_string($_POST['adminid']);
		$firstname = mysql_real_escape_string($_POST['firstname']);
		$firstname = ucwords($firstname); //Format the first name as a proper noun
		$lastname = mysql_real_escape_string($_POST['lastname']);
		$lastname = ucwords($lastname); //Format the last name as a proper noun
		$email = mysql_real_escape_string($_POST['email']);
		$email = strtolower($email); //Format the email to lower case
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);
		$password2 = mysql_real_escape_string($_POST['password2']);
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
			$error .= 'Invalid AdminID. ';
		}
		else if (query_numrows( "SELECT `username` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."'" ) == 0)
		{
			$error .= 'Invalid AdminID. ';
		}
		if ($firstnameLength < 2)
		{
			$error .= 'Firstname is too short (2 Chars min.). ';
		}
		if (checkEmail($email) == FALSE)
		{
			$error .= 'Invalid Email. ';
		}
		if ($usernameLength < 5)
		{
			$error .= 'Username is too short (5 Chars min.). ';
		}
		else if (query_numrows( "SELECT `status` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."' && `adminid` != '".$adminid."'" ) != 0)
		{
			$error .= 'Username is already in use by another administrator. ';
		}
		if (!empty($password))
		{
			if ($passwordLength <= 3)
			{
				$error .= 'Password is unsecure. ';
			}
			if ($password != $password2)
			{
				$error .= "Passwords don't match. ";
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: myaccount.php" );
			die();
		}
		###
		//Processing password
		if (empty($password))
		{
			query_basic( "UPDATE `".DBPREFIX."admin` SET `username` = '".$username."', `firstname` = '".$firstname."', `lastname` = '".$lastname."', `email` = '".$email."' WHERE `adminid` = '".$adminid."'" );
		}
		else
		{
			$salt = hash('sha512', $username); //Salt
			$password = hash('sha512', $salt.$password); //Hashed password with salt
			query_basic( "UPDATE `".DBPREFIX."admin` SET `username` = '".$username."', `firstname` = '".$firstname."', `lastname` = '".$lastname."', `email` = '".$email."', `password` = '".$password."' WHERE `adminid` = '".$adminid."'" );
		}
		###
		//Refresh session's information if the connected user has edited his profile
		$_SESSION['adminusername'] = $username;
		$_SESSION['adminfirstname'] = $firstname;
		$_SESSION['adminlastname'] = $lastname;
		###
		$_SESSION['msg1'] = 'Account Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to your account have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: index.php" );
		die();
		break;

	case 'personalnotes':
		$adminid = mysql_real_escape_string($_POST['adminid']);
		$notes = mysql_real_escape_string($_POST['notes']);
		###
		$error = '';
		###
		if (!is_numeric($adminid))
		{
			$error .= 'Invalid AdminID. ';
		}
		else if (query_numrows( "SELECT `username` FROM `".DBPREFIX."admin` WHERE `adminid` = '".$adminid."'" ) == 0)
		{
			$error .= 'Invalid AdminID. ';
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
		query_basic( "UPDATE `".DBPREFIX."admin` SET `notes` = '".$notes."' WHERE `adminid` = '".$adminid."'" );
		###
		$_SESSION['msg1'] = 'Personal Notes Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to your personal notes have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: index.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>