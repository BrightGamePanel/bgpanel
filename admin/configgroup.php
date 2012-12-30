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



$title = 'Manage Groups';
$page = 'configgroup';
$tab = 5;
$return = 'configgroup.php';


require("../configuration.php");
require("./include.php");


$groups = mysql_query( "SELECT * FROM `".DBPREFIX."group` ORDER BY `groupid`" );


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
					<span class="label label-info"><?php echo mysql_num_rows($groups); ?> Record(s) Found</span> (<a href="configgroupadd.php">Add New Group</a>)
				</div>
				<table id="groups" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Description</th>
							<th>Members</th>
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
							<td colspan="6"><div style="text-align: center;"><span class="label label-warning">No Groups Found</span><br />No groups found. <a href="configgroupadd.php">Click here</a> to add a new group.</div></td>
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
				<script type="text/javascript">
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
					if (confirm("Are you sure you want to delete group: "+group+"?"))
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