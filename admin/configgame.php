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



$page = 'configgame';
$tab = 5;
$return = 'configgame.php';


require("../configuration.php");
require("./include.php");

$title = T_('Manage Games');

$games = mysql_query( "SELECT * FROM `".DBPREFIX."game` ORDER BY `game`" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<div class="container">
				<div style="text-align: center; margin-bottom: 20px;">
					<a href="configgameadd.php" class="btn btn-primary"><i class="icon-plus icon-white"></i>&nbsp;<?php echo T_('Add New Game'); ?></a>
				</div>
			</div> <!-- End Container -->
			<div class="well">
				<table id="games" class="zebra-striped">
					<thead>
						<tr>
							<th><?php echo T_('Game'); ?></th>
							<th><?php echo T_('Query Type'); ?></th>
							<th><?php echo T_('Cache Directory'); ?></th>
							<th><?php echo T_('Status'); ?></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

while ($rowsGames = mysql_fetch_assoc($games))
{
?>
						<tr>
							<td><?php echo htmlspecialchars($rowsGames['game'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsGames['querytype'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsGames['cachedir'], ENT_QUOTES); ?></td>
							<td><?php echo formatStatus($rowsGames['status']); ?></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="configgameedit.php?id=<?php echo $rowsGames['gameid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-danger btn-small" href="#" onclick="doDelete('<?php echo $rowsGames['gameid']; ?>', '<?php echo htmlspecialchars(addslashes($rowsGames['game']), ENT_QUOTES); ?>')"><i class="icon-remove icon-white"></i></a></div></td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($games) != 0)
{
?>
				<script>
				$(document).ready(function() {
					$("#games").tablesorter({
						headers: {
							4: {
								sorter: false
							},
							5: {
								sorter: false
							}
						},
						sortList: [[0,0]]
					});
				});
				<!-- -->
				function doDelete(id, game)
				{
					if (confirm("<?php echo T_('Are you sure you want to delete game:'); ?> "+game+"?"))
					{
						window.location='configgameprocess.php?task=configgamedelete&id='+id;
					}
				}
				</script>
<?php
}
unset($games);

?>
			</div>
<?php


include("./bootstrap/footer.php");
?>