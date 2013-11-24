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



$page = 'scriptconsole';
$tab = 4;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error:ScriptID error.');
}

$scriptid = $_GET['id'];
$return = 'scriptconsole.php?id='.urlencode($scriptid);


require("configuration.php");
require("include.php");
require("./includes/func.ssh2.inc.php");
require_once("./libs/phpseclib/Crypt/AES.php");
require_once("./libs/phpseclib/ANSI.php");


$title = T_('Script Console');

$scriptid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
{
	exit('Error: ScriptID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );


if ($rows['status'] != 'Active')
{
	exit('Validation Error! The script is disabled!');
}
else
{
	$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
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

	$screen = $rows['screen'];
	if (empty($screen)) {
		$screen = preg_replace('#[^a-zA-Z0-9]#', "_", $rows['name']);
	}

	// We retrieve screen name ($session)
	$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$screen."$'"."\n" );
	$session = trim($session);

	if ($rows['type'] == '1')
	{
		if (!empty($_GET['cmd']) && !empty($session))
		{
			$cmdRcon = $_GET['cmd'];

			// We prepare and we send the command into the screen
			$cmd = "screen -S ".$session." -p 0 -X stuff \"".$cmdRcon."\"`echo -ne '\015'`";
			$ssh->exec($cmd."\n");
			unset($cmd);

			// Adding event to the database
			$message = 'Script command ('.mysql_real_escape_string($cmdRcon).') sent to : '.mysql_real_escape_string($rows['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `scriptid` = '".$scriptid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['clientusername'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			unset($cmdRcon);

			header( 'Location: scriptconsole.php?id='.urlencode($scriptid) );
			die();
		}
	}

	// We retrieve screen contents
	if (!empty($session)) {
		$ssh->write("screen -R ".$session."\n");
		$ssh->setTimeout(1);

		@$ansi->appendString($ssh->read());
		$screenContents = htmlspecialchars_decode(strip_tags($ansi->getScreen()));
	}
	else {
		$screenContents = "The Script is not running...\n";
	}

	$ssh->disconnect();
}


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="scriptsummary.php?id=<?php echo $scriptid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li class="active"><a href="scriptconsole.php?id=<?php echo $scriptid; ?>"><?php echo T_('Console'); ?></a></li>
			</ul>
			<script>
			$(document).ready(function() {
				prettyPrint();
			});
			</script>
			<div class="page-header">
				<h1><small><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></small></h1>
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
<?php

if ($rows['type'] == '1' && !empty($session))
{
?>
					<form class="form-inline" method="get" action="scriptconsole.php">
						<input type="hidden" name="id" value="<?php echo $rows['scriptid']; ?>" />
						<div class="input-prepend input-append">
							<span class="add-on"><?php echo T_('Command'); ?>:</span>
							<input type="text" name="cmd" class="input-xlarge" placeholder="<?php echo T_('Your Command'); ?>">
							<button type="submit" class="btn">
								<?php echo T_('Send'); ?>
							</button>
							<button class="btn" onclick="window.location.reload();">
								<?php echo T_('Refresh'); ?>
							</button>
						</div>
					</form>
<?php
}
else
{
?>
					<button class="btn" onclick="window.location.reload();">
						<?php echo T_('Refresh'); ?>
					</button>
<?php
}

?>
				</div>
<?php


include("./bootstrap/footer.php");
?>