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



$page = 'index';
$tab = 0;
$isSummary = TRUE;
$return = 'index.php';


require("configuration.php");
require("include.php");
include_once("./libs/lgsl/lgsl_class.php");


$title = T_('Home');


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."client` WHERE `clientid` = '".$_SESSION['clientid']."' LIMIT 1" );


include("./bootstrap/header.php");


$groups = getClientGroups($_SESSION['clientid']);

if ($groups == FALSE)
{
	$error1 = T_("You don't belong to any groups.");
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
}

// Build NEW single dimention array
if (!empty($groupServers))
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
	$error2 = T_("You don't have servers associated with your groups.");
}


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="row-fluid">
				<div class="span6">
					<legend><?php echo T_('Your Information'); ?></legend>
						<table class="table table-bordered table-condensed">
							<tr>
								<td><strong><?php echo T_('Full Name'); ?></strong></td>
								<td><?php echo htmlspecialchars($rows['firstname'], ENT_QUOTES); ?> <?php echo htmlspecialchars($rows['lastname'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><strong><?php echo T_('Email Adress'); ?></strong></td>
								<td><?php echo htmlspecialchars($rows['email'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td><strong><?php echo T_('Username'); ?></strong></td>
								<td><?php echo htmlspecialchars($rows['username'], ENT_QUOTES); ?></td>
							</tr>
						</table>
				</div>
				<div class="span6">
					<legend><?php echo T_('Your Groups'); ?></legend>
						<table class="table table-striped table-bordered table-condensed">
							<thead>
								<tr>
									<th><?php echo T_('Name'); ?></th>
									<th><?php echo T_('Description'); ?></th>
								</tr>
							</thead>
							<tbody>
<?php

if (isset($error1))
{
?>
								<tr>
									<td colspan="3"><div style="text-align: center;"><span class="label label-warning"><?php echo $error1; ?></span></div></td>
								</tr>
<?php
}
else
{
	foreach ($groups as $value)
	{
		$group = query_fetch_assoc( "SELECT `name`, `description` FROM `".DBPREFIX."group` WHERE `groupid` = '".$value."' LIMIT 1" );
		###
?>
								<tr>
									<td><?php echo htmlspecialchars($group['name'], ENT_QUOTES); ?></td>
									<td><?php echo htmlspecialchars($group['description'], ENT_QUOTES); ?></td>
								</tr>
<?php
		###
		unset($group);
	}
}
unset($groups);

?>
							</tbody>
						</table>
				</div>
			</div>
			<legend><?php echo T_('Assigned Game Servers'); ?></legend>
				<table id="serverstable" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Name'); ?></th>
							<th><?php echo T_('Net Status'); ?></th>
							<th><?php echo T_('Game'); ?></th>
							<th><?php echo T_('IP'); ?></th>
							<th><?php echo T_('Port'); ?></th>
							<th><?php echo T_('Slots'); ?></th>
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
		$game = query_fetch_assoc( "SELECT `game` FROM `".DBPREFIX."server` WHERE `serverid` = '".$value['serverid']."' LIMIT 1" );
		$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `game` = '".mysql_real_escape_string($game['game'])."' LIMIT 1");

		//---------------------------------------------------------+
		//Querying the server
		$server = lgsl_query_live($type['querytype'], $ip['ip'], NULL, $value['queryport'], NULL, 's');
		//---------------------------------------------------------+
?>
						<tr>
							<td><?php echo htmlspecialchars($value['name'], ENT_QUOTES); ?></td>
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
							<td><?php echo htmlspecialchars($value['game'], ENT_QUOTES); ?></td>
							<td><?php echo $ip['ip']; ?></td>
							<td><?php echo $value['port']; ?></td>
							<td><?php echo $value['slots']; ?></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="server.php?id=<?php echo $value['serverid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php
		unset($ip, $game, $type, $server);
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
			<div id="notes">
				<legend><?php echo T_('Personal Notes'); ?></legend>
					<form method="post" action="process.php">
						<input type="hidden" name="task" value="personalnotes" />
						<input type="hidden" name="clientid" value="<?php echo $_SESSION['clientid']; ?>" />
						<div style="text-align: center;">
							<textarea name="notes" class="textarea span11"><?php echo htmlspecialchars($rows['notes'], ENT_QUOTES); ?></textarea>
						</div>
						<div style="text-align: center; margin-top: 18px;">
							<button type="submit" class="btn"><?php echo T_('Save'); ?></button>
						</div>
					</form>
			</div><!-- /accordion notes -->
<?php


include("./bootstrap/footer.php");
?>