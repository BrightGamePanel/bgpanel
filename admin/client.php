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



$title = 'Clients';
$page = 'client';
$tab = 1;
$return = 'client.php';


require("../configuration.php");
require("./include.php");


$clients = mysql_query( "SELECT `clientid`, `firstname`, `lastname`, `email`, `lastlogin`, `status` FROM `".DBPREFIX."client` ORDER BY `clientid`" );


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
					<span class="label label-info"><?php echo mysql_num_rows($clients); ?> Record(s) Found</span> (<a href="clientadd.php">Add New Client</a>)
				</div>
				<table id="clients" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th>Last Login</th>
							<th>Status</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($clients) == 0)
{
?>
						<tr>
							<td colspan="8"><div style="text-align: center;"><span class="label label-warning">No Clients Found</span><br />No clients found. <a href="clientadd.php">Click here</a> to add a new client.</div></td>
						</tr>
<?php
}

while ($rowsClients = mysql_fetch_assoc($clients))
{
?>
						<tr>
							<td><?php echo $rowsClients['clientid']; ?></td>
							<td><?php echo htmlspecialchars($rowsClients['firstname'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsClients['lastname'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsClients['email'], ENT_QUOTES); ?></td>
							<td><?php echo formatDate($rowsClients['lastlogin']); ?></td>
							<td><?php echo formatStatus($rowsClients['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="clientprofile.php?id=<?php echo $rowsClients['clientid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="clientsummary.php?id=<?php echo $rowsClients['clientid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($clients) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#clients").tablesorter({
						headers: {
							6: {
								sorter: false
							},
							7: {
								sorter: false
							}
						},
						sortList: [[3,0]]
					});
				});
				</script>
<?php
}
unset($clients);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>