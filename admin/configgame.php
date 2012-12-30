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



$title = 'Manage Games';
$page = 'configgame';
$tab = 5;
$return = 'configgame.php';


require("../configuration.php");
require("./include.php");


$games = mysql_query( "SELECT * FROM `".DBPREFIX."game` ORDER BY `game`" );


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
					<span class="label label-info"><?php echo mysql_num_rows($games); ?> Record(s) Found</span> (<a href="configgameadd.php">Add New Game</a>)
				</div>
				<table id="games" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Game</th>
							<th>Query Type</th>
							<th>Cache Directory</th>
							<th>Status</th>
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
							<td><?php echo $rowsGames['gameid']; ?></td>
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
				<script type="text/javascript">
				$(document).ready(function() {
					$("#games").tablesorter({
						headers: {
							5: {
								sorter: false
							},
							6: {
								sorter: false
							}
						},
						sortList: [[1,0]]
					});
				});
				<!-- -->
				function doDelete(id, game)
				{
					if (confirm("Are you sure you want to delete game: "+game+"?"))
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