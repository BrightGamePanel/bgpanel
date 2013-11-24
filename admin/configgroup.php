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



$page = 'configgroup';
$tab = 5;
$return = 'configgroup.php';


require("../configuration.php");
require("./include.php");

$title = T_('Manage Groups');

$groups = mysql_query( "SELECT * FROM `".DBPREFIX."group` ORDER BY `groupid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="configgroupadd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Group'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="well">
				<table id="groups" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('ID'); ?></th>
							<th><?php echo T_('Name'); ?></th>
							<th><?php echo T_('Description'); ?></th>
							<th><?php echo T_('Members'); ?></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($groups) == 0)
{
?>
						<tr>
							<td colspan="6"><div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Groups Found'); ?></span><br /> <?php echo T_('No groups found.'); ?><a href="configgroupadd.php"> <?php echo T_('Click here'); ?></a>&nbsp;<?php echo T_('to add a new group.'); ?></div></td>
						</tr>
<?php
}

while ($rowsGroups = mysql_fetch_assoc($groups))
{
	if (getGroupClients($rowsGroups['groupid']) == FALSE)
	{
		$counter = 0;
	}
	else
	{
		$counter = count(getGroupClients($rowsGroups['groupid']));
	}
?>
						<tr>
							<td><?php echo $rowsGroups['groupid']; ?></td>
							<td><?php echo htmlspecialchars($rowsGroups['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsGroups['description'], ENT_QUOTES); ?></td>
							<td><?php echo $counter; ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="configgroupedit.php?id=<?php echo $rowsGroups['groupid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-danger btn-small" href="#" onclick="doDelete('<?php echo $rowsGroups['groupid']; ?>', '<?php echo htmlspecialchars(addslashes($rowsGroups['name']), ENT_QUOTES); ?>')"><i class="icon-remove icon-white"></i></a></div></td>
						</tr>
<?php
	unset($counter);
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($groups) != 0)
{
?>
				<script>
				$(document).ready(function() {
					$("#groups").tablesorter({
						headers: {
							4: {
								sorter: false
							},
							5: {
								sorter: false
							},
							sortList: [[1,0]]
						}
					});
				});
				<!-- -->
				function doDelete(id, group)
				{
					if (confirm("<?php echo T_('Are you sure you want to delete group:'); ?> "+group+"?"))
					{
						window.location='configgroupprocess.php?task=configgroupdelete&id='+id;
					}
				}
				</script>
<?php
}
unset($groups);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>