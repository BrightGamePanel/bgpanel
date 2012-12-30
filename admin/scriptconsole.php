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



$title = 'Script Console';
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


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."'" ) == 0)
{
	exit('Error: ScriptID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."script` WHERE `scriptid` = '".$scriptid."' LIMIT 1" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
if (isset($_SESSION['msg1']) && isset($_SESSION['msg2']) && isset($_SESSION['msg-type']))
{
?>
			<div class="alert alert-<?php
	switch ($_SESSION['msg-type'])
	{
		case 'block':
			echo 'block';
			break;

		case 'error':
			echo 'error';
			break;

		case 'success':
			echo 'success';
			break;

		case 'info':
			echo 'info';
			break;
	}
?>">
				<a class="close" data-dismiss="alert">&times;</a>
				<h4 class="alert-heading"><?php echo $_SESSION['msg1']; ?></h4>
				<?php echo $_SESSION['msg2']; ?>
			</div>
<?php
	unset($_SESSION['msg1']);
	unset($_SESSION['msg2']);
	unset($_SESSION['msg-type']);
}
/**
 *
 */


?>
			<ul class="nav nav-tabs">
				<li><a href="scriptsummary.php?id=<?php echo $scriptid; ?>">Summary</a></li>
				<li><a href="scriptprofile.php?id=<?php echo $scriptid; ?>">Profile</a></li>
				<li class="active"><a href="scriptconsole.php?id=<?php echo $scriptid; ?>">Console</a></li>
			</ul>
<?php


require_once("../libs/phpseclib/SSH2.php");
require_once("../libs/phpseclib/Crypt/AES.php");


if ($rows['status'] != 'Active')
{
	exit('Validation Error! The script is disabled!');
}
else
{
	$box = query_fetch_assoc( "SELECT `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
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
			$output = $ssh->exec("screen -ls | grep ".$rows['screen']."\n");
			$output = trim($output);
			$session = explode("\t", $output);
			unset($output);

			//We prepare and we send the command into the screen
			$cmd = "screen -S ".$session[0]." -p 0 -X stuff \"".$cmdRcon."\"`echo -ne '\015'`";
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
<?php

if ($rows['type'] == '1')
{
?>
				<div class="modal hide fade" id="console">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>BrightGamePanel Script Console</h3>
					</div>
					<div class="modal-body">
						<p>
							<div class="page-header">
								<h1>
									<small>
									Target: <?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?><br />
									Box: <?php echo $box['ip'].':'.$box['sshport']."\r\n"; ?>
									</small>
								</h1>
							</div><br />
							<form class="well form-inline" method="get" action="scriptconsole.php">
								<label>Command:</label>
								<input type="hidden" name="id" value="<?php echo $rows['scriptid']; ?>" />
								<input type="text" name="cmd" class="input-xlarge" placeholder="Your Command">
								<button type="submit" class="btn">Send</button>
							</form>
						</p>
					</div>
					<div class="modal-footer">
						<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-primary">Close</a>
					</div>
				</div>
				<div style="text-align: center; margin-bottom: 5px;">
					<a class="btn btn-primary btn-large" data-toggle="modal" href="#console">Send Command</a>
				</div>
<?php
}

?>
				<div style="text-align: center;">
					<button class="btn btn-large" onclick="window.location.reload();">Refresh</button>
				</div>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="script.php">Back to Scripts</a>
						</li>
					</ul>
				</div>
<?php


include("./bootstrap/footer.php");
?>