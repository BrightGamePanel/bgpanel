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



$page = 'clientserver';
$tab = 1;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: ClientID error.');
}

$clientid = $_GET['id'];
$return = 'clientserver.php?id='.urlencode($clientid);


require("../configuration.php");
require("./include.php");


$title = T_('Client Servers');

$clientid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) == 0)
{
	exit('Error: ClientID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `clientid`, `firstname`, `lastname`, `status` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."' LIMIT 1" );
$groups = getClientGroups($clientid);

if ($groups == FALSE)
{
	$error1 = T_("This client doesn't belong to any groups.");
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
	$error2 = T_("This client doesn't have servers associated with his groups.");
}


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="clientsummary.php?id=<?php echo $clientid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="clientprofile.php?id=<?php echo $clientid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li class="active"><a href="clientserver.php?id=<?php echo $clientid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="clientlog.php?id=<?php echo $clientid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="serveradd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Server'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info"><?php echo T_('Assigned Servers'); ?></span>
				</div>
				<table id="serverstable" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Name'); ?></th>
							<th><?php echo T_('Game'); ?></th>
							<th><?php echo T_('IP'); ?></th>
							<th><?php echo T_('Port'); ?></th>
							<th><?php echo T_('Slots'); ?></th>
							<th><?php echo T_('Status'); ?></th>
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
		$ip = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$value['ipid']."' LIMIT 1" );
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
				<script>
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