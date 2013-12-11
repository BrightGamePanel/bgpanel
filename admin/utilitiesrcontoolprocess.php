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


//---------------------------------------------------------+

if (isset($_GET['serverid']) && is_numeric($_GET['serverid']))
{
	if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$_GET['serverid']."'" ) == 0)
	{
		exit('Error: Server is invalid.');
	}
	else
	{
		$serverid = $_GET['serverid'];
		$step = 'rcon';
	}
}
else
{
	die();
}

//---------------------------------------------------------+


switch ($step)
{

//------------------------------------------------------------------------------------------------------------+



	case 'rcon':
		require("../includes/func.ssh2.inc.php");
		require_once("../libs/phpseclib/Crypt/AES.php");
		require_once("../libs/phpseclib/ANSI.php");

		$error = '';

		if (empty($serverid))
		{
			$error .= T_('No ServerID specified for server validation !');
		}
		else
		{
			if (!is_numeric($serverid))
			{
				$error .= T_('Invalid ServerID. ');
			}
			else if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
			{
				$error .= T_('Invalid ServerID. ');
			}
		}

		if (!empty($error))
		{
			die();
		}

		$panelstatus = query_fetch_assoc( "SELECT `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		if ($panelstatus['panelstatus'] != 'Started')
		{
			die();
		}

		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		if ($status['status'] != 'Active')
		{
			die();
		}

		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );

		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);

		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			die();
		}

		$ansi = new File_ANSI();

		// We retrieve screen name ($session)
		$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$server['screen']."$'"."\n" );
		$session = trim($session);

		// We retrieve screen contents
		$ssh->write("screen -R ".$session."\n");
		$ssh->setTimeout(1.1);

		@$ansi->appendString($ssh->read());
		$screenContents = htmlspecialchars_decode(strip_tags($ansi->getScreen()));

		$ssh->disconnect();
		unset($session);


?>

<?php

		// Each lines are a value of rowsTable
		$rowsTable = explode("\n", $screenContents);

		// Output
		foreach ($rowsTable as $key => $value)
		{
			echo htmlentities($value, ENT_QUOTES);
		}

?>

<?php
		die();
		break;



//------------------------------------------------------------------------------------------------------------+

}



?>