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



$title = 'Server Summary';
$page = 'serversummary';
$tab = 2;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$serverid = $_GET['id'];
}
else
{
	exit('Error: ServerID error.');
}
###
$return = 'serversummary.php?id='.urlencode($serverid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."'" ) == 0)
{
	exit('Error: ServerID is invalid.');
}

$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."server` WHERE `serverid` = '".$serverid."' LIMIT 1" );
$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rows['boxid']."' LIMIT 1" );
$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1");
$game = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."game` WHERE `gameid` = '".$rows['gameid']."' LIMIT 1" );
$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rows['groupid']."' LIMIT 1" );
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` WHERE `serverid` = '".$serverid."' ORDER BY `logid` DESC LIMIT 5" );


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
				<li class="active"><a href="serversummary.php?id=<?php echo $serverid; ?>">Summary</a></li>
				<li><a href="serverprofile.php?id=<?php echo $serverid; ?>">Profile</a></li>
				<li><a href="servermanage.php?id=<?php echo $serverid; ?>">Manage</a></li>
<?php

if ($type['querytype'] != 'none')
{
	echo "\t\t\t\t<li><a href=\"serverlgsl.php?id=".$serverid."\">LGSL</a></li>";
}

?>

<?php

if ($rows['panelstatus'] == 'Started')
{
	echo "\t\t\t\t<li><a href=\"utilitiesrcontool.php?serverid=".$serverid."\">RCON Tool</a></li>";
}

?>

				<li><a href="serverlog.php?id=<?php echo $serverid; ?>">Activity Logs</a></li>
			</ul>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Server Information</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Name</td>
								<td><?php echo htmlspecialchars($rows['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Status</td>
								<td><?php echo formatStatus($rows['status']); ?></td>
							</tr>
							<tr>
								<td>Owner Group</td>
								<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Game</td>
								<td><?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>IP Address</td>
								<td><?php echo $serverIp['ip']; ?></td>
							</tr>
							<tr>
								<td>Port</td>
								<td><?php echo $rows['port']; ?></td>
							</tr>
							<tr>
								<td>Query Port</td>
								<td><?php echo $rows['queryport']; ?></td>
							</tr>
							<tr>
								<td>Slots</td>
								<td><?php echo $rows['slots']; ?></td>
							</tr>
						</table>
						<div style="text-align: center;">
							<button onclick="deleteServer();return false;" class="btn btn-danger">Delete Server</button>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Server Configuration</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Priority</td>
								<td colspan="2"><?php echo $rows['priority']; ?></td>
							</tr>
							<tr>
								<td>Start Command</td>
								<td colspan="2"><?php echo htmlspecialchars($rows['startline'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Home Directory</td>
								<td colspan="2"><?php echo htmlspecialchars($rows['homedir'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Screen Name</td>
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
							<span class="label label-info">Server Monitoring</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Query Type</td>
								<td><?php echo $type['querytype']; ?></td>
							</tr>
							<tr>
								<td>Panel Status</td>
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
								<td>Net Status</td>
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
								<td>Map</td>
								<td><?php echo @$server['s']['map']; ?></td>
							</tr>
							<tr>
								<td>Players</td>
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
							<span class="label label-info">Tiny Server Control Panel</span>
						</div>
<?php

if ($rows['status'] == 'Pending')
{
?>
						<div class="alert alert-info">
							<h4 class="alert-heading">Server not validated !</h4>
							<p>
								You must validate the server in order to use it.
							</p>
							<p>
								<a class="btn btn-primary" href="serverprocess.php?task=servervalidation&serverid=<?php echo $serverid; ?>">Validate</a>
							</p>
						</div>
<?php
}
else if ($rows['status'] == 'Inactive')
{
?>
						<div class="alert alert-block" style="text-align: center;">
							<h4 class="alert-heading">The server has been disabled !</h4>
						</div>
<?php
}
else if ($rows['panelstatus'] == 'Stopped') //The server has been validated and is marked as offline, the only available action is to start it
{
?>
						<div style="text-align: center;">
							<a class="btn btn-primary" href="serverprocess.php?task=serverstart&serverid=<?php echo $serverid; ?>">Start</a>
						</div>
<?php
}
else if ($rows['panelstatus'] == 'Started') //The server has been validated and is marked as online, the available actions are to restart or to stop it
{
?>
						<div style="text-align: center;">
							<a class="btn btn-warning" href="serverprocess.php?task=serverstop&serverid=<?php echo $serverid; ?>">Stop</a>
							<a class="btn btn-primary" href="serverprocess.php?task=serverreboot&serverid=<?php echo $serverid; ?>">Restart</a>
						</div>
<?php
}

?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="span6 offset3">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Last 5 Actions</span>
						</div>
						<table class="table table-bordered">
<?php

if (mysql_num_rows($logs) == 0)
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><span class="label label-warning">No Logs Found</span></div>
								</td>
							</tr>
<?php
}

while ($rowsLogs = mysql_fetch_assoc($logs))
{
?>
							<tr>
								<td>
									<div style="text-align: center;"><?php echo formatDate($rowsLogs['timestamp']); ?> - <?php echo $rowsLogs['message']; ?></div>
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
				if (confirm("Are you sure you want to delete server: <?php echo htmlspecialchars(addslashes($rows['name']), ENT_QUOTES); ?> ?"))
				{
					window.location.href='serverprocess.php?task=serverdelete&serverid=<?php echo $rows['serverid']; ?>';
				}
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>