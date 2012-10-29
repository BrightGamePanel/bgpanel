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



$title = 'Home';
$page = 'index';
$tab = 0;
$isSummary = TRUE;
$return = 'index.php';


require("configuration.php");
require("include.php");


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."client` WHERE `clientid` = '".$_SESSION['clientid']."' LIMIT 1" );


include("./bootstrap/header.php");


$groups = getClientGroups($_SESSION['clientid']);

if ($groups == FALSE)
{
	$error1 = 'You don\'t belong to any groups.';
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
	$error2 = 'You don\'t have servers associated with your groups.';
}


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
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Your Information</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Full Name</td>
								<td><?php echo htmlspecialchars($rows['firstname']); ?> <?php echo htmlspecialchars($rows['lastname']); ?></td>
							</tr>
							<tr>
								<td>Email Adress</td>
								<td><?php echo htmlspecialchars($rows['email']); ?></td>
							</tr>
							<tr>
								<td>Username</td>
								<td><?php echo htmlspecialchars($rows['username']); ?></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Your Group(s)</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Description</th>
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
	foreach ($groups as $key => $value)
	{
		$group = query_fetch_assoc( "SELECT `name`, `description` FROM `".DBPREFIX."group` WHERE `groupid` = '".$value."' LIMIT 1" );
		###
?>
								<tr>
									<td><?php echo ($key + 1); ?></td>
									<td><?php echo htmlspecialchars($group['name']); ?></td>
									<td><?php echo htmlspecialchars($group['description']); ?></td>
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
			</div>
			<div class="well">
				<div style="text-align: center; margin-bottom: 5px;">
					<span class="label label-info"><?php if (!empty($servers)) { echo count($servers); } else { echo '0'; } ?> Assigned Server(s)</span>
				</div>
				<table id="serverstable" class="tablesorter">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Game</th>
							<th>IP</th>
							<th>Port</th>
							<th>Slots</th>
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
							<td><?php echo htmlspecialchars($value['serverid']); ?></td>
							<td><?php echo htmlspecialchars($value['name']); ?></td>
							<td><?php echo htmlspecialchars($value['game']); ?></td>
							<td><?php echo $ip['ip']; ?></td>
							<td><?php echo $value['port']; ?></td>
							<td><?php echo $value['slots']; ?></td>
							<td><div style="text-align: center;"><a href="server.php?id=<?php echo $value['serverid']; ?>"><i class="icon-search <?php echo formatIcon('', TEMPLATE); ?>"></i></a></div></td>
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
							0: {
								sorter: false
							},
							6: {
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
			<div class="accordion" id="notes">
				<div class="accordion-group">
					<div class="accordion-heading" style="text-align: center;">
						<a class="accordion-toggle" href="#collapseOne" data-parent="#notes" data-toggle="collapse">Personal Notes</a>
					</div>
					<div id="collapseOne" class="accordion-body collapse">
						<div class="accordion-inner">
							<form method="post" action="process.php">
								<input type="hidden" name="task" value="personalnotes" />
								<input type="hidden" name="clientid" value="<?php echo $rows['clientid']; ?>" />
								<div style="text-align: center;">
									<textarea name="notes" class="textarea span11"><?php echo htmlspecialchars($rows['notes']); ?></textarea>
								</div>
								<div style="text-align: center; margin-top: 18px;">
									<button type="submit" class="btn">Save</button>
								</div>
							</form>
						</div><!-- /accordion-inner -->
					</div><!-- /accordion-body collapseOne-->
				</div><!-- /accordion-group -->
			</div><!-- /accordion notes -->
			<script src="./bootstrap/js/home.js"></script>
<?php


include("./bootstrap/footer.php");
?>