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



$page = 'servermanage';
$tab = 2;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: ServerID error.');
}

$serverid = $_GET['id'];
$return = 'servermanage.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");
require_once("../includes/func.ssh2.inc.php");
require_once("../libs/phpseclib/Crypt/AES.php");
require_once("../libs/gameinstaller/gameinstaller.php");


$title = T_('Server Control Panel');

$serverid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1");
$game = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
$box = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rows['ipid']."' LIMIT 1" );

$aes = new Crypt_AES();
$aes->setKeyLength(256);
$aes->setKey(CRYPT_KEY);

// Get SSH2 Object OR ERROR String
$ssh = newNetSSH2($box['ip'], $box['sshport'], $box['login'], $aes->decrypt($box['password']));
if (!is_object($ssh))
{
	$_SESSION['msg1'] = T_('Connection Error!');
	$_SESSION['msg2'] = $ssh;
	$_SESSION['msg-type'] = 'error';
}

$gameInstaller = new GameInstaller( $ssh );

$gameCacheInfo =	$gameInstaller->getCacheInfo( dirname($rows['path']) );
$boxGameCacheInfo =	$gameInstaller->getCacheInfo( $game['cachedir'] );
$gameExists =		$gameInstaller->gameExists( $game['game'] );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="serversummary.php?id=<?php echo $serverid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="serverprofile.php?id=<?php echo $serverid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li class="active"><a href="servermanage.php?id=<?php echo $serverid; ?>"><?php echo T_('Manage'); ?></a></li>
<?php

if ($type['querytype'] != 'none')
{
	echo "\t\t\t\t<li><a href=\"serverlgsl.php?id=".$serverid."\">LGSL</a></li>";
}

?>

<?php

if ($rows['panelstatus'] == 'Started')
{
	echo "\t\t\t\t<li><a href=\"utilitiesrcontool.php?serverid=".$serverid."\">".T_('RCON Tool')."</a></li>";
}

?>

				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
				<li><a href="serverlog.php?id=<?php echo $serverid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
<?php

// Game Installer Notification
if ( $gameExists != FALSE ) {
	if ( $gameCacheInfo != FALSE ) {
		if ( ($gameCacheInfo['status'] != 'Ready') && ($gameCacheInfo['status'] != 'Aborted') ) {
			// Operation in progress
?>
			<div class="alert alert-info">
				<h4 class="alert-heading"><?php echo T_('Operation In Progress On This Game Server'); ?></h4>
				<br />
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"><?php echo htmlspecialchars($gameCacheInfo['status'], ENT_QUOTES); ?></div>
				</div>
				<p class="text-center">
					<a class="btn btn-warning" href="#" onclick="doGameServerAction('<?php echo $serverid; ?>', 'abortOperation', '<?php echo T_('abort current operation for game server'); ?>', '<?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?>')">
						<i class="icon-stop icon-white"></i>&nbsp;<?php echo T_('Abort Operation'); ?>
					</a>
				</p>
			</div>
<?php
		}
	}
}

if ($rows['status'] == 'Pending')
{
?>
			<div class="alert alert-info">
				<h4 class="alert-heading"><?php echo T_('Server not validated !'); ?></h4>
				<p>
					<?php echo T_('You must validate the server in order to use it.'); ?>
				</p>
				<p>
					<a class="btn btn-primary" href="serverprocess.php?task=servervalidation&serverid=<?php echo $serverid; ?>"><?php echo T_('Validate'); ?></a>
					<a class="btn btn-primary" href="#" onclick="doGameServerAction('<?php echo $serverid; ?>', 'makeGameServer', 'install game server files', '<?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?>')"><?php echo T_('Install'); ?></a>
				</p>
			</div>
<?php
}
else if ($rows['status'] == 'Inactive')
{
?>
			<div class="alert alert-block" style="text-align: center;">
				<h4 class="alert-heading"><?php echo T_('The server has been disabled'); ?>&nbsp;!</h4>
			</div>
<?php
}
else if ($rows['status'] == 'Active')
{

	//---------------------------------------------------------+
	//Querying the server
	include_once("../libs/lgsl/lgsl_class.php");

	$server = lgsl_query_live($type['querytype'], $ip['ip'], NULL, $rows['queryport'], NULL, 's');

?>
			<div class="well">
				<h3>Server Commander</h3>
				<table class="table">
					<tr>
						<td><?php echo T_('Path'); ?></td>
						<td><?php echo T_('Screen Name'); ?></td>
						<td><?php echo T_('Box'); ?></td>
						<td><?php echo T_('IP:Port'); ?></td>
						<td><?php echo T_('Panel Status'); ?></td>
						<td><?php echo T_('Net Status'); ?></td>
					</tr>
					<tr>
						<td><?php echo htmlspecialchars($rows['path'], ENT_QUOTES); ?></td>
						<td><?php echo $rows['screen']; ?></td>
						<td><?php echo htmlspecialchars($box['name'], ENT_QUOTES); ?></td>
						<td><?php echo $ip['ip'].':'.$rows['port']; ?></td>
						<td><?php echo formatStatus($rows['panelstatus']); ?></td>
						<td><?php

	if (@$server['b']['status'] == '1')
	{
		echo formatStatus('Online');
	}
	else
	{
		echo formatStatus('Offline');
	}
	unset($server);

?></td>
					</tr>
				</table>
				<div style="text-align: center;">
<?php

	if ($rows['panelstatus'] == 'Stopped') //The server has been validated and is marked as offline, the only available action is to start it
	{
?>
					<a href="serverprocess.php?task=serverstart&serverid=<?php echo $serverid; ?>" class="btn btn-primary"><i class="icon-play icon-white"></i>&nbsp;<?php echo T_('Start'); ?></a>
<?php
	}
	else if ($rows['panelstatus'] == 'Started') //The server has been validated and is marked as online, the available actions are to restart or to stop it
	{
?>
					<a href="serverprocess.php?task=serverstop&serverid=<?php echo $serverid; ?>" class="btn btn-warning"><i class="icon-stop icon-white"></i>&nbsp;<?php echo T_('Stop'); ?></a>
					<a href="serverprocess.php?task=serverreboot&serverid=<?php echo $serverid; ?>" class="btn btn-primary"><i class="icon-repeat icon-white"></i>&nbsp;<?php echo T_('Restart'); ?></a>
<?php
	}

?>
					<a href="#" class="btn btn-primary" onclick="dlScrLog();return false;"><i class="icon-download-alt icon-white"></i>&nbsp;<?php echo T_('Download Screenlog'); ?></a>
				</div>
				<hr>
				<h3>Install Wizard</h3>
				<table class="table">
					<tr>
						<td><?php echo T_('Disk Usage'); ?></td>
						<td><?php echo T_('Last Modification'); ?></td>
						<td><?php echo T_('Status'); ?></td>
						<td><?php echo T_('Game Repository'); ?></td>
					</tr>
					<tr>
						<td><?php if ($gameCacheInfo != FALSE) { echo htmlspecialchars($gameCacheInfo['size'], ENT_QUOTES); } else { echo "None"; } ?></td>
						<td><?php if ($gameCacheInfo != FALSE) { echo @date('l | F j, Y | H:i', $gameCacheInfo['mtime']); } else { echo 'Never'; } ?></td>
						<td><?php

	if ($gameExists == FALSE) {
		echo "<span class=\"label\">".T_('Game Not Supported')."</span>";
	}
	else if ($gameCacheInfo == FALSE) {
		echo "<span class=\"label label-warning\">".T_('No Data')."</span>&nbsp;<img src=\"../bootstrap/img/data2.png\">";
	}
	else if ($gameCacheInfo['status'] == 'Ready') {
		echo "<span class=\"label label-success\">Ready</span>&nbsp;<img src=\"../bootstrap/img/data1.png\">";
	}
	else if ($gameCacheInfo['status'] == 'Aborted') {
		echo "<span class=\"label label-important\">Aborted</span>&nbsp;<img src=\"../bootstrap/img/data2.png\">";
	}
	else {
		echo "<span class=\"label label-info\">".htmlspecialchars($gameCacheInfo['status'], ENT_QUOTES)."</span>";
	}

?></td>
						<td><?php

	if ($gameExists == FALSE) {
		echo "<span class=\"label\">".T_('Game Not Supported')."</span>";
	}
	else if ($boxGameCacheInfo == FALSE) {
		echo "<span class=\"label label-warning\">".T_('No Cache')."</span>&nbsp;<img src=\"../bootstrap/img/data2.png\">";
	}
	else if ($boxGameCacheInfo['status'] == 'Ready') {
		echo "<span class=\"label label-success\">Ready</span>&nbsp;<img src=\"../bootstrap/img/data1.png\">";
	}
	else if ($boxGameCacheInfo['status'] == 'Aborted') {
		echo "<span class=\"label label-important\">Aborted</span>&nbsp;<img src=\"../bootstrap/img/data1.png\">";
	}
	else {
		echo "<span class=\"label label-info\">".htmlspecialchars($boxGameCacheInfo['status'], ENT_QUOTES)."</span>";
	}

?></td>
					</tr>
				</table>
				<p class="text-center">
<?php

	if ( $gameExists && ($boxGameCacheInfo['status'] == 'Ready') )
	{
		if ( ($gameCacheInfo['status'] == 'Ready') || ($gameCacheInfo['status'] == 'Aborted') ) {
		// Ready OR operation aborted (must rebuild server)
?>
					<a class="btn btn-warning" href="#" onclick="doGameServerAction('<?php echo $serverid; ?>', 'makeGameServer', '<?php echo T_('reset game server contents'); ?>', '<?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?>')">
						<i class="icon-repeat icon-white"></i>&nbsp;<?php echo T_('Reset Contents'); ?>
					</a>
<?php
		}

		if ( $gameCacheInfo['status'] == 'Ready' ) {
		// Ready
?>
					<a class="btn btn-primary" href="#" onclick="doGameServerAction('<?php echo $serverid; ?>', 'updateGameServer', '<?php echo T_('update game server contents'); ?>', '<?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?>')">
						<i class="icon-download-alt icon-white"></i>&nbsp;<?php echo T_('Update Contents'); ?>
					</a>
<?php
		}
	}

?>
				</p>
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

	//---------------------------------------------------------+

}

?>
			<script>
			function doGameServerAction(serverid, task, action, game)
			{
				if (confirm('Are you sure you want to '+action+' ('+game+') ?'))
				{
					window.location='serverprocess.php?serverid='+serverid+'&task='+task;
				}
			}
			<!-- -->
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>