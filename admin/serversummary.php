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



$page = 'serversummary';
$tab = 2;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: ServerID error.');
}

$serverid = $_GET['id'];
$return = 'serversummary.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");
require_once("../includes/func.ssh2.inc.php");
require_once("../libs/phpseclib/Crypt/AES.php");
require_once("../libs/gameinstaller/gameinstaller.php");


$title = T_('Server Summary');

$serverid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$box = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rows['ipid']."' LIMIT 1" );
$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1");
$game = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rows['groupid']."' LIMIT 1" );
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` WHERE `serverid` = '".$serverid."' ORDER BY `logid` DESC LIMIT 5" );

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
				<li class="active"><a href="serversummary.php?id=<?php echo $serverid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="serverprofile.php?id=<?php echo $serverid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="servermanage.php?id=<?php echo $serverid; ?>"><?php echo T_('Manage'); ?></a></li>
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

?>

			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Server Information'); ?></span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td><?php echo T_('Name'); ?></td>
								<td><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Status'); ?></td>
								<td><?php echo formatStatus($rows['status']); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Owner Group'); ?></td>
								<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Game'); ?></td>
								<td><?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('IP Address'); ?></td>
								<td><?php echo $serverIp['ip']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Port'); ?></td>
								<td><?php echo $rows['port']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Query Port'); ?></td>
								<td><?php echo $rows['queryport']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Slots'); ?></td>
								<td><?php echo $rows['slots']; ?></td>
							</tr>
						</table>
<?php

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
								<a class="btn btn-primary" href="#" onclick="doGameServerAction('<?php echo $serverid; ?>', 'makeGameServer', '<?php echo T_('install game server files'); ?>', '<?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?>')"><?php echo T_('Install'); ?></a>
							</p>
						</div>
<?php
}

?>
						<div style="text-align: center;">
<?php

if ($rows['status'] == 'Active')
{
?>
							<a href="servermanage.php?id=<?php echo $serverid; ?>" class="btn btn-primary"><i class="icon-cog icon-white"></i>&nbsp;<?php echo T_('Manage Server'); ?></a>
<?php
}

?>
							<div class="btn-group">
								<button onclick="deleteServer();return false;" class="btn btn-danger"><i class="icon-trash icon-white"></i>&nbsp;<?php echo T_('Delete Server'); ?></button>
								<button class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
<?php

if ( $rows['status'] != 'Pending' )
{
?>
									<li><a href="#" onclick="deleteServerWithFiles();return false;"><?php echo T_('Delete Server &amp; Files'); ?></a></li>
<?php
}

?>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Files Information'); ?></span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td><?php echo T_('Disk Usage'); ?></td>
								<td colspan="2"><?php if ($gameCacheInfo != FALSE) { echo htmlspecialchars($gameCacheInfo['size'], ENT_QUOTES); } else { echo "None"; } ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Last Modification'); ?></td>
								<td colspan="2"><?php if ($gameCacheInfo != FALSE) { echo @date('l | F j, Y | H:i', $gameCacheInfo['mtime']); } else { echo 'Never'; } ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Status'); ?></td>
								<td colspan="2"><?php

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
							</tr>
							<tr>
								<td><?php echo T_('Game Repository'); ?></td>
								<td colspan="2"><?php

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

if ($rows['status'] == 'Active')
{
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
}

?>
						</p>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Server Monitoring'); ?></span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td><?php echo T_('Query Type'); ?></td>
								<td><?php echo $type['querytype']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Panel Status'); ?></td>
								<td><?php echo formatStatus($rows['panelstatus']); ?></td>
							</tr>
<?php

if (($rows['status'] == 'Active') && ($rows['panelstatus'] == 'Started'))
{
	//---------------------------------------------------------+
	//Querying the server
	include_once("../libs/lgsl/lgsl_class.php");

	$server = lgsl_query_live($type['querytype'], $serverIp['ip'], NULL, $rows['queryport'], NULL, 's');
	//
	//---------------------------------------------------------+
}

?>
							<tr>
								<td><?php echo T_('Net Status'); ?></td>
								<td><?php

if (@$server['b']['status'] == '1')
{
	echo formatStatus('Online');
}
else
{
	echo formatStatus('Offline');
}

?></td>
							</tr>
							<tr>
								<td><?php echo T_('Map'); ?></td>
								<td><?php echo @$server['s']['map']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Players'); ?></td>
								<td><?php echo @$server['s']['players']; ?> / <?php echo @$server['s']['playersmax']; ?></td>
							</tr>
<?php

unset($server);

?>
						</table>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Server Configuration'); ?></span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td><?php echo T_('Priority'); ?></td>
								<td colspan="2"><?php echo $rows['priority']; ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Start Command'); ?></td>
								<td colspan="2"><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Directory'); ?></td>
								<td colspan="2"><?php echo htmlspecialchars(dirname($rows['path']), ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Executable'); ?></td>
								<td colspan="2"><?php echo htmlspecialchars(basename($rows['path']), ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><?php echo T_('Screen Name'); ?></td>
								<td colspan="2"><?php echo $rows['screen']; ?></td>
							</tr>
<?php

$n = 1;
while ($n < 10)
{
	if (!empty($rows['cfg'.$n.'name']) || !empty($rows['cfg'.$n]))
	{
?>
							<tr>
								<td><?php echo htmlspecialchars($rows['cfg'.$n.'name'], ENT_QUOTES); ?></td>
								<td><?php echo htmlspecialchars($rows['cfg'.$n.''], ENT_QUOTES); ?></td>
								<td>{cfg<?php echo $n; ?>}</td>
							</tr>
<?php
	}
	++$n;
}
unset($n);

?>
						</table>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info"><?php echo T_('Last 5 Actions'); ?></span>
						</div>
						<table class="table table-bordered">
<?php

if (mysql_num_rows($logs) == 0)
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Logs Found'); ?></span></div>
								</td>
							</tr>
<?php
}

while ($rowsLogs = mysql_fetch_assoc($logs))
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><?php echo formatDate($rowsLogs['timestamp']); ?> - <?php echo htmlspecialchars($rowsLogs['message'], ENT_QUOTES); ?></div>
								</td>
							</tr>
<?php
}
unset($logs);

?>
						</table>
					</div>
				</div>
			</div>
			<script language="javascript" type="text/javascript">
			function deleteServer()
			{
				if (confirm("<?php echo T_('Are you sure you want to unlink game server:'); ?> <?php echo htmlspecialchars(addslashes($rows['name']), ENT_QUOTES); ?> ?"))
				{
					window.location.href='serverprocess.php?task=serverdelete&serverid=<?php echo $rows['serverid']; ?>';
				}
			}
			<!-- -->
			function deleteServerWithFiles()
			{
				if (confirm("<?php echo T_('Are you sure you want to fully delete game server with its files:'); ?> <?php echo htmlspecialchars(addslashes($rows['name']), ENT_QUOTES); ?> ?"))
				{
					window.location.href='serverprocess.php?task=serverdelete&serverdeletefiles=true&serverid=<?php echo $rows['serverid']; ?>';
				}
			}
			<!-- -->
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