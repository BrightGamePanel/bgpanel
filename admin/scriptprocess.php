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
require_once("../libs/phpseclib/SSH2.php");
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
	case 'scriptadd':
		$groupid = mysql_real_escape_string($_POST['groupID']);
		$boxid = mysql_real_escape_string($_POST['boxID']);
		$catid = mysql_real_escape_string($_POST['catID']);
		$name = mysql_real_escape_string($_POST['name']);
		$description = mysql_real_escape_string($_POST['description']);
		$filename = mysql_real_escape_string($_POST['file']);
		$startline = mysql_real_escape_string($_POST['startLine']);
		$mode = mysql_real_escape_string($_POST['mode']);
		$homedir = mysql_real_escape_string($_POST['homeDir']);
		###
		//Used to fill in the blanks of the form
		$_SESSION['groupid'] = $groupid;
		$_SESSION['boxid'] = $boxid;
		$_SESSION['catid'] = $catid;
		$_SESSION['name'] = $name;
		$_SESSION['description'] = $description;
		$_SESSION['file'] = $filename;
		$_SESSION['startline'] = $startline;
		$_SESSION['mode'] = $mode;
		$_SESSION['homedir'] = $homedir;
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (empty($name))
		{
			$error .= 'No script name specified. ';
		}
		else if (query_numrows( "SELECT `scriptid` FROM `".DBPREFIX."script` WHERE `name` = '".$name."'" ) != 0)
		{
			$error .= 'This name is already in use ! ';
		}
		if ($groupid != 'none')
		{
			if (!is_numeric($groupid))
			{
				$error .= 'Invalid GroupID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
			{
				$error .= 'Invalid GroupID. ';
			}
		}
		if (!is_numeric($boxid))
		{
			$error .= 'BoxID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		if (!is_numeric($catid))
		{
			$error .= 'CatID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$catid."'" ) == 0)
		{
			$error .= 'Invalid CatID. ';
		}
		if (empty($filename))
		{
			$error .= 'No script specified. ';
		}
		if (empty($startline))
		{
			$error .= 'Start command is not specified. ';
		}
		else if (!preg_match("#\{script\}#", $startline))
		{
			$error .= "Invalid Start Command: alias &#123;script&#125; not found. ";
		}
		if (empty($homedir))
		{
			$error .= 'Home Directory is not specified. ';
		}
		else if(!validateDirPath($homedir))
		{
			$error .= 'Invalid Home Directory. ';
		}
		if ( ($mode != '0') && ($mode != '1') ) // NoHup / Screen
		{
			$error .= 'Invalid ExecMode. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: scriptadd.php' );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['groupid']);
		unset($_SESSION['boxid']);
		unset($_SESSION['catid']);
		unset($_SESSION['name']);
		unset($_SESSION['description']);
		unset($_SESSION['file']);
		unset($_SESSION['startline']);
		unset($_SESSION['mode']);
		unset($_SESSION['homedir']);
		###
		/**
		 * Adding the script to the database
		 */
		###
		if ($mode == '0') // NoHup Case
		{
			query_basic( "INSERT INTO `".DBPREFIX."script` SET
				`boxid` = '".$boxid."',
				`catid` = '".$catid."',
				`name` = '".$name."',
				`description` = '".$description."',
				`status` = 'Pending',
				`startline` = '".$startline."',
				`filename` = '".$filename."',
				`homedir` = '".$homedir."',
				`type` = '".$mode."'" );
		}
		else if ($mode == '1') // Screen Case
		{
			query_basic( "INSERT INTO `".DBPREFIX."script` SET
				`boxid` = '".$boxid."',
				`catid` = '".$catid."',
				`name` = '".$name."',
				`description` = '".$description."',
				`status` = 'Pending',
				`panelstatus` = 'Stopped',
				`startline` = '".$startline."',
				`filename` = '".$filename."',
				`homedir` = '".$homedir."',
				`type` = '".$mode."',
				`screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $name)."'" );
		}
		###
		$scriptid = mysql_insert_id();
		###
		if ($groupid != 'none')
		{
			query_basic( "UPDATE `".DBPREFIX."script` SET `groupid` = '".$groupid."' WHERE `scriptid` = '".$scriptid."'" );
		}
		###
		$_SESSION['msg1'] = 'Script Added Successfully!';
		$_SESSION['msg2'] = 'The new script has been added but must be validated.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
		die();
		break;

	case 'scriptprofile':
		$scriptid = mysql_real_escape_string($_POST['scriptid']);
		$groupid = mysql_real_escape_string($_POST['groupID']);
		$boxid = mysql_real_escape_string($_POST['boxID']);
		$catid = mysql_real_escape_string($_POST['catID']);
		$name = mysql_real_escape_string($_POST['name']);
		$status = mysql_real_escape_string($_POST['status']);
		$description = mysql_real_escape_string($_POST['description']);
		$filename = mysql_real_escape_string($_POST['file']);
		$startline = mysql_real_escape_string($_POST['startLine']);
		$mode = mysql_real_escape_string($_POST['mode']);
		$homedir = mysql_real_escape_string($_POST['homeDir']);
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (!is_numeric($scriptid))
		{
			$error .= 'Invalid ScriptID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
		{
			$error .= 'Invalid ScriptID. ';
		}
		###
		if ($status != 'Active')
		{
			if ($status != 'Inactive')
			{
				if ($status != 'Pending')
				{
					$error .= 'Invalid status. ';
				}
			}
		}
		###
		$pstatus = query_fetch_assoc( "SELECT `panelstatus` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'");
		if ($pstatus['panelstatus'] == 'Started')
		{
			$error .= 'Cannot edit the script while this one is running. ';
		}
		unset($pstatus);
		###
		if (empty($name))
		{
			$error .= 'No script name specified. ';
		}
		else if (query_numrows( "SELECT `scriptid` FROM `".DBPREFIX."script` WHERE `name` = '".$name."' && `boxid` = '".$boxid."' && `scriptid` != '".$scriptid."'" ) != 0)
		{
			$error .= 'This name is already in use ! ';
		}
		if ($groupid != 'none')
		{
			if (!is_numeric($groupid))
			{
				$error .= 'Invalid GroupID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$groupid."'" ) == 0)
			{
				$error .= 'Invalid GroupID. ';
			}
		}
		if (!is_numeric($boxid))
		{
			$error .= 'BoxID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
		{
			$error .= 'Invalid BoxID. ';
		}
		if (!is_numeric($catid))
		{
			$error .= 'CatID is not valid. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$catid."'" ) == 0)
		{
			$error .= 'Invalid CatID. ';
		}
		if (empty($filename))
		{
			$error .= 'No script specified. ';
		}
		if (empty($startline))
		{
			$error .= 'Start command is not specified. ';
		}
		else if (!preg_match("#\{script\}#", $startline))
		{
			$error .= "Invalid Start Command: alias &#123;script&#125; not found. ";
		}
		if (empty($homedir))
		{
			$error .= 'Home Directory is not specified. ';
		}
		else if(!validateDirPath($homedir))
		{
			$error .= 'Invalid Home Directory. ';
		}
		if ( ($mode != '0') && ($mode != '1') ) // NoHup / Screen
		{
			$error .= 'Invalid ExecMode. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: scriptprofile.php?id='.urlencode($scriptid) );
			die();
		}
		###
		if ($mode == '0') // NoHup Case
		{
			query_basic( "UPDATE `".DBPREFIX."script` SET
				`boxid` = '".$boxid."',
				`catid` = '".$catid."',
				`name` = '".$name."',
				`description` = '".$description."',
				`status` = '".$status."',
				`panelstatus` = NULL,
				`startline` = '".$startline."',
				`filename` = '".$filename."',
				`homedir` = '".$homedir."',
				`type` = '".$mode."' WHERE `scriptid` = '".$scriptid."'" );
		}
		else if ($mode == '1') // Screen Case
		{
			query_basic( "UPDATE `".DBPREFIX."script` SET
				`boxid` = '".$boxid."',
				`catid` = '".$catid."',
				`name` = '".$name."',
				`description` = '".$description."',
				`status` = '".$status."',
				`panelstatus` = 'Stopped',
				`startline` = '".$startline."',
				`filename` = '".$filename."',
				`homedir` = '".$homedir."',
				`type` = '".$mode."',
				`screen` = '".preg_replace('#[^a-zA-Z0-9]#', "_", $name)."' WHERE `scriptid` = '".$scriptid."'" );
		}
		###
		if ($groupid != 'none')
		{
			query_basic( "UPDATE `".DBPREFIX."script` SET
				`groupid` = '".$groupid."' WHERE `scriptid` = '".$scriptid."'" );
		}
		else
		{
			query_basic( "UPDATE `".DBPREFIX."script` SET
				`groupid` = NULL WHERE `scriptid` = '".$scriptid."'" );
		}
		###
		$_SESSION['msg1'] = 'Script Updated Successfully!';
		$_SESSION['msg2'] = 'Your changes to the script have been saved.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
		die();
		break;

	case 'scriptdelete':
		$scriptid = $_GET['scriptid'];
		###
		$error = '';
		###
		if (empty($scriptid))
		{
			$error .= 'No ScriptID specified for script deletion !';
		}
		else
		{
			if (!is_numeric($scriptid))
			{
				$error .= 'Invalid ScriptID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
			{
				$error .= 'Invalid ScriptID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: script.php' );
			die();
		}
		###
		$rows = query_fetch_assoc( "SELECT `name`, `panelstatus` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
		###
		if ($rows['panelstatus'] == 'Started')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script must be stopped first!';
			$_SESSION['msg-type'] = 'error';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
		}
		query_basic( "DELETE FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
		###
		$message = 'Script Deleted: '.mysql_real_escape_string($rows['name']);
		###
		query_basic( "INSERT INTO `".DBPREFIX."log` SET `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
		###
		$_SESSION['msg1'] = 'Script Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected script has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: script.php" );
		die();
		break;

	case 'scriptvalidation':
		$scriptid = $_GET['scriptid'];
		###
		$error = '';
		###
		if (empty($scriptid))
		{
			$error .= 'No ScriptID specified for validation !';
		}
		else
		{
			if (!is_numeric($scriptid))
			{
				$error .= 'Invalid ScriptID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
			{
				$error .= 'Invalid ScriptID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: script.php' );
			die();
		}
		else
		{
			$script = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$script['boxid']."' LIMIT 1" );
			###
			$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);
			if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
			{
				$_SESSION['msg1'] = 'Connection Error!';
				$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			###
			//We check for "screen" requirement
			$output = $ssh->exec('screen -v'."\n");
			if (!preg_match("#^Screen version#", $output))
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'Screen is not installed on the script\'s box.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			###
			//We check for "wine" requirement if it is necessary
			if (preg_match("#wine#", $script['startline']))
			{
				$output = $ssh->exec('wine --version'."\n");
				if (!preg_match("#^wine#", $output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'Wine is not installed on the script\'s box.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}
			}
			###
			//We check script dir
			$output = $ssh->exec('cd '.$script['homedir']."\n"); //We retrieve the output of the 'cd' command
			if (!empty($output)) //If the output is empty, we consider that there is no errors
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'Unable to find HOMEDIR path.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			else
			{
				// Check if the script is located in the home directory
				$ssh->exec('cd '.$script['homedir'].'; ls > temp.txt'."\n"); //We list all files of the home directory into 'temp.txt'
				$output = $ssh->exec('cd '.$script['homedir'].'; grep \''.$script['filename'].'\' temp.txt'."\n"); //We check for the script
				$ssh->exec('cd '.$script['homedir'].'; rm temp.txt'."\n"); //temp.txt is now useless
				if (empty($output))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'Unable to find '.htmlspecialchars($script['filename'], ENT_QUOTES).' located in '.htmlspecialchars($script['homedir'], ENT_QUOTES);
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}
				else
				{
					//Everything is OKAY, Mark the script as validated
					###
					query_basic( "UPDATE `".DBPREFIX."script` SET `status` = 'Active' WHERE `scriptid` = '".$scriptid."'" );
					###
					//Adding event to the database
					$message = 'Script Validated : '.mysql_real_escape_string($script['name']);
					query_basic( "INSERT INTO `".DBPREFIX."log` SET `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
					###
					$_SESSION['msg1'] = 'Script Successfully Validated!';
					$_SESSION['msg2'] = 'The script is now ready for use.';
					$_SESSION['msg-type'] = 'success';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
					break;
				}
			}
		}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

	case 'scriptcatadd':
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
			$error .= 'Category Name is too short (2 Chars min.). ';
		}
		if (query_numrows( "SELECT `id` FROM `".DBPREFIX."scriptCat` WHERE `name` = '".$name."'" ) != 0)
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
			header( "Location: scriptcatadd.php" );
			die();
		}
		###
		//As the form has been validated, vars are useless
		unset($_SESSION['name']);
		unset($_SESSION['notes']);
		###
		//Adding category to the database
		query_basic( "INSERT INTO `".DBPREFIX."scriptCat` SET `name` = '".$name."', `description` = '".$description."'" );
		###
		$_SESSION['msg1'] = 'Category Added Successfully!';
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'success';
		header( "Location: scriptcatmanage.php" );
		die();
		break;

	case 'scriptcatedit':
		$catid = mysql_real_escape_string($_POST['catid']);
		$name = mysql_real_escape_string($_POST['name']);
		$description = mysql_real_escape_string($_POST['notes']);
		###
		//Check the inputs. Output an error if the validation failed
		###
		$error = '';
		###
		if (!is_numeric($catid))
		{
			$error .= 'Invalid CatID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$catid."'" ) == 0)
		{
			$error .= 'Invalid CatID. ';
		}
		###
		$nameLength = strlen($name);
		if ($nameLength < 2)
		{
			$error .= 'Category Name is too short (2 Chars min.). ';
		}
		else if (query_numrows( "SELECT `id` FROM `".DBPREFIX."scriptCat` WHERE `name` = '".$name."' && `id` != '".$catid."'" ) != 0)
		{
			$error .= 'This name is already in use ! ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: scriptcatedit.php?id=".urlencode($catid) );
			die();
		}
		###
		//We update the database
		query_basic( "UPDATE `".DBPREFIX."scriptCat` SET
			`name` = '".$name."',
			`description` = '".$description."' WHERE `id` = '".$catid."'" );
		###
		$_SESSION['msg1'] = 'Category Updated Successfully!';
		$_SESSION['msg2'] = '';
		$_SESSION['msg-type'] = 'success';
		header( "Location: scriptcatmanage.php" );
		die();
		break;

	case 'scriptcatdelete':
		$catid = $_GET['id'];
		###
		$error = '';
		###
		if (!is_numeric($catid))
		{
			$error .= 'Invalid CatID. ';
		}
		else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$catid."'" ) == 0)
		{
			$error .= 'Invalid CatID. ';
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( "Location: scriptcatmanage.php" );
			die();
		}
		###
		if (query_numrows( "SELECT `scriptid` FROM `".DBPREFIX."script` WHERE `catid` = '".$catid."'" ) != 0)
		{
			$_SESSION['msg1'] = 'Error!';
			$_SESSION['msg2'] = 'The selected category cannot be deleted as it is currently linked with a script. The script must be deleted first.';
			$_SESSION['msg-type'] = 'error';
			header( "Location: scriptcatmanage.php" );
			die();
		}
		###
		query_basic( "DELETE FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$catid."' LIMIT 1" );
		###
		$_SESSION['msg1'] = 'Category Deleted Successfully!';
		$_SESSION['msg2'] = 'The selected category has been removed.';
		$_SESSION['msg-type'] = 'success';
		header( "Location: scriptcatmanage.php" );
		die();
		break;

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

	case 'scriptstart':
		$scriptid = $_GET['scriptid'];
		###
		$error = '';
		###
		if (empty($scriptid))
		{
			$error .= 'No ScriptID specified !';
		}
		else
		{
			if (!is_numeric($scriptid))
			{
				$error .= 'Invalid ScriptID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
			{
				$error .= 'Invalid ScriptID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: script.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" );
		if (($status['status'] == 'Inactive'))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script has been disabled. ';
			$_SESSION['msg-type'] = 'error';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script is pending. ';
			$_SESSION['msg-type'] = 'error';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
		}
		else
		{
			$script = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$script['boxid']."' LIMIT 1" );
			###
			if ($script['type'] == '0') // Nohup case
			{
				$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
				$aes = new Crypt_AES();
				$aes->setKeyLength(256);
				$aes->setKey(CRYPT_KEY);
				if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
				{
					$_SESSION['msg1'] = 'Connection Error!';
					$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}

				//We try to retrieve screen name ($session)
				$output = $ssh->exec("screen -ls | grep ".preg_replace('#[^a-zA-Z0-9]#', "_", $script['name'])."\n");
				$output = trim($output);
				$session = explode("\t", $output);
				unset($output);

				//We verify that another instance of this script is not running
				if (!empty($session[0]))
				{
					$_SESSION['msg1'] = 'Error!';
					$_SESSION['msg2'] = 'This script still running: aborting.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}

				//We prepare the startline
				$startline = $script['startline'];
				###
				if (preg_match("#\{script\}#", $startline))
				{
					$startline = preg_replace("#\{script\}#", $script['filename'], $startline); //SCRIPT replacement
				}
				#-----------------+
				$cmd = "screen -AdmSL ".preg_replace('#[^a-zA-Z0-9]#', "_", $script['name'])." ".$startline;
				$ssh->exec('cd '.$script['homedir'].'; rm screenlog.0; '.$cmd."\n");
				#-----------------+
			}
			else // Screen case
			{
				if (($script['panelstatus'] == 'Started'))
				{
					$_SESSION['msg1'] = 'Validation Error!';
					$_SESSION['msg2'] = 'The script has been already started! ';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}
				###
				$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
				$aes = new Crypt_AES();
				$aes->setKeyLength(256);
				$aes->setKey(CRYPT_KEY);
				if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
				{
					$_SESSION['msg1'] = 'Connection Error!';
					$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
					$_SESSION['msg-type'] = 'error';
					header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
					die();
				}
				###
				//We prepare the startline
				$startline = $script['startline'];
				###
				if (preg_match("#\{script\}#", $startline))
				{
					$startline = preg_replace("#\{script\}#", $script['filename'], $startline); //SCRIPT replacement
				}
				#-----------------+
				$cmd = "screen -AdmSL ".$script['screen']." ".$startline;
				$ssh->exec('cd '.$script['homedir'].'; '.$cmd."\n");
				#-----------------+
				//Mark the script as started
				query_basic( "UPDATE `".DBPREFIX."script` SET `panelstatus` = 'Started' WHERE `scriptid` = '".$scriptid."'" );
			}
			###
			//Adding event to the database
			$message = 'Script Launched : '.mysql_real_escape_string($script['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `scriptid` = '".$scriptid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			###
			$_SESSION['msg1'] = 'Script Successfully Launched!';
			$_SESSION['msg2'] = 'With command : '.htmlspecialchars($cmd, ENT_QUOTES);
			$_SESSION['msg-type'] = 'info';
			if (isset($_GET['return'])) {
				header( "Location: ".$_GET['return'] );
			} else {
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			}
			die();
			break;
		}

	case 'scriptstop':
		$scriptid = $_GET['scriptid'];
		###
		$error = '';
		###
		if (empty($scriptid))
		{
			$error .= 'No ScriptID specified !';
		}
		else
		{
			if (!is_numeric($scriptid))
			{
				$error .= 'Invalid ScriptID. ';
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
			{
				$error .= 'Invalid ScriptID. ';
			}
		}
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: script.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" );
		if (($status['status'] == 'Inactive'))
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script has been disabled. ';
			$_SESSION['msg-type'] = 'error';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
		}
		else if ($status['status'] == 'Pending')
		{
			$_SESSION['msg1'] = 'Validation Error!';
			$_SESSION['msg2'] = 'The script is pending. ';
			$_SESSION['msg-type'] = 'error';
			header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			die();
		}
		else
		{
			$script = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );
			$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$script['boxid']."' LIMIT 1" );
			###
			if ($script['type'] == '0') // Nohup case
			{
				$_SESSION['msg1'] = 'Error!';
				$_SESSION['msg2'] = 'Non-interactive scripts are unstoppable!';
				$_SESSION['msg-type'] = 'error';
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			// Else : Screen case
			if (($script['panelstatus'] == 'Stopped'))
			{
				$_SESSION['msg1'] = 'Validation Error!';
				$_SESSION['msg2'] = 'The script has been already stopped! ';
				$_SESSION['msg-type'] = 'error';
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			###
			$ssh = new Net_SSH2($box['ip'].':'.$box['sshport']);
			$aes = new Crypt_AES();
			$aes->setKeyLength(256);
			$aes->setKey(CRYPT_KEY);
			if (!$ssh->login($box['login'], $aes->decrypt($box['password'])))
			{
				$_SESSION['msg1'] = 'Connection Error!';
				$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
				$_SESSION['msg-type'] = 'error';
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
				die();
			}
			###
			$output = $ssh->exec("screen -ls | grep ".$script['screen']."\n");
			$output = trim($output);
			$session = explode("\t", $output);
			#-----------------+
			$cmd = "screen -S ".$session[0]." -X quit; cd ".$script['homedir']."; rm screenlog.0";
			$ssh->exec($cmd."\n");
			#-----------------+
			//Mark the script as stopped
			query_basic( "UPDATE `".DBPREFIX."script` SET `panelstatus` = 'Stopped' WHERE `scriptid` = '".$scriptid."'" );
			###
			//Adding event to the database
			$message = 'Script Stopped : '.mysql_real_escape_string($script['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `scriptid` = '".$scriptid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			###
			$_SESSION['msg1'] = 'Script Successfully Stopped!';
			$_SESSION['msg2'] = '';
			$_SESSION['msg-type'] = 'info';
			if (isset($_GET['return'])) {
				header( "Location: ".$_GET['return'] );
			} else {
				header( "Location: scriptsummary.php?id=".urlencode($scriptid) );
			}
			die();
			break;
		}

	default:
		exit('<h1><b>Error</b></h1>');
}

exit('<h1><b>403 Forbidden</b></h1>'); //If the task is incorrect or unspecified, we drop the user.