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
 * @copyleft	2012
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 4
 * @link		http://sourceforge.net/projects/brightgamepanel/
 */



$title = 'Box Game Files';
$page = 'boxgamefile';
$tab = 3;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$boxid = $_GET['id'];
}
else
{
	exit('Error: BoxID error.');
}
###
$return = 'boxgamefile.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");
require_once("../libs/phpseclib/SSH2.php");
require_once("../libs/phpseclib/Crypt/AES.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$sshInfos = query_fetch_assoc( "SELECT `ip`, `sshport`, `login`, `password` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$games = mysql_query( "SELECT `gameid`, `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `status` = 'Active' ORDER BY `game`" );


###
//Check SSH2 connection
$ssh = new Net_SSH2($sshInfos['ip'].':'.$sshInfos['sshport']);
$aes = new Crypt_AES();
$aes->setKeyLength(256);
$aes->setKey(CRYPT_KEY);
if (!$ssh->login($sshInfos['login'], $aes->decrypt($sshInfos['password'])))
{
	$_SESSION['msg1'] = 'Connection Error!';
	$_SESSION['msg2'] = 'Unable to connect to box with SSH.';
	$_SESSION['msg-type'] = 'error';
	header( "Location: boxgamefile.php?id=".urlencode($boxid) );
	die();
}
else
###
//Connected with ssh, processing...
{
	if (mysql_num_rows($games) == 0)
	{
		$error = 'No Games Found in the DataBase !';
		break;
	}
	else
	{
		$n = 1;
		while ($rowsGames = mysql_fetch_assoc($games)) //We will test for each games
		{
			if (empty($rowsGames['cachedir'])) //If the cacheDir is not specified
			{
				goto end; //We skip it.
			}
			$output = $ssh->exec('cd '.$rowsGames['cachedir']."\n"); //We retrieve the output of the 'cd' command
			if (empty($output)) //If the output is empty, we consider that there is no errors, so the dir is correct
			{
				$isCached[$n] = $rowsGames['gameid'];
				++$n;
			}
			unset($output);
			end:
		}
		unset($games);
	}
}


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
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>">Summary</a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>">Profile</a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>">Servers</a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>">Charts</a></li>
				<li class="active"><a href="boxgamefile.php?id=<?php echo $boxid; ?>">Game Files</a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>">Activity Logs</a></li>
			</ul>
			<div class="alert alert-info">
			Work in Progress...
			</div>
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info">Game Files</span>
				</div>
				<table id="games" class="tablesorter">
					<thead>
						<tr>
							<th>Game</th>
							<th>Cache Directory</th>
						</tr>
					</thead>
					<tbody>
<?php

if (!isset($isCached))
{
?>
						<tr>
							<td colspan="2"><div style="text-align: center;"><span class="label label-warning">No Game Files Found</span></div></td>
						</tr>
<?php
}
else
{
	foreach ($isCached as $key => $value)
	{
		$game = query_fetch_assoc( "SELECT `game`, `cachedir` FROM `".DBPREFIX."game` WHERE `gameid` = '".$value."'" );
?>
						<tr>
							<td>
								<div style="text-align: center;"><a href="configgameedit.php?id=<?php echo $value; ?>"><?php echo htmlspecialchars($game['game']); ?></a></div>
							</td>
							<td>
								<div style="text-align: center;"><?php echo htmlspecialchars($game['cachedir']); ?></div>
							</td>
						</tr>
<?php
		unset($game);
	}
}

?>					</tbody>
				</table>
<?php

if (isset($isCached))
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#games").tablesorter({
						sortList: [[0,0]]
					});
				});
				</script>
<?php
}
unset($isCached);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>