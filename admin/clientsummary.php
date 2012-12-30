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



$title = 'Client Summary';
$page = 'clientsummary';
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
$return = 'clientsummary.php?id='.urlencode($clientid);


require("../configuration.php");
require("./include.php");


if (query_numrows( "SELECT `username` FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."'" ) == 0)
{
	exit('Error: ClientID is invalid.');
}


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."client` WHERE `clientid` = '".$clientid."' LIMIT 1" );
$logs = mysql_query( "SELECT * FROM `".DBPREFIX."log` WHERE `clientid` = '".$clientid."' ORDER BY `logid` DESC LIMIT 5" );
$groups = getClientGroups($clientid);


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
				<li class="active"><a href="clientsummary.php?id=<?php echo $clientid; ?>">Summary</a></li>
				<li><a href="clientprofile.php?id=<?php echo $clientid; ?>">Profile</a></li>
				<li><a href="clientserver.php?id=<?php echo $clientid; ?>">Servers</a></li>
				<li><a href="clientlog.php?id=<?php echo $clientid; ?>">Activity Logs</a></li>
			</ul>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Client Information</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Full Name</td>
								<td><?php echo htmlspecialchars($rows['firstname'], ENT_QUOTES); ?> <?php echo htmlspecialchars($rows['lastname'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Email Address</td>
								<td><?php echo htmlspecialchars($rows['email'], ENT_QUOTES); ?></td>
							</tr>
							<tr>
								<td>Username</td>
								<td><?php echo htmlspecialchars($rows['username'], ENT_QUOTES); ?></td>
							</tr>
						</table>
						<div style="text-align:center">
							<button onclick="deleteClient();return false;" class="btn btn-danger pull-midle">Delete Client</button>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Client's Group(s)</span>
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

if ($groups == FALSE)
{
?>
								<tr>
									<td colspan="3"><div style="text-align: center;"><span class="label label-warning">This client doesn't belong to any groups.</span></div></td>
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
			</div>
			<div class="row-fluid">
				<div class="span6">
					<div class="well">
						<div style="text-align: center; margin-bottom: 5px;">
							<span class="label label-info">Other Information</span>
						</div>
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<td>Status</td>
								<td><?php echo formatStatus($rows['status']); ?></td>
							</tr>
							<tr>
								<td>Client Since</td>
								<td><?php echo formatDate($rows['created']); ?></td>
							</tr>
							<tr>
								<td>Last Login</td>
								<td><?php echo formatDate($rows['lastlogin']); ?></td>
							</tr>
							<tr>
								<td>Last IP</td>
								<td><?php echo $rows['lastip']; ?></td>
							</tr>
							<tr>
								<td>Last Hostname</td>
								<td><?php echo $rows['lasthost']; ?></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="span6">
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
									<div style="text-align: center;">
										<span class="label label-warning">No Logs Found</span>
									</div>
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
			function deleteClient()
			{
				if (confirm("Are you sure you want to delete client: <?php echo htmlspecialchars(addslashes($rows['firstname']), ENT_QUOTES); ?> <?php echo htmlspecialchars(addslashes($rows['lastname']), ENT_QUOTES); ?> ?"))
				{
					window.location.href='clientprocess.php?task=clientdelete&id=<?php echo $clientid; ?>';
				}
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>