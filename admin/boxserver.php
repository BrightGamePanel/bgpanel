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



$page = 'boxserver';
$tab = 3;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: BoxID error.');
}

$boxid = $_GET['id'];
$return = 'boxserver.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


$title = T_('Box Servers');

$boxid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );
$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `boxid` = '".$boxid."' ORDER BY `serverid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="boxip.php?id=<?php echo $boxid; ?>"><?php echo T_('IP Addresses'); ?></a></li>
				<li class="active"><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
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
							<th><?php echo T_('Owner Group'); ?></th>
							<th><?php echo T_('Game'); ?></th>
							<th><?php echo T_('Port'); ?></th>
							<th><?php echo T_('Slots'); ?></th>
							<th><?php echo T_('Status'); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($servers) == 0)
{
?>
						<tr>
							<td colspan="7"><div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Logs Found'); ?></span></div></td>
						</tr>
<?php
}

while ($rowsServers = mysql_fetch_assoc($servers))
{
	$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rowsServers['groupid']."' LIMIT 1" );
?>
						<tr>
							<td><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsServers['game'], ENT_QUOTES); ?></td>
							<td><?php echo $rowsServers['port']; ?></td>
							<td><?php echo $rowsServers['slots']; ?></td>
							<td><?php echo formatStatus($rowsServers['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php
	unset($group);
}

?>
					</tbody>
				</table>
<?php

if (mysql_num_rows($servers) != 0)
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
			<script>
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>