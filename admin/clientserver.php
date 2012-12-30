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



$title = 'Client Servers';
$page = 'clientserver';
$tab = 1;
$isSummary = TRUE;
###
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	$clientid = $_GET['id'];
}
else
{
	exit('Error: ClientID error.');
}
###
$return = 'clientserver.php?id='.urlencode($clientid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) == 0)
{
	exit('Error: ClientID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `clientid`, `firstname`, `lastname`, `status` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."' LIMIT 1" );
$groups = getClientGroups($clientid);

if ($groups == FALSE)
{
	$error1 = 'This client doesn\'t belong to any groups.';
}
else
{
	foreach($groups as $value)
	{
		if (getGroupServers($value) != FALSE)
		{
			$groupServers[] = getGroupServers($value); // Multi- dimensional array
		}
	}
	unset($groups);
}

// Build NEW single dimention array
if (isset($groupServers))
{
	foreach($groupServers as $key => $value)
	{
		foreach($value as $subkey => $subvalue)
		{
			$servers[] = $subvalue;
		}
	}
	unset($groupServers);
}
else
{
	$error2 = 'This client doesn\'t have servers associated with his groups.';
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
				<li><a href="clientsummary.php?id=<?php echo $clientid; ?>">Summary</a></li>
				<li><a href="clientprofile.php?id=<?php echo $clientid; ?>">Profile</a></li>
				<li class="active"><a href="clientserver.php?id=<?php echo $clientid; ?>">Servers</a></li>
				<li><a href="clientlog.php?id=<?php echo $clientid; ?>">Activity Logs</a></li>
			</ul>
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info"><?php if (!empty($servers)) { echo count($servers); } else { echo '0'; } ?> Assigned Server(s)</span> (<a href="serveradd.php">Add New Server</a>)
				</div>
				<table id="serverstable" class="zebra-striped">
					<thead>
						<tr>
							<th>Name</th>
							<th>Game</th>
							<th>IP</th>
							<th>Port</th>
							<th>Slots</th>
							<th>Status</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (isset($error1))
{
?>
						<tr>
							<td colspan="7"><div style="text-align: center;"><span class="label label-warning"><?php echo $error1 ?></span></div></td>
						</tr>
<?php
}
else if (isset($error2))
{
?>
						<tr>
							<td colspan="7"><div style="text-align: center;"><span class="label label-warning"><?php echo $error2 ?></span></div></td>
						</tr>
<?php
}

if (!empty($servers))
{
	foreach($servers as $key => $value)
	{
		$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."box` WHERE `boxid` = '".$value['boxid']."' LIMIT 1" );
?>
						<tr>
							<td><?php echo htmlspecialchars($value['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($value['game'], ENT_QUOTES); ?></td>
							<td><?php echo $ip['ip']; ?></td>
							<td><?php echo $value['port']; ?></td>
							<td><?php echo $value['slots']; ?></td>
							<td><?php echo formatStatus($value['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="serversummary.php?id=<?php echo $value['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php
		unset($ip);
	}
}

?>
					</tbody>
				</table>
<?php

if (!empty($servers))
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#serverstable").tablesorter({
						headers: {
							6: {
								sorter: false
							}
						},
						sortList: [[0,0]]
					});
				});
				</script>
<?php
}
unset($servers);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>