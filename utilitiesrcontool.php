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



$page = 'utilitiesrcontool';
$tab = 4;
$return = 'utilitiesrcontool.php';


require("configuration.php");
require("include.php");


$title = T_('RCON Tool');


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
	$step = 'selectserver';
}

//---------------------------------------------------------+


switch ($step)
{

//------------------------------------------------------------------------------------------------------------+



	case 'selectserver':


		include("./bootstrap/header.php");


		/**
		 * Notifications
		 */
		include("./bootstrap/notifications.php");


		$servers = getClientServers( $_SESSION['clientid'] )


?>
			<div class="well">
<?php
		if (!empty($servers))
		{
?>
				<form method="get" action="utilitiesrcontool.php">
					<label><?php echo T_('Available servers for RCON'); ?>&nbsp;:</label>
						<select name="serverid">
<?php
			foreach($servers as $key => $value)
			{
				if ( ($value['status'] == 'Active') && ($value['panelstatus'] == 'Started') )
				{
?>
							<option value="<?php echo $value['serverid']; ?>">#<?php echo $value['serverid']; ?> - <?php echo htmlspecialchars($value['name'], ENT_QUOTES); ?></option>
<?php
				}
			}
			unset($servers);
?>
						</select>
						<div style="text-align: center; margin-top: 19px;">
							<button type="submit" class="btn btn-primary btn-large"><?php echo T_('RCON Console'); ?></button>
						</div>
				</form>
<?php
		}
?>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="index.php"><?php echo T_('Back to Home'); ?></a>
						</li>
					</ul>
				</div>
			</div>
<?php

		break;



//------------------------------------------------------------------------------------------------------------+



	case 'rcon':
		require("./includes/func.ssh2.inc.php");
		require_once("./libs/phpseclib/Crypt/AES.php");
		require_once("./libs/phpseclib/ANSI.php");
		###
		$error = '';
		###
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
		###
		if (!empty($error))
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = $error;
			$_SESSION['msg-type'] = 'error';
			unset($error);
			header( 'Location: index.php' );
			die();
		}
		###
		$panelstatus = query_fetch_assoc( "SELECT `panelstatus` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		if ($panelstatus['panelstatus'] != 'Started')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is not running!');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$status = query_fetch_assoc( "SELECT `status` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		if ($status['status'] != 'Active')
		{
			$_SESSION['msg1'] = T_('Validation Error!');
			$_SESSION['msg2'] = T_('The server is disabled or pending!');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$server = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
		$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$server['boxid']."' LIMIT 1" );
		###
		// Rights
		$checkGroup = checkClientGroup($server['groupid'], $_SESSION['clientid']);
		if ($checkGroup == FALSE)
		{
			$_SESSION['msg1'] = T_('Error!');
			$_SESSION['msg2'] = T_('This is not your server!');
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}
		###
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		###
		// Get SSH2 Object OR ERROR String
		$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
		if (!is_object($ssh))
		{
			$_SESSION['msg1'] = T_('Connection Error!');
			$_SESSION['msg2'] = $ssh;
			$_SESSION['msg-type'] = 'error';
			header( 'Location: index.php' );
			die();
		}

		$ansi = new File_ANSI();

		// We retrieve screen name ($session)
		$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$server['screen']."$'"."\n" );
		$session = trim($session);

		if (!empty($_GET['cmd']))
		{
			$cmdRcon = $_GET['cmd'];

			// We prepare and we send the command into the screen
			$cmd = "screen -S ".$session." -p 0 -X stuff \"".$cmdRcon."\"`echo -ne '\015'`";
			$ssh->exec($cmd."\n");
			unset($cmd);

			// Adding event to the database
			$message = 'RCON command ('.mysql_real_escape_string($cmdRcon).') sent to : '.mysql_real_escape_string($server['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `serverid` = '".$serverid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['clientusername'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			unset($cmdRcon);

			header( 'Location: utilitiesrcontool.php?serverid='.urlencode($serverid) );
			die();
		}

		// We retrieve screen contents
		$ssh->write("screen -R ".$session."\n");
		$ssh->setTimeout(1);

		@$ansi->appendString($ssh->read());
		$screenContents = htmlspecialchars_decode(strip_tags($ansi->getScreen()));

		$ssh->disconnect();
		unset($session);


		include("./bootstrap/header.php");


		/**
		 * Notifications
		 */
		include("./bootstrap/notifications.php");


?>
			<script>
			$(document).ready(function() {
				prettyPrint();
			});
			</script>
			<div class="page-header">
				<h1><small><?php echo htmlspecialchars($server['name'], ENT_QUOTES); ?></small></h1>
			</div>
<pre class="prettyprint">
<?php

		// Each lines are a value of rowsTable
		$rowsTable = explode("\n", $screenContents);

		// Output
		foreach ($rowsTable as $key => $value)
		{
			echo htmlentities($value, ENT_QUOTES);
		}

?>

</pre>
				<div style="text-align: center;">
					<form class="form-inline" method="get" action="utilitiesrcontool.php">
						<input type="hidden" name="serverid" value="<?php echo $serverid; ?>" />
						<div class="input-prepend input-append">
							<span class="add-on"><?php echo T_('RCON Command'); ?>:</span>
							<input type="text" name="cmd" class="input-xlarge" placeholder="<?php echo T_('Your RCON Command'); ?>">
							<button type="submit" class="btn">
								<?php echo T_('Send'); ?>
							</button>
							<button class="btn" onclick="window.location.reload();">
								<?php echo T_('Refresh'); ?>
							</button>
							<button class="btn" onclick="dlScrLog();return false;">
								<?php echo T_('Download Screenlog'); ?>
							</button>
						</div>
					</form>
				</div>
				<hr/>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="utilitiesrcontool.php"><?php echo T_('Back to RCON Tool Servers List'); ?></a>
							<a href="server.php?id=<?php echo $serverid; ?>"><?php echo T_('Go to Server Summary'); ?></a>
						</li>
					</ul>
				</div>
				<script>
				function dlScrLog()
				{
					if (confirm("<?php echo T_('Download SCREENLOG ?'); ?>"))
					{
						window.location.href='serverprocess.php?task=getserverlog&serverid=<?php echo $serverid; ?>';
					}
				}
				</script>
<?php
		break;



//------------------------------------------------------------------------------------------------------------+

}


include("./bootstrap/footer.php");
?>