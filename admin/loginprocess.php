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


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


switch(@$task)
{
	case 'processlogin':
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);
		$return = $_POST['return'];
		###
		if (!empty($username) && !empty($password))
		{
			###
			//Processing the password
			$salt = hash('sha512', $username); //Salt
			$password = hash('sha512', $salt.$password); //Hashed password with salt
			###
			$numrows = query_numrows( "SELECT `adminid` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."' AND `password` = '".$password."' AND `status` = 'Active'" );
			if ($numrows == 1)
			{
				$rows = query_fetch_assoc( "SELECT `adminid`, `username`, `firstname`, `lastname`, `access` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."' AND `password` = '".$password."' AND `status` = 'Active'" ); //Retrieve information from database
				###
				//Maintenance
				if (MAINTENANCE == 1)
				{
					if ($rows['access'] != "Super")
					{
						header( "Location: loginmaintenance.php" );
						die();
					}
				}
				###
				query_basic( "UPDATE `".DBPREFIX."admin` SET `lastlogin` = '".date('Y-m-d H:i:s')."', `lastip` = '".$_SERVER['REMOTE_ADDR']."', `lasthost` = '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."' WHERE `adminid` = '".$rows['adminid']."'" ); //Update last connection and so on
				###
				//Creation of the session's information
				$_SESSION['adminid'] = $rows['adminid'];
				$_SESSION['adminusername'] = $rows['username'];
				$_SESSION['adminfirstname'] = $rows['firstname'];
				$_SESSION['adminlastname'] = $rows['lastname'];
				###
				validateAdmin();
				###
				//Cookie
				if (isset($_POST['rememberMe']))
				{
					setcookie('adminUsername', htmlentities($username, ENT_QUOTES), time() + (86400 * 7 * 2)); // 86400 = 1 day
				}
				else if (isset($_COOKIE['adminUsername']))
				{
					setcookie('adminUsername', htmlentities($username, ENT_QUOTES), time() - 3600); // Remove the cookie
				}
				###
				if (!empty($_SESSION['loginattempt']))
				{
					unset($_SESSION['loginattempt']);
				}
				else if (!empty($_SESSION['lockout']))
				{
					unset($_SESSION['lockout']);
				}
				###
				if (!empty($return))
				{
					header( "Location: ".urldecode($return)); //Redirection to the protected resource
					die();
				}
				else
				{
					header( "Location: index.php" ); //Standard login redirection to index.php
					die();
				}
			}
			else if (query_numrows( "SELECT `adminid` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."' AND `password` = '".$password."' AND `status` = 'Suspended'" ) == 1)
			{
				header( "Location: loginsuspended.php" );
				die();
			}
		}
		$_SESSION['loginerror'] = TRUE;
		@$_SESSION['loginattempt']++;
		if (4 < $_SESSION['loginattempt'])
		{
			$_SESSION['lockout'] = time();
			$_SESSION['loginattempt'] = 0; //Reseting attempts as the user will be ban for 5 mins
			$message = '5 Incorrect Admin Login Attempts ('.$username.')';
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `message` = '".$message."', `name` = 'System Message', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		}
		header( "Location: login.php" );
		die();
		break;

	case 'processpassword':
		$username = mysql_real_escape_string($_POST['username']);
		$email = mysql_real_escape_string($_POST['email']);
		###
		/**
		 * Securimage - A PHP class for creating captcha images.
		 *
		 * VERSION: 3.0
		 * AUTHOR: Drew Phillips <drew@drew-phillips.com>
		 */
		require("../libs/securimage/securimage.php");
		$securimage = new Securimage();
		###
		if ($securimage->check($_POST['captcha_code']) == TRUE)
		{
			if (!empty($username) && !empty($email))
			{
				$numrows = query_numrows( "SELECT `adminid` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."' && `email` = '".$email."'" );
				if ($numrows == 1)
				{
					$rows = query_fetch_assoc( "SELECT `adminid`, `email` FROM `".DBPREFIX."admin` WHERE `username` = '".$username."'" );
					###
					//Processing the password
					$password = createRandomPassword(8);
					$password2 = $password; //Temp var for the email
					$salt = hash('sha512', $username); //Salt
					$password = hash('sha512', $salt.$password); //Hashed password with salt
					query_basic( "UPDATE `".DBPREFIX."admin` SET `password` = '".$password."' WHERE `adminid` = '".$rows['adminid']."'" );
					###
					$to = htmlentities($rows['email'], ENT_QUOTES);
					$subject = 'Reset Password';
					$message = "Your password has been reset to:<br /><br />{$password2}<br /><br />With IP: ".$_SERVER['REMOTE_ADDR'];
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
					unset($_SESSION['loginattempt']);
					unset($_SESSION['lockout']);
					$_SESSION['success'] = 'Yes';
					header( "Location: login.php?task=password" );
					die();
				}
			}
		}
		$_SESSION['success'] = 'No';
		$_SESSION['loginattempt']++;
		if (4 < $_SESSION['loginattempt'])
		{
			$_SESSION['lockout'] = time();
			$_SESSION['loginattempt'] = 0; //Reseting attempts as the user will be ban for 5 mins
			$message = '5 Incorrect Admin Login Attempts ('.$username.')';
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `message` = '".$message."', `name` = 'System Message', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		}
		header( "Location: login.php?task=password" );
		die();
		break;

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.
?>