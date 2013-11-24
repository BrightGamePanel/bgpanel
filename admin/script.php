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



$page = 'script';
$tab = 5;
$return = 'script.php';


require("../configuration.php");
require("./include.php");


$title = T_('Manage Scripts');


$scripts = mysql_query( "SELECT `scriptid`, `groupid`, `boxid`, `catid`, `name`, `status`, `panelstatus`, `type` FROM `".DBPREFIX."script` ORDER BY `scriptid`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="scriptadd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Script'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="well">
				<table id="scripts" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Name'); ?></th>
							<th><?php echo T_('Category'); ?></th>
							<th><?php echo T_('Owner Group'); ?></th>
							<th><?php echo T_('Exec Mode'); ?></th>
							<th><?php echo T_('Panel Status'); ?></th>
							<th><?php echo T_('Box Name'); ?></th>
							<th><?php echo T_('Status'); ?></th>
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
							<td colspan="10"><div style="text-align: center;"><span class="label label-warning"><?php echo T_('No Scripts Found'); ?></span><br /><?php echo T_('No scripts found.'); ?> <a href="scriptadd.php"><?php echo T_('Click here'); ?></a> <?php echo T_('to add a new script.'); ?></div></td>
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
		$pstatus = "<span class=\"label\"><em>".T_('None')."</em></span>";
	}

?>
						<tr>
							<td><?php echo htmlspecialchars($rowsScripts['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?></td>
							<td><?php if (!empty($group['name'])) { echo htmlspecialchars($group['name'], ENT_QUOTES); } else { echo "<span class=\"label\"><em>".T_('None')."</em></span>"; } ?></td>
							<td><?php if ($rowsScripts['type'] == '0') { echo T_('Non-Interactive'); } else { echo T_('Interactive'); }; ?></td>
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
				<script>
				$(document).ready(function() {
					$("#scripts").tablesorter({
						headers: {
							7: {
								sorter: false
							},
							8: {
								sorter: false
							}
						},
						sortList: [[1,0]]
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
							<a href="scriptcatmanage.php"><?php echo T_('Go to Categories'); ?></a>
						</li>
					</ul>
				</div>
			</div>
<?php


include("./bootstrap/footer.php");
?>