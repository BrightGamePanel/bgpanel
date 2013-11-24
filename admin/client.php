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



$page = 'client';
$tab = 1;
$return = 'client.php';


require("../configuration.php");
require("./include.php");


$title = T_('Clients');


$clients = mysql_query( "SELECT `clientid`, `firstname`, `lastname`, `email`, `lastlogin`, `status` FROM `".DBPREFIX."client` ORDER BY `clientid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="clientadd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Client'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="well">
				<table id="clients" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('ID'); ?></th>
							<th><?php echo T_('First Name'); ?></th>
							<th><?php echo T_('Last Name'); ?></th>
							<th><?php echo T_('Email'); ?></th>
							<th><?php echo T_('Last Login'); ?></th>
							<th><?php echo T_('Status'); ?></th>
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
							<td colspan="8"><div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Clients Found'); ?></span><br /><?php echo T_('No clients found.'); ?> <a href="clientadd.php"><?php echo T_('Click here'); ?></a>&nbsp;<?php echo T_('to add a new client.'); ?></div></td>
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
				<script>
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