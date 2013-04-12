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
 * @version		(Release 0) DEVELOPER BETA 6
 * @link		http://www.bgpanel.net/
 */



$page = 'scriptconsole';
$tab = 5;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$scriptid = $_GET['id'];
}
else
{
	exit('Error:ScriptID error.');
}
###
$return = 'scriptconsole.php?id='.urlencode($scriptid);


require("../configuration.php");
require("./include.php");
require("../includes/func.ssh2.inc.php");
require_once("../libs/phpseclib/Crypt/AES.php");


$title = T_('Script Console');


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
{
	exit('Error: ScriptID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="scriptsummary.php?id=<?php echo $scriptid; ?>">Summary</a></li>
				<li><a href="scriptprofile.php?id=<?php echo $scriptid; ?>">Profile</a></li>
				<li class="active"><a href="scriptconsole.php?id=<?php echo $scriptid; ?>">Console</a></li>
			</ul>
<?php


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

	if ($rows['type'] == '1')
	{
		if (!empty($_GET['cmd']))
		{
			$cmdRcon = $_GET['cmd'];

			//We retrieve the content of the screen
			$cmd = "cd ".$rows['homedir']."; cat screenlog.0";
			$outputScreenContent = $ssh->exec($cmd."\n");
			unset($cmd);

			//We retrieve screen name ($session)
			$session = $ssh->exec( "screen -ls | awk '{ print $1 }' | grep '^[0-9]*\.".$rows['screen']."$'"."\n" );
			$session = trim($session);

			//We prepare and we send the command into the screen
			$cmd = "screen -S ".$session." -p 0 -X stuff \"".$cmdRcon."\"`echo -ne '\015'`";
			$ssh->exec($cmd."\n");
			unset($cmd);

			//Adding event to the database
			$message = 'Script command ('.mysql_real_escape_string($cmdRcon).') sent to : '.mysql_real_escape_string($rows['name']);
			query_basic( "INSERT INTO `".DBPREFIX."log` SET `scriptid` = '".$scriptid."', `message` = '".$message."', `name` = '".mysql_real_escape_string($_SESSION['adminfirstname'])." ".mysql_real_escape_string($_SESSION['adminlastname'])."', `ip` = '".$_SERVER['REMOTE_ADDR']."'" );
			unset($cmdRcon);

			// Check if the output has been updated

			$cmd = "cd ".$rows['homedir']."; cat screenlog.0";
			$i = 0; //Security counter

			$updated = FALSE;

			while ($updated != TRUE)
			{
				$output = $ssh->exec($cmd."\n");
				###
				if ((md5($output) != md5($outputScreenContent)) || ($i == 20))
				{
					$outputScreenContent = $output;
					$updated = TRUE;
				}
				###
				sleep(1);
				$i++;
			}

			unset($output, $updated, $cmd);
		}
	}

	//We retrieve the content of the screen
	$cmd = "cd ".$rows['homedir']."; cat screenlog.0";
	$outputScreenContent = $ssh->exec($cmd."\n");
	$ssh->disconnect();
	unset($cmd);
}
?>
			<script type="text/javascript">
			$(document).ready(function() {
				prettyPrint();
			});
			</script>
			<div class="page-header">
				<h1><small><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></small></h1>
			</div>
<pre class="prettyprint">
<?php

//We will output the last 25 rows

$screenRows = $outputScreenContent;
unset($outputScreenContent);

//Each lines are a value of rowsTable
$rowsTable = explode("\r\n", $screenRows);

//Count number of lines of rowsTable
$n = count($rowsTable);

$x = $n - 25; //Number of lines to delete

$rowsTable = array_splice($rowsTable, $x, $n);
unset($x, $n);

//Output
foreach ($rowsTable as $key => $value)
{
	echo htmlentities($value, ENT_QUOTES)."\r\n";
}

?>
</pre>
				<div style="text-align: center;">
<?php

if ($rows['type'] == '1')
{
?>
					<form class="well form-inline" method="get" action="scriptconsole.php">
						<label><?php echo T_('Command'); ?>:</label>
						<input type="hidden" name="id" value="<?php echo $rows['scriptid']; ?>" />
						<input type="text" name="cmd" class="input-xlarge" placeholder="<?php echo T_('Your Command'); ?>">
						<button type="submit" class="btn"><?php echo T_('Send'); ?></button>
					</form>
<?php
}

?>
					<button class="btn btn-large" onclick="window.location.reload();"><?php echo T_('Refresh'); ?></button>
				</div>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="script.php"><?php echo T_('Back to Scripts'); ?></a>
						</li>
					</ul>
				</div>
<?php


include("./bootstrap/footer.php");
?>