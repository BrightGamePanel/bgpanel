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



$page = 'server';
$tab = 2;
$return = 'server.php';


require("../configuration.php");
require("./include.php");


$title = T_('Servers');


$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` ORDER BY `serverid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="serveradd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Server'); ?></a>
				</div>
			</div> <!-- End Container -->

			<form method="post" action="serverprocess2.php" style="margin-bottom: 0px;">

			<div class="well">
			<fieldset>
				<table id="serverstable" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Name'); ?></th>
							<th><?php echo T_('Owner Group'); ?></th>
							<th><?php echo T_('Panel Status'); ?></th>
							<th><?php echo T_('Net Status'); ?></th>
							<th><?php echo T_('Game'); ?></th>
							<th><?php echo T_('IP'); ?>:<?php echo T_('Port'); ?></th>
							<th><?php echo T_('QPort'); ?></th>
							<th><?php echo T_('Map'); ?></th>
							<th><?php echo T_('Slots'); ?></th>
							<th><?php echo T_('Status'); ?></th>
							<th></th>
							<th></th>
							<th><input type="checkbox" class="checkall"></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($servers) == 0)
{
?>
						<tr>
							<td colspan="13"><div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Servers Found'); ?></span><br /><?php echo T_('No servers found.'); ?> <a href="serveradd.php"><?php echo T_('Click here'); ?></a> <?php echo T_('to add a new server.'); ?></div></td>
						</tr>
<?php
}

//LGSL vars
$p = 0; //Players
$mp = 0; //Max Players

$n = 0;

while ($rowsServers = mysql_fetch_assoc($servers))
{
	$serverIp = query_fetch_assoc( "SELECT `ip` FROM `".DBPREFIX."boxIp` WHERE `ipid` = '".$rowsServers['ipid']."' LIMIT 1" );
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
							<td><?php echo $serverIp['ip']; ?>:<?php echo $rowsServers['port']; ?></td>
							<td><?php echo $rowsServers['queryport']; ?></td>
							<td><?php echo @$server['s']['map']; ?></td>
							<td><?php echo @$server['s']['players']; ?> / <?php echo $server['s']['playersmax']; ?></td>
							<td><?php echo formatStatus($rowsServers['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="serverprofile.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
							<td>
								<label class="checkbox inline">
									<input type="checkbox" name="serverCheckedBoxes[]" value="<?php echo $rowsServers['serverid']; ?>">
								</label>
							</td>
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
							<td><?php echo htmlspecialchars($rowsServers['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
							<td><?php echo formatStatus('Stopped'); ?></td>
							<td><?php echo formatStatus('Offline'); ?></td>
							<td><?php echo htmlspecialchars($game['game'], ENT_QUOTES); ?></td>
							<td><?php echo $serverIp['ip']; ?>:<?php echo $rowsServers['port']; ?></td>
							<td><?php echo $rowsServers['queryport']; ?></td>
							<td>Unknown</td>
							<td>0 / 0</td>
							<td><?php echo formatStatus($rowsServers['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="serverprofile.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="serversummary.php?id=<?php echo $rowsServers['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
							<td>
								<label class="checkbox inline">
									<input type="checkbox" name="serverCheckedBoxes[]" value="<?php echo $rowsServers['serverid']; ?>">
								</label>
							</td>
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
				<script>
				$(document).ready(function() {
					$("#serverstable").tablesorter({
						headers: {
							10: {
								sorter: false
							},
							11: {
								sorter: false
							},
							12: {
								sorter: false
							}
						},
						sortList: [[0,0]]
					});
					/*
					 *	jQuery Checkall Checkboxes
					 *	http://briancray.com/posts/check-all-jquery-javascript
					 */
					$(function () {
						$('.checkall').on('click', function () {
							$(this).closest('fieldset').find(':checkbox').prop('checked', this.checked);
						});
					});
				});
				</script>
<?php
}

?>
			</fieldset>
			</div><!-- End Well -->

			<div class="row">
				<div class="span4">
					<table class="table table-striped table-bordered table-condensed">
						<tr>
							<td>
								<?php echo T_('Servers:'); ?>&nbsp;
								<span class="badge badge-info"><?php echo $n; ?></span>
							</td>
							<td>
								<?php echo T_('Players:'); ?>&nbsp;
								<span class="badge badge-info"><?php echo $p; ?></span>
							</td>
							<td>
								<?php echo T_('Max Players:'); ?>&nbsp;
								<span class="badge badge-info"><?php echo $mp; ?></span>
							</td>
						</tr>
					</table>
				</div>
				<div class="span5 offset3">
					<div class="pull-right" style="padding-right: 25px;">
						<button class="btn" onclick="window.location.reload();"><?php echo T_('Refresh'); ?></button>
<?php

if (mysql_num_rows($servers) != 0)
{
?>
						<select name="actionOnMultipleServers" style="margin-bottom: 0px;">
							<option value="multipleStart" selected="selected">Start</option>
							<option value="multipleStop">Stop</option>
							<option value="multipleReboot">Reboot</option>
							<option value="multipleUpdate">Update</option>
						</select>
						<button class="btn" type="submit">Ok</button>
						<img src="../bootstrap/img/arrow<?php echo formatIcon(); ?>.png">
<?php
}
unset($servers);

?>
					</div>
				</div>
			</div>

			</form>

			<div class="well well-small">
				<div style="text-align: center;">
					Powered by <a href="http://www.greycube.com" target="_blank">LGSL By Richard Perry</a>
				</div>
			</div>

<?php


include("./bootstrap/footer.php");
?>