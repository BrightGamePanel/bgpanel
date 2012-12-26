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
 * @copyleft	2012
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 4
 * @link		http://www.bgpanel.net/
 */



$title = 'Boxes';
$page = 'box';
$tab = 3;
$return = 'box.php';


require("../configuration.php");
require("./include.php");


$cron = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'lastcronrun' LIMIT 1" );
$boxes = mysql_query( "SELECT `boxid`, `name`, `ip`, `sshport`, `cpu`, `ram`, `loadavg`, `hdd` FROM `".DBPREFIX."box` ORDER BY `boxid`" );


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
					<span class="label label-info"><?php echo mysql_num_rows($boxes); ?> Record(s) Found</span> (<a href="boxadd.php">Add New Box</a>)
				</div>
				<table id="boxes" class="zebra-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>IP Address</th>
							<th>Servers</th>
							<th>Network Status</th>
							<th>CPU (<a href="#" id="cpu" rel="tooltip" title="Shows the percentage of CPU in use by the box (user mode).">?</a>)</th>
							<th>RAM (<a href="#" id="ram" rel="tooltip" title="Shows the percentage of RAM in use by the box.">?</a>)</th>
							<th>Load Average (<a href="#" id="loadavg" rel="tooltip" title="Represents the average system load during the last 15 minutes.">?</a>) [<a href="http://en.wikipedia.org/wiki/Load_%28computing%29" target="_blank">Wiki</a>]</th>
							<th>HDD (<a href="#" id="hdd" rel="tooltip" title="Shows the percentage of HDD usage.">?</a>)</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

if (mysql_num_rows($boxes) == 0)
{
?>
						<tr>
							<td colspan="11"><div style="text-align: center;"><span class="label label-warning">No Boxes Found</span><br />No boxes found. <a href="boxadd.php">Click here</a> to add a new box.</div></td>
						</tr>
<?php
}

while ($rowsBoxes = mysql_fetch_assoc($boxes))
{
	$cpu = explode(';', $rowsBoxes['cpu']);
	$mem = explode(';', $rowsBoxes['ram']);
	$hdd = explode(';', $rowsBoxes['hdd']);
?>
						<tr>
							<td><?php echo $rowsBoxes['boxid']; ?></td>
							<td><?php echo htmlspecialchars($rowsBoxes['name'], ENT_QUOTES); ?></td>
							<td><?php echo htmlspecialchars($rowsBoxes['ip'], ENT_QUOTES); ?></td>
							<td><?php echo query_numrows( "SELECT `serverid` FROM `".DBPREFIX."server` WHERE `boxid` = '".$rowsBoxes['boxid']."'" ); ?></td>
							<td><?php echo formatStatus(getStatus($rowsBoxes['ip'], $rowsBoxes['sshport'])); ?></td>
							<td><span class="badge badge-<?php if ($cpu[2] < 65) { echo 'info'; } else if ($cpu[2] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $cpu[2].' %'; ?></span></td>
							<td><span class="badge badge-<?php if ($mem[3] < 65) { echo 'info'; } else if ($mem[3] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $mem[3].' %'; ?></span></td>
							<td><span class="badge badge-<?php if ($rowsBoxes['loadavg'] < $cpu[1]) { echo 'info'; } else if ($rowsBoxes['loadavg'] == $cpu[1]) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $rowsBoxes['loadavg']; ?></span></td>
							<td><span class="badge badge-<?php if ($hdd[3] < 65) { echo 'info'; } else if ($hdd[3] < 85) { echo 'warning'; } else { echo 'important'; } ?>"><?php echo $hdd[3].' %'; ?></span></td>
							<td><div style="text-align: center;"><a class="btn btn-small" href="boxprofile.php?id=<?php echo $rowsBoxes['boxid']; ?>"><i class="icon-edit <?php echo formatIcon(); ?>"></i></a></div></td>
							<td><div style="text-align: center;"><a class="btn btn-info btn-small" href="boxsummary.php?id=<?php echo $rowsBoxes['boxid']; ?>"><i class="icon-search icon-white"></i></a></div></td>
						</tr>
<?php
}

?>					</tbody>
				</table>
<?php

if (mysql_num_rows($boxes) != 0)
{
?>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#boxes").tablesorter({
						headers: {
							5: {
								sorter: false
							},
							6: {
								sorter: false
							},
							7: {
								sorter: false
							},
							8: {
								sorter: false
							},
							9: {
								sorter: false
							},
							10: {
								sorter: false
							}
						},
						sortList: [[1,0]]
					});
					$('#cpu').tooltip();
					$('#ram').tooltip();
					$('#loadavg').tooltip();
					$('#hdd').tooltip();
				});
				</script>
<?php
}
unset($boxes);

?>
			</div>

			<div class="well">Last Update : <span class="label"><?php echo formatDate($cron['value']); ?></span><?php
if ($cron['value'] == 'Never')
{
	echo "\t\t\t<br />Setup the cron job to enable box monitoring!";
}
?></div>

<?php


include("./bootstrap/footer.php");
?>