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



$title = 'Servers';
$page = 'server';
$tab = 2;
$return = 'server.php';


require("../configuration.php");
require("./include.php");


$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` ORDER BY `serverid`" );


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
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info"><?php echo mysql_num_rows($servers); ?> Record(s) Found</span> (<a href="serveradd.php">Add New Server</a>)
				</div>
				<table id="serverstable" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Owner Group</th>
							<th>Panel Status</th>
							<th>Net Status</th>
							<th>Game</th>
							<th>IP</th>
							<th>Port</th>
							<th>QPort</th>
							<th>Map</th>
							<th>Slots</th>
							<th>Status</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($servers) == 0)
{
?>
						<tr>
							<td colspan="14"><div style="text-align: center;"><span class="label label-warning">No Servers Found</span><br />No servers found. <a href="serveradd.php">Click here</a> to add a new server.</div></td>
						</tr>
<?php
}

//LGSL vars
$p = 0; //Players
$mp = 0; //Max Players

$n = 0;

while ($rowsServers = mysql_fetch_assoc($servers))
{
	$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsServers['boxid']."' LIMIT 1" );
	$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1");
	$game = query_fetch_assoc( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1" );
	$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rowsServers['groupid']."' LIMIT 1" );

	if ($rowsServers['status'] == 'Active' && $rowsServers['panelstatus'] == 'Started')
	{
		//---------------------------------------------------------+
		//Querying the server
		include_once("../libs/lgsl/lgsl_class.php");

		$server = lgsl_query_live($type['querytype'], $serverIp['ip'], NULL, $rowsServers['queryport'], NULL, 's');

?>
						<tr>
							<td><?php echo $rowsServers['serverid']; ?></td>
							<td><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
							<td><?php echo formatStatus('Started'); ?></td>
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
							<td><?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?></td>
							<td><?php echo $serverIp['ip']; ?></td>
							<td><?php echo $rowsServers['port']; ?></td>
							<td><?php echo $rowsServers['queryport']; ?></td>
							<td><?php echo @$server['s']['map']; ?></td>
							<td><?php echo @$server['s']['players']; ?> / <?php echo $server['s']['playersmax']; ?></td>
							<td><?php echo formatStatus($rowsServers['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="serverprofile.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
<?php

		$p = $p + $server['s']['players']; //Players
		$mp = $mp + $server['s']['playersmax']; //Max Players

		unset($server);
		//---------------------------------------------------------+
	}
	else
	{
		//---------------------------------------------------------+
?>
						<tr>
							<td><?php echo $rowsServers['serverid']; ?></td>
							<td><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
							<td><?php echo formatStatus('Stopped'); ?></td>
							<td><?php echo formatStatus('Offline'); ?></td>
							<td><?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?></td>
							<td><?php echo $serverIp['ip']; ?></td>
							<td><?php echo $rowsServers['port']; ?></td>
							<td><?php echo $rowsServers['queryport']; ?></td>
							<td>Unknown</td>
							<td>0 / 0</td>
							<td><?php echo formatStatus($rowsServers['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="serverprofile.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
<?php
		//---------------------------------------------------------+
	}

	unset($game);
	unset($type);
	unset($serverIp);
	++$n;
}

?>
						</tr>
					</tbody>
				</table>
<?php

if (mysql_num_rows($servers) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#serverstable").tablesorter({
						headers: {
							12: {
								sorter: false
							},
							13: {
								sorter: false
							}
						},
						sortList: [[1,0]]
					});
				});
				</script>
<?php
}
unset($servers);

?>
			</div>

			<div class="well">
				<div class="row">
					<div class="span4 offset4">
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Servers: <?php echo $n; ?></td>
								<td>Players: <?php echo $p; ?></td>
								<td>Max Players: <?php echo $mp; ?></td>
							</tr>
						</table>
					</div>
				</div>
				<div style="text-align: center;">
					<button class="btn" onclick="window.location.reload();">Refresh</button>
				</div>
				<div style="margin-top: 19px;">
					<table class="table table-bordered">
						<tr>
							<td style="text-align: center;">
								Powered by <a href="http://www.greycube.com" target="_blank">LGSL By Richard Perry</a>
							</td>
						</tr>
					</table>
				</div>
			</div>

<?php


include("./bootstrap/footer.php");
?>