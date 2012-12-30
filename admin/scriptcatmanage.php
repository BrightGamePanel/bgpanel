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



$title = 'Manage Script Categories';
$page = 'scriptcatmanage';
$tab = 5;
$return = 'scriptcatmanage.php';


require("../configuration.php");
require("./include.php");


$categories = mysql_query( "SELECT * FROM `".DBPREFIX."scriptCat` ORDER BY `id`" );


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
					<span class="label label-info"><?php echo mysql_num_rows($categories); ?> Record(s) Found</span> (<a href="scriptcatadd.php">Add New Category</a>)
				</div>
				<table id="categories" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Description</th>
							<th>Scripts</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($categories) == 0)
{
?>
						<tr>
							<td colspan="6"><div style="text-align: center;"><span class="label label-warning">No Categories Found</span><br />No categories found. <a href="scriptcatadd.php">Click here</a> to add a new category.</div></td>
						</tr>
<?php
}

while ($rowsCategories = mysql_fetch_assoc($categories))
{
?>
						<tr>
							<td><?php echo $rowsCategories['id']; ?></td>
							<td><?php echo htmlspecialchars($rowsCategories['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsCategories['description'], ENT_QUOTES); ?></td>
							<td><?php echo query_numrows( "SELECT `scriptid` FROM `".DBPREFIX."script` WHERE `catid` = '".$rowsCategories['id']."'" ); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="scriptcatedit.php?id=<?php echo $rowsCategories['id']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-danger btn-small" href="#" onclick="doDelete('<?php echo $rowsCategories['id']; ?>', '<?php echo htmlspecialchars(addslashes($rowsCategories['name']), ENT_QUOTES); ?>')"><i class="icon-remove icon-white"></i></a></div></td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($categories) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#categories").tablesorter({
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
				function doDelete(id, cat)
				{
					if (confirm("Are you sure you want to delete category: "+cat+"?"))
					{
						window.location='scriptprocess.php?task=scriptcatdelete&id='+id;
					}
				}
				</script>
<?php
}
unset($categories);

?>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="script.php">Go to Scripts</a>
						</li>
					</ul>
				</div>
			</div>
<?php


include("./bootstrap/footer.php");
?>