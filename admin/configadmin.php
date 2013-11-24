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



$page = 'configadmin';
$tab = 5;
$return = 'configadmin.php';


require("../configuration.php");
require("./include.php");

$title = T_('Administrators');

$admins = mysql_query( "SELECT * FROM `".DBPREFIX."admin` ORDER BY `adminid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="configadminadd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Administrator'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="well">
				<table id="admins" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('ID'); ?></th>
							<th><?php echo T_('Full Name'); ?></th>
							<th><?php echo T_('Email'); ?></th>
							<th><?php echo T_('Username'); ?></th>
							<th><?php echo T_('Access Level'); ?></th>
							<th><?php echo T_('Last Login'); ?></th>
							<th><?php echo T_('Status'); ?></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

while ($rowsAdmins = mysql_fetch_assoc($admins))
{
?>
						<tr>
							<td><?php echo $rowsAdmins['adminid']; ?></td>
							<td><?php echo htmlspecialchars($rowsAdmins['firstname'], ENT_QUOTES); echo ' '; echo htmlspecialchars($rowsAdmins['lastname'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsAdmins['email'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsAdmins['username'], ENT_QUOTES); ?></td>
							<td><?php echo $rowsAdmins['access']; ?></td>
							<td><?php echo formatDate($rowsAdmins['lastlogin']); ?></td>
							<td><?php echo formatStatus($rowsAdmins['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="configadminedit.php?id=<?php echo $rowsAdmins['adminid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-danger btn-small" href="#" onclick="doDelete('<?php echo $rowsAdmins['adminid']; ?>', '<?php echo htmlspecialchars(addslashes($rowsAdmins['firstname']), ENT_QUOTES); ?> <?php echo htmlspecialchars(addslashes($rowsAdmins['lastname']), ENT_QUOTES); ?>')"><i class="icon-remove icon-white"></i></a></div></td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($admins) != 0)
{
?>
				<script>
				$(document).ready(function() {
					$("#admins").tablesorter({
						headers: {
							7: {
								sorter: false
							},
							8: {
								sorter: false
							}
						},
						sortList: [[3,0]]
					});
				});
				<!-- -->
				function doDelete(id, name)
				{
					if (confirm("<?php echo T_('Are you sure you want to delete administrator:'); ?> "+name+"?"))
					{
						window.location='configadminprocess.php?task=configadmindelete&id='+id;
					}
				}
				</script>
<?php
}
unset($admins);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>