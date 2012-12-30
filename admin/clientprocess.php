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
	case 'clientadd':
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);
		$firstname = mysql_real_escape_string($_POST['firstname']);
		$firstname = ucwords($firstname); //Format the first name as a proper noun
		$lastname = mysql_real_escape_string($_POST['lastname']);
		$lastname = ucwords($lastname); //Format the last name as a proper noun
		$email = mysql_real_escape_string($_POST['email']);
		$email = strtolower($email); //Format the email to lower case
		$notes = mysql_real_escape_string($_POST['notes']);
		if (isset($_POST['sendemail'])) {
			$sendemail = 'on';
		} else {
			$sendemail = '';
		}
		###
		//Used to fill in the blanks of the form
		$_SESSION['username'] = $username;
		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;
		$_SESSION['notes'] = $notes;
		###
		//Check the inputs. Output an error if the validation failed
		$usernameLength = strlen($username);
		$passwordLength = strlen($password);
		###
		$error = '';
		###
		if ($usernameLength < 5)
		{
			$error .= 'Username is too short (5 Chars min.). ';
		}
		else if (query_numrows( "SELECT `clientid` FROM `".DBPREFIX."client` WHERE `username` = '".$username."'" ) != 0)
		{
			$error .= 'Username is already in use. ';
		}
		if ((!empty($password)) && ($passwordLength <= 3))
		{
			$error .= 'Password is unsecure. ';
		}
		if (checkEmail($email) == FALSE)
		{
			$error .= 'Invalid Email. ';
		}
		if (empty($password) && empty($sendemail))
		{
			$error .= 'You must send an email for a random password.';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: clientadd.php" );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['username']);
		unset($_SESSION['firstname']);
		unset($_SESSION['lastname']);
		unset($_SESSION['email']);
		unset($_SESSION['notes']);
		###
		//Adding client to the database
		if (empty($password))
		{
			$password = createRandomPassword(8);
		}
		$password2 = $password; //Temp var for the email
		$salt = hash('sha512', $username); //Salt
		$password = hash('sha512', $salt.$password); //Hashed password with salt
		query_basic( "INSERT INTO `".DBPREFIX."client` SET
			`username` = '".$username."',
			`password` = '".$password."',
			`firstname` = '".$firstname."',
			`lastname` = '".$lastname."',
			`email` = '".$email."',
			`notes` = '".$notes."',
			`status` = 'Active',
			`lastlogin` = '0000-00-00 00:00:00',
			`lastactivity` = '0',
			`lastip` = '~',
			`lasthost` = '~',
			`created` = '".date('Y-m-d')."',
			`token`= ''" );
		###
		//Adding event to the database
		$clientid = mysql_insert_id();
		$message = "New Client Added: ".$username;
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `clientid` = '".$clientid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		if ($sendemail == 'on')
		{
			$systemurl = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'systemurl' LIMIT 1" );
			###
			$to = $email;
			$subject = 'Game Panel Account Information';
			$message = "Dear {$firstname} {$lastname},<br /><br /><u>Here is your account login details:</u><br />Username: {$username}<br />Email Address: {$email}<br />Password: {$password2}<br />Game Panel Link: ".$systemurl['value'];
			###
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: Bright Game Panel System <localhost@'.$_SERVER['SERVER_NAME'].'>' . "\r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			#-----------------+
			$mail = mail($to, $subject, $message, $headers);
			#-----------------+
			if(!$mail)
			{
			   exit("<h1><b>Error: message could not be sent.</b></h1>");
			}
			###
			//Message has been sent
		}
		###
		$_SESSION['msg1'] = 'Client Added Successfully!';
		$_SESSION['msg2'] = 'The new client account has been added and is ready for use.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: clientsummary.php?id=".urlencode($clientid) );
		die();
		break;

	case 'clientprofile':
		$clientid = mysql_real_escape_string($_POST['clientid']);
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);
		$status = mysql_real_escape_string($_POST['status']);
		$firstname = mysql_real_escape_string($_POST['firstname']);
		$firstname = ucwords($firstname); //Format the first name as a proper noun
		$lastname = mysql_real_escape_string($_POST['lastname']);
		$lastname = ucwords($lastname); //Format the last name as a proper noun
		$email = mysql_real_escape_string($_POST['email']);
		$email = strtolower($email); //Format the email to lower case
		if (isset($_POST['sendemail']))	{
			$sendemail = 'on';
		} else {
			$sendemail = '';
		}
		###
		//Check the inputs. Output an error if the validation failed
		$usernameLength = strlen($username);
		$passwordLength = strlen($password);
		###
		$error = '';
		###
		if (!is_numeric($clientid))
		{
			$error .= 'Invalid ClientID. ';
		}
		else if (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) == 0)
		{
			$error .= 'Invalid ClientID. ';
		}
		###
		if ($usernameLength < 5)
		{
			$error .= 'Username is too short (5 Chars min.). ';
		}
		else if (query_numrows( "SELECT `status` FROM `".DBPREFIX."client` WHERE `username` = '".$username."' && `clientid` != '".$clientid."'" ) != 0)
		{
			$error .= 'Username is already in use. ';
		}
		if ((!empty($password)) && ($passwordLength <= 3))
		{
			$error .= 'Password is unsecure. ';
		}
		if (checkEmail($email) == FALSE)
		{
			$error .= 'Invalid Email. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error! Form has been reset!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: clientprofile.php?id=".urlencode($clientid));
			die();
		}
		###
		if (empty($password))
		{
			query_basic( "UPDATE `".DBPREFIX."client` SET
				`username` = '".$username."',
				`firstname` = '".$firstname."',
				`lastname` = '".$lastname."',
				`email` = '".$email."',
				`status` = '".$status."' WHERE `clientid` = '".$clientid."'" );
		}
		else
		{
			$password2 = $password; //Temp var for the email
			$salt = hash('sha512', $username); //Salt
			$password = hash('sha512', $salt.$password); //Hashed password with salt
			query_basic( "UPDATE `".DBPREFIX."client` SET
				`username` = '".$username."',
				`password` = '".$password."',
				`firstname` = '".$firstname."',
				`lastname` = '".$lastname."',
				`email` = '".$email."',
				`status` = '".$status."' WHERE `clientid` = '".$clientid."'" );
		}
		###
		//Adding event to the database
		$message = "Client Edited: ".$username." (by Admin)";
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `clientid` = '".$clientid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		if ($sendemail == 'on')
		{
			$systemurl = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'systemurl' LIMIT 1" );
			###
			$to = $email;
			$subject = 'Game Panel Account Information';
			$message = "Dear {$firstname} {$lastname},<br /><br /><u>Here is your new account login details:</u><br />Username: {$username}<br />Email Address: {$email}<br />Password: {$password2}<br />Game Panel Link: ".$systemurl['value'];
			###
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: Bright Game Panel System <localhost@'.$_SERVER['SERVER_NAME'].'>' . "\r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			#-----------------+
			$mail = mail($to, $subject, $message, $headers);
			#-----------------+
			if(!$mail)
			{
			   exit("<h1><b>Error: message could not be sent.</b></h1>");
			}
			###
			//Message has been sent
		}
		###
		$_SESSION['msg1'] = 'Client Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the client have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: clientsummary.php?id=".urlencode($clientid) );
		die();
		break;

	case 'clientdelete':
		$clientid = $_GET['id'];
		###
		$error = '';
		###
		if (!is_numeric($clientid))
		{
			$error .= 'Invalid ClientID. ';
		}
		else if (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) == 0)
		{
			$error .= 'Invalid ClientID. ';
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
		$username = query_fetch_assoc( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."' LIMIT 1" );
		###
		query_basic( "DELETE FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."' LIMIT 1" );
		###
		//We have to remove the client from any associated group
		query_basic( "DELETE FROM `".DBPREFIX."groupMember` WHERE `clientid` = '".$clientid."' LIMIT 1" );
		###
		//Adding event to the database
		$message = 'Client Deleted: '.mysql_real_escape_string($username['username']);
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `clientid` = '".$clientid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Client Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected client has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: client.php" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>