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



$title = 'Manage Scripts';
$page = 'script';
$tab = 5;
$return = 'script.php';


require("../configuration.php");
require("./include.php");


$scripts = mysql_query( "SELECT `scriptid`, `groupid`, `boxid`, `catid`, `name`, `status`, `panelstatus`, `type` FROM `".DBPREFIX."script` ORDER BY `scriptid`" );


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
					<span class="label label-info"><?php echo mysql_num_rows($scripts); ?> Record(s) Found</span> (<a href="scriptadd.php">Add New Script</a>)
				</div>
				<table id="scripts" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Category</th>
							<th>Owner Group</th>
							<th>Exec Mode</th>
							<th>Panel Status</th>
							<th>Box Name</th>
							<th>Status</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($scripts) == 0)
{
?>
						<tr>
							<td colspan="10"><div style="text-align: center;"><span class="label label-warning">No Scripts Found</span><br />No scripts found. <a href="scriptadd.php">Click here</a> to add a new script.</div></td>
						</tr>
<?php
}

while ($rowsScripts = mysql_fetch_assoc($scripts))
{
	$cat = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."scriptCat` WHERE `id` = '".$rowsScripts['catid']."' LIMIT 1" );
	$group = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."group` WHERE `groupid` = '".$rowsScripts['groupid']."' LIMIT 1" );
	$box = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$rowsScripts['boxid']."' LIMIT 1" );
	###
	if (!empty($rowsScripts['panelstatus']))
	{
		$pstatus = formatStatus($rowsScripts['panelstatus']);
	}
	else
	{
		$pstatus = "<span class=\"label\"><em>None</em></span>";
	}

?>
						<tr>
							<td><?php echo $rowsScripts['scriptid']; ?></td>
							<td><?php echo htmlspecialchars($rowsScripts['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?></td>
							<td><?php if (!empty($group['name'])) { echo htmlspecialchars($group['name'], ENT_QUOTES); } else { echo "<span class=\"label\"><em>None</em></span>"; } ?></td>
							<td><?php if ($rowsScripts['type'] == '0') { echo 'Non-Interactive'; } else { echo 'Interactive'; }; ?></td>
							<td><?php echo $pstatus; ?></td>
							<td><?php echo htmlspecialchars($box['name'], ENT_QUOTES); ?></td>
							<td><?php echo formatStatus($rowsScripts['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="scriptprofile.php?id=<?php echo $rowsScripts['scriptid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="scriptsummary.php?id=<?php echo $rowsScripts['scriptid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php

	unset($cat, $group, $box, $pstatus);
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($scripts) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#scripts").tablesorter({
						headers: {
							8: {
								sorter: false
							},
							9: {
								sorter: false
							}
						},
						sortList: [[2,0]]
					});
				});
				</script>
<?php
}
unset($scripts);

?>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="scriptcatmanage.php">Go to Categories</a>
						</li>
					</ul>
				</div>
			</div>
<?php


include("./bootstrap/footer.php");
?>